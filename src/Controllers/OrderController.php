<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use RedBeanPHP\R;

class OrderController
{
    public function index(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);

        $products  = R::findAll('product', 'WHERE is_available = 1 ORDER BY name ASC');
        $locations = R::findAll('pickuplocation');

        return $view->render($response, 'order/index.html.twig', [
            'title'     => 'Place an Order — Sage Sucre',
            'products'  => $products,
            'locations' => $locations,
        ]);
    }

    public function submit(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $order = R::dispense('order');
        $order->first_name       = htmlspecialchars($data['first_name'] ?? '');
        $order->last_name        = htmlspecialchars($data['last_name'] ?? '');
        $order->email            = htmlspecialchars($data['email'] ?? '');
        $order->phone            = htmlspecialchars($data['phone'] ?? '');
        $order->pickup_date      = $data['pickup_date'] ?? '';
        $order->pickup_location  = htmlspecialchars($data['pickup_location'] ?? '');
        $order->product_type     = htmlspecialchars($data['product_type'] ?? '');
        $order->customizations   = htmlspecialchars($data['customizations'] ?? '');
        $order->inspiration_url  = '';
        $order->status           = 'Pending';
        $order->created_at       = date('Y-m-d H:i:s');

        R::freeze(false);
        $id = R::store($order);
        R::freeze(true);

        return $response
            ->withHeader('Location', '/order/confirm/' . $id)
            ->withStatus(302);
    }

    public function confirm(Request $request, Response $response, array $args): Response
    {
        $view  = Twig::fromRequest($request);
        $order = R::load('order', (int) $args['id']);

        return $view->render($response, 'order/confirm.html.twig', [
            'title' => 'Order Received — Sage Sucre',
            'order' => $order,
        ]);
    }
}