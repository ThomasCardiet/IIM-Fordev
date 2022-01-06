<?php

namespace App\Controller;

use App\Entity\User;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class BaseController must be used in place of {@link AbstractController} as this class provides some utility
 * functions.
 *
 * @package App\Controller
 * @author  Arthur TESSE
 */
abstract class BaseController
    extends AbstractController
{

    /**
     * Returns the path for the controller that implements the class.
     *
     * @return string the web path for this controller.
     */
    abstract public static function getControllerPath(): string;

    /**
     * Returns the controller's name the implements this class.
     *
     * @return string the controller name.
     */
    abstract public static function getControllerName(): string;

    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        if (!$this->requestStack->getCurrentRequest()->hasSession()) {
            $this->requestStack->getCurrentRequest()->setSession(new Session());
        }
    }

    protected function getCurrentUser(): ?User
    {
        $userId            = $this->requestStack->getCurrentRequest()->getSession()->get('user_id');
        return $userId !== null ? $this->getDoctrine()->getManager()->find(User::class, $userId) : null;

    }

    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            throw new RuntimeException('Can\'t access the current request');
        }

        if ( ! isset($parameters['logged_user'])) {
            $parameters['logged_user'] = $this->getCurrentUser();
        }

        return parent::render($view, $parameters, $response);
    }
}