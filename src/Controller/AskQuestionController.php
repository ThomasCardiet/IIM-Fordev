<?php


namespace App\Controller;


use App\Entity\Question;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AskQuestionController extends BaseController
{

    public static function getControllerPath(): string
    {
        return "/ask";
    }

    public static function getControllerName(): string
    {
        return "Ask a question";
    }

    public function __invoke(Request $request): Response
    {
        if ($this->getCurrentUser() != null) {
            if ($request->getMethod() === Request::METHOD_POST) {
                return $this->METHOD_POST($request);
            } else {
                return $this->METHOD_GET();
            }
        } else {
            return $this->redirectToRoute(HomeController::getControllerName());
        }
    }

    private function METHOD_GET(): Response
    {
        return $this->render('Questions/ask.html.twig', ['callback' => self::getControllerPath()]);
    }

    private function METHOD_POST(Request $request): Response
    {
        $question = new Question();
        $question->setAuthor($this->getCurrentUser());
        $question->setTitle($request->get('questionTitle'));
        $question->setBody($request->get('questionContent'));
        $question->setDatePosted(new DateTime('now'));
        ($manager = $this->getDoctrine()->getManager())->persist($question);
        $manager->flush();
        return $this->redirect('/');
    }
}