<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class QAController
    extends BaseController
{

    public static function getControllerPath(): string
    {
        return "/qa";
    }

    public static function getControllerName(): string
    {
        return "Question / Answers";
    }

    public function __invoke():Response
    {
        return new JsonResponse(
            ["name" => self::getControllerName(),
             "path" => self::getControllerPath()]
        );
    }
}