<?php


namespace App\Controller;


use App\Entity\Question;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ForumController extends BaseController
{
    public static function getControllerPath(): string
    {
        return "/forum/{search_value}";
    }

    public static function getControllerName(): string
    {
        return "forum";
    }

    public function __invoke(Request $request, $search_value): Response
    {
        $offset = ($request->query->get('page', 1) - 1) * 5;
        $questionsRepo = $this->getDoctrine()->getRepository(Question::class);
        $latest = $questionsRepo->findBy([], ['date_posted'=>'DESC'], null, $offset);
        if($search_value !== null) {
            $em = $this->getDoctrine()->getManager();
            $latest = $em->getRepository(Question::class)->createQueryBuilder('q')
                ->andWhere('q.title LIKE :search')
                ->setParameter('search', '%'.urldecode($search_value).'%')
                ->orderBy('q.date_posted', 'DESC')
                ->setFirstResult($offset)
                ->getQuery()
                ->getResult();
        }
        return $this->render('Forum/index.html.twig', compact('latest', 'search_value'));
    }

    public function search(Request $request) {
        $search_request = $request->get("header_search_content");
        if(!empty($search_request)) {
            return $this->redirectToRoute("forum", ['search_value' => urlencode($search_request)]);
        }

        return $this->redirectToRoute("forum");
    }
}