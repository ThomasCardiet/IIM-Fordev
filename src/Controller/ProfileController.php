<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProfileController
    extends BaseController
{

    public static function getControllerPath(): string
    {
        return "/profile/{profileId}";
    }

    public static function getControllerName(): string
    {
        return "Profile";
    }

    public function __invoke(int $profileId): Response
    {
        return new JsonResponse(
            ["name"       => self::getControllerName(),
             "path"       => self::getControllerPath(),
             "parameters" => [
                 "profileName" => $profileId,
             ]]
        );
    }
}