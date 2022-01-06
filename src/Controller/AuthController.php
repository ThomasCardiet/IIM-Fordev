<?php

namespace App\Controller;

use App\Entity\User;
use League\OAuth2\Client\Provider\GenericProvider;
use Microsoft\Graph\Graph;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

function env(string $key): string
{
    if (array_key_exists($key, $_ENV)) {
        return $_ENV[$key];
    }

    return "";
}

class AuthController
    extends AbstractController
{

    private function createGenericProvider(): GenericProvider
    {
        return new GenericProvider(
            [
                'clientId'                => env('OAUTH_ID'),
                'clientSecret'            => env('OAUTH_SECRET'),
                'redirectUri'             => env('OAUTH_REDIRECT_URI'),
                'urlAuthorize'            => env('OAUTH_AUTHORITY').env('OAUTH_AUTHORIZE_ENDPOINT'),
                'urlAccessToken'          => env('OAUTH_AUTHORITY').env('OAUTH_TOKEN_ENDPOINT'),
                'urlResourceOwnerDetails' => '',
                'scopes'                  => env('OAUTH_SCOPES'),
            ]
        );
    }

    public function auth(Request $request): Response
    {
        $session = $request->getSession();
        if ( ! $session->isStarted()) {
            $session->start();
        }
        $oauthClient = $this->createGenericProvider();
        $url         = $oauthClient->getAuthorizationUrl();
        $session->set("state", $oauthClient->getState());
        $session->save();

        return $this->redirect($url);
    }

    public function auth_callback(Request $request): Response
    {
        $session = $request->getSession();
        if ( ! $session->isStarted()) {
            $session->start();
        }
        $oauthClient  = $this->createGenericProvider();
        $accessToken  = $oauthClient->getAccessToken(
            'authorization_code',
            [
                'code' => $request->query->get("code"),
            ]
        );
        $queryState   = $request->query->get("state");
        $sessionState = $session->get("state");
        $session->clear();
        $session->save();
        if ($queryState !== $sessionState) {
            throw new NotAcceptableHttpException(
                "State differs between session and url [session=$sessionState,query=$queryState]"
            );
        }
        $graph = new Graph();
        $graph->setAccessToken($accessToken);
        $graphResponse = $graph->createRequest("GET", '/me?$select=mail,givenName,surname,id')
            ->execute();
        if (($status = $graphResponse->getStatus()) !== 200) {
            throw new NotAcceptableHttpException("Can't complete request to MS Graph [error=$status]");
        }
        $userInfo = $graphResponse->getBody();
        $userMail = $userInfo["mail"];
        if ( ! str_ends_with($userMail, "@edu.devinci.fr")) {
            throw new UnauthorizedHttpException("not an @edu.devinci.fr user mail");
        }
        $user = new User();
        $user->setEmail($userMail)
            ->setMsId($userInfo['id'])
            ->setFirstName($userInfo['givenName'])
            ->setLastName($userInfo['surname']);
        $doctrine = $this->getDoctrine();
        $userDb   = $doctrine->getRepository(User::class)
            ->findOneBy(["msId" => $userInfo['id']]);
        if ($userDb === null) {
            $session->set("temp", $user);

            return new RedirectResponse("/auth_finish");
        }

        $session->set('user_id', $userDb->getId());

        return $this->redirect('/');
    }

    function finish_register(Request $request): Response
    {
        $session = $request->getSession();
        if ($request->getMethod() === 'GET') {
            if ( ! $session->has("temp")) {
                return $this->redirect("/auth");
            }
            $user = $session->get("temp");

            return $this->render(
                "user_finish_register.html.twig",
                [
                    'user' => $user,
                ]
            );
        }

        if ($request->getMethod() === 'POST') {
            if ( ! $request->request->has("username")) {
                $this->redirect("/auth");
            }
            $username        = trim($request->request->get('username'));
            $username_length = iconv_strlen($username);
            if ($username_length < 4 || $username_length > 24) {
                $this->redirect('/auth');
            }
            /** @var User $user */
            $user = $session->get('temp');
            $user->setUsername($username);
            ($manager = $this->getDoctrine()
                ->getManager())
                ->persist($user);
            $manager->flush();

            $session->set('user_id', $user->getId());
            $session->save();

            return $this->redirect('/');
        }

        return $this->redirect('/auth');
    }

    public function logout(Request $request)
    {
        $request->getSession()->clear();
        $request->getSession()->save();

        return $this->redirect('/');
    }
}