<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use App\Entity\User;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{

    private $em;
    public function __construct($em)
    {
        $this->em = $em;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('find', [$this, 'find']),
        ];
    }

    public function find($id)
    {
        return $this->em->getRepository(User::class)->find($id);
    }
}