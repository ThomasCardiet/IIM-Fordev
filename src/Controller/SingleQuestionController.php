<?php

namespace App\Controller;

use App\Entity\Question;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SingleQuestionController
    extends BaseController
{

    public static function getControllerPath(): string
    {
        return "/q/{questionId}";
    }

    public static function getControllerName(): string
    {
        return "Question";
    }

    public function __invoke(int $questionId) : Response{
        $question_repo= $this->getDoctrine()->getRepository(Question::class);
        return $this->render("Forum/view.html.twig", [
            "question" => $question_repo ->find($questionId)
        ]);

    }
}