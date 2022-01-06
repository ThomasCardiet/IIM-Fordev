<?php

namespace App\Controller\Test;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TemplateTestController
    extends BaseController
{

    /**
     * @inheritDoc
     */
    public static function getControllerPath(): string
    {
        return "/_test/template/{fileName}";
    }

    /**
     * @inheritDoc
     */
    public static function getControllerName(): string
    {
        return "Test / Template";
    }

    public function __invoke(Request $request, string $fileName): Response
    {
        return $this->render($fileName, $request->query->all());
    }
}