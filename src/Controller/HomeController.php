<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController
    extends BaseController
{

    public static function getControllerPath(): string
    {
        return "/";
    }

    public static function getControllerName(): string
    {
        return "Home";
    }

    public function __invoke(Request $request): Response
    {
        return $this->render('Home/index.html.twig');
    }
}