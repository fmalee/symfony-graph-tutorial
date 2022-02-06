<?php

namespace App\Controller;

use App\TimeZones\TimeZones;
use App\TokenStore\TokenCache;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/calendar", name="calendar")
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
