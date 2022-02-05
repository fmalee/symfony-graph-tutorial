<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * 站点首页
     *
     * @Route("/", name="home")
     *
     * @return Response
     */
    public function welcome()
    {
        $viewData = $this->loadViewData();

        return $this->render('welcome.html.twig', [
            'data' => $viewData,
        ]);
    }
}
