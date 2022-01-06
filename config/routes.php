<?php

use App\Controller\AskQuestionController;
use App\Controller\AuthController;
use App\Controller\CguController;
use App\Controller\ForumController;
use App\Controller\HomeController;
use App\Controller\MyProfileController;
use App\Controller\ProfileController;
use App\Controller\QAController;
use App\Controller\SingleQuestionController;
use App\Controller\Test\TemplateTestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes) {

    $routes->add("Auth Page", "/auth")
        ->controller([AuthController::class, "auth"])
        ->methods([Request::METHOD_POST, Request::METHOD_GET]);

    $routes->add("Auth Callback", "/auth_callback")
        ->controller([AuthController::class, "auth_callback"]);
    $routes->add("Auth Finish", "/auth_finish")
        ->controller([AuthController::class, "finish_register"]);
    $routes->add("Logout", "/logout")
        ->controller([AuthController::class, "logout"]);

    $routes->add("forum_search", "/forum_search")
        ->controller([ForumController::class, "search"]);

    $routes->add(HomeController::getControllerName(), HomeController::getControllerPath())
        ->controller(HomeController::class)
        ->methods([Request::METHOD_POST, Request::METHOD_GET]);

    $routes->add(AskQuestionController::getControllerName(), AskQuestionController::getControllerPath())
           ->controller(AskQuestionController::class)
           ->methods([Request::METHOD_GET, Request::METHOD_POST]);

    $routes->add(MyProfileController::getControllerName(), MyProfileController::getControllerPath())
        ->controller(MyProfileController::class)
        ->methods([Request::METHOD_POST, Request::METHOD_GET]);

    $routes->add("Ajax", "/ajax/{parameter}")
        ->controller([MyProfileController::class, "ajax"]);

    $routes->add(ProfileController::getControllerName(), ProfileController::getControllerPath())
        ->controller(ProfileController::class)
        ->methods([Request::METHOD_POST, Request::METHOD_GET])
        ->requirements(["profileId" => "\d+"]);

    $routes->add(SingleQuestionController::getControllerName(), SingleQuestionController::getControllerPath())
        ->controller(SingleQuestionController::class)
        ->methods([Request::METHOD_POST, Request::METHOD_GET])
        ->requirements(["questionId" => "\d+"]);

    $routes->add(CguController::getControllerName(), CguController::getControllerPath())
        ->controller(CguController::class)
        ->methods([Request::METHOD_GET]);

    $routes->add(ForumController::getControllerName(), ForumController::getControllerPath())
        ->controller(ForumController::class)
        ->methods([Request::METHOD_POST, Request::METHOD_GET])
        ->defaults(["search_value" => null]);

    if ($GLOBALS["kernel"]->isDebug()) {

        $routes->add(TemplateTestController::getControllerName(), TemplateTestController::getControllerPath())
            ->controller(TemplateTestController::class);
    }
};