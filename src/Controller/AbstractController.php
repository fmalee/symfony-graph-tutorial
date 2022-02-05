<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractController extends BaseController
{
    public function loadViewData()
    {
        $viewData = [];

        $session = $this->getSession();

        // Flash错误信息赋值
        if ($error = $session->get('error')) {
            $viewData['error'] = $error;
            $viewData['errorDetail'] = $session->get('errorDetail');
        }

        // 登录用户信息赋值
        if ($userName = $session->get('userName')) {
            $viewData['name'] = $userName;
            $viewData['email'] = $session->get('userEmail');
            $viewData['time_zone'] = $session->get('userTimeZone');
        }

        return $viewData;
    }

    public function getSession()
    {
        return $this->container->get('request_stack')->getSession();
    }
}
