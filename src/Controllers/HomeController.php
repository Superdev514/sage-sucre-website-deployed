<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class HomeController
{
    public function index(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'home.html.twig', [
            'title' => 'Sage Sucre — Custom Cakes & Sweet Treats',
        ]);
    }

    public function about(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'about.html.twig', [
            'title' => 'About — Sage Sucre',
        ]);
    }

    public function contact(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'contact.html.twig', [
            'title' => 'Contact — Sage Sucre',
        ]);
    }
}