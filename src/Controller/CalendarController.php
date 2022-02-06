<?php

namespace App\Controller;

use App\TimeZones\TimeZones;
use App\TokenStore\TokenCache;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/calendar")
 */
class CalendarController extends AbstractController
{
    /**
     * 令牌存储实例
     * 
     * @var TokenCache
     */
    private TokenCache $cache;

    public function __construct(TokenCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * 日历事件列表
     *
     * @Route("/", name="calendar")
     *
     * @return Response
     */
    public function calendar(TokenCache $cache): Response
    {
        $viewData = $this->loadViewData();

        $graph = $this->getGraph();

        // 获取用户时区
        $timezone = TimeZones::getTzFromWindows($viewData['time_zone']);

        // 获取开始和结束的星期
        $startOfWeek = new \DateTimeImmutable('sunday -1 week', $timezone);
        $endOfWeek = new \DateTimeImmutable('sunday', $timezone);
    
        $viewData['date_range'] = $startOfWeek->format('M j, Y').' - '.$endOfWeek->format('M j, Y');

        $queryParams = [
            'startDateTime' => $startOfWeek->format(\DateTimeInterface::ISO8601),
            'endDateTime' => $endOfWeek->format(\DateTimeInterface::ISO8601),
            // 只请求本应用会用到的属性
            '$select' => 'subject,organizer,start,end',
            // 按开始时间排序
            '$orderby' => 'start/dateTime',
            // 限制结果为25
            '$top' => 25
        ];

        // 将查询参数附加到 /me/calendarView 网址
        $getEventsUrl = '/me/calendarView?' . http_build_query($queryParams);

        $events = $graph->createRequest('GET', $getEventsUrl)
            // 将用户的时区添加到 Prefer 标头
            ->addHeaders([
                'Prefer' => 'outlook.timezone="' . $viewData['time_zone'] . '"'
            ])
            ->setReturnType(Model\Event::class)
            ->execute();

        $viewData['events'] = $events;

        return $this->render('calendar.html.twig', [
            'data' => $viewData,
        ]);
    }

    /**
     * 日历事件列表
     *
     * @Route("/new", name="new_event")
     *
     * @return Response
     */
    public function createNewEvent(Request $request, ValidatorInterface $validator)
    {
        $viewData = $this->loadViewData();
        $errors = [];

        // 安装symfony的表单组件可以更简单的实现表单的展示、提交、验证：
        // composer require symfony/form
        // 但是因为是简单教程，本教程就只用Symfony的核心框架，没有安装全栈。
        if ($request->request->get('submit')) {
            // 获取所有表单参数
            $form = $request->request->all();
            unset($form['submit']);

            // 验证必填字段
            $errors = $this->validateForm($validator, $form);

            if (!$errors->count()) { // 提交事件
                return $this->postEvent($form, $viewData['time_zone']);
            }
        }

        return $this->render('new_event.html.twig', [
            'data' => $viewData,
            'errors' => $errors
        ]);
    }

    /**
     * 验证表单
     * 
     * @param ValidatorInterface $validator 验证器
     * @param array $form 表单数据
     * 
     * @return ConstraintViolationList 验证错误列表
     */
    private function validateForm(ValidatorInterface $validator, array $form): ConstraintViolationList
    {
        $groups = new Assert\GroupSequence(['Default', 'custom']);
        $constraint = new Assert\Collection([
            'eventSubject' => new Assert\Type('string'),
            'eventAttendees' => new Assert\Type('string'),
            'eventStart' => [
                new Assert\NotBlank(),
                new Assert\DateTime(['format' => 'Y-m-d\TH:i']),
            ],
            'eventEnd' => [
                new Assert\NotBlank(),
                new Assert\DateTime(['format' => 'Y-m-d\TH:i']),
            ],
            'eventBody' => new Assert\Type('string'),
        ]);
        
        return $validator->validate($form, $constraint, $groups);
    }

    /**
     * 提交事件
     * 
     * @param array $form 表单数组
     * @param string $form 用户时区
     * 
     * @return Response
     */
    private function postEvent(array $form, string $timeZone): Response
    {
        $graph = $this->getGraph();

        $attendees = [];
        if ($eventAttendees = $form['eventAttendees']) {
            // 表单中的与会者是以分号分隔的电子邮件地址列表
            $attendeeAddresses = explode(';', $eventAttendees);

            // Graph中的 Attendee 对象很复杂，所以请构建以下结构
            foreach($attendeeAddresses as $attendeeAddress) {
                array_push($attendees, [
                    // 在 emailAddress 属性中添加邮箱地址
                    'emailAddress' => [
                        'address' => $attendeeAddress
                    ],
                    // 将与会者类型设置为 required
                    'type' => 'required'
                ]);
            }
        }

        // 创建事件
        $newEvent = [
            'subject' => $form['eventSubject'],
            'attendees' => $attendees,
            'start' => [
                'dateTime' => $form['eventStart'],
                'timeZone' => $timeZone
            ],
            'end' => [
                'dateTime' => $form['eventEnd'],
                'timeZone' => $timeZone
            ],
            'body' => [
                'content' => $form['eventBody'],
                'contentType' => 'text'
            ]
        ];

        // 提交到 /me/events
        $response = $graph->createRequest('POST', '/me/events')
            ->attachBody($newEvent)
            ->setReturnType(Model\Event::class)
            ->execute();

        return $this->redirectToRoute('calendar');
    }

    /**
     * 获取 Graph 客户端
     * 
     * @return Graph
     */
    private function getGraph(): Graph
    {
        // 从缓存中获取访问令牌
        $accessToken = $this->cache->getAccessToken();

        // 创建 Graph 客户端
        $graph = new Graph();
        $graph->setAccessToken($accessToken);
        return $graph;
    }
}
