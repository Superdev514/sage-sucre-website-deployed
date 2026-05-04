<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use RedBeanPHP\R;

class MenuController
{
    public function index(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);

        $categories = R::findAll('category');
        $products    = R::findAll('product', 'WHERE is_available = 1 ORDER BY category_id, name ASC');

        return $view->render($response, 'menu.html.twig', [
            'title'      => 'Menu — Sage Sucre',
            'categories' => $categories,
            'products'   => $products,
        ]);
    }
}