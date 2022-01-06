<?php


namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;

class CguController extends BaseController
{
    public static function getControllerPath(): string
    {
        return "/cgu";
    }

    public static function getControllerName(): string
    {
        return "cgu";
    }

    public function __invoke(): Response
    {
        return $this->render('Cgu/cgu.html.twig');
    }
}