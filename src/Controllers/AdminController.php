<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use RedBeanPHP\R;

class AdminController
{
    public function dashboard(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);

        $totalOrders    = R::count('order');
        $pendingOrders  = R::count('order', 'WHERE status = ?', ['Pending']);
        $completedOrders = R::count('order', 'WHERE status = ?', ['Completed']);
        $recentOrders   = R::findAll('order', 'ORDER BY created_at DESC LIMIT 5');

        return $view->render($response, 'admin/dashboard.html.twig', [
            'title'           => 'Dashboard — Sage Sucre Admin',
            'totalOrders'     => $totalOrders,
            'pendingOrders'   => $pendingOrders,
            'completedOrders' => $completedOrders,
            'recentOrders'    => $recentOrders,
        ]);
    }

    public function orders(Request $request, Response $response): Response
    {
        $view   = Twig::fromRequest($request);
        $orders = R::findAll('order', 'ORDER BY created_at DESC');

        return $view->render($response, 'admin/orders.html.twig', [
            'title'  => 'Orders — Sage Sucre Admin',
            'orders' => $orders,
        ]);
    }

    public function orderDetail(Request $request, Response $response, array $args): Response
    {
        $view  = Twig::fromRequest($request);
        $order = R::load('order', (int) $args['id']);

        return $view->render($response, 'admin/order-detail.html.twig', [
            'title' => 'Order #' . $args['id'] . ' — Sage Sucre Admin',
            'order' => $order,
        ]);
    }

    public function updateStatus(Request $request, Response $response, array $args): Response
    {
        $data   = $request->getParsedBody();
        $order  = R::load('order', (int) $args['id']);
        $order->status = $data['status'] ?? $order->status;
        R::store($order);

        return $response->withHeader('Location', '/Sage-Sucre-Website/admin/orders')->withStatus(302);
    }

    public function products(Request $request, Response $response): Response
    {
        $view     = Twig::fromRequest($request);
        $products = R::findAll('product', 'ORDER BY name ASC');

        return $view->render($response, 'admin/products.html.twig', [
            'title'    => 'Products — Sage Sucre Admin',
            'products' => $products,
        ]);
    }

    public function createProduct(Request $request, Response $response): Response
    {
        $view       = Twig::fromRequest($request);
        $categories = R::findAll('category');

        return $view->render($response, 'admin/product-form.html.twig', [
            'title'      => 'Add Product — Sage Sucre Admin',
            'categories' => $categories,
        ]);
    }

    public function storeProduct(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $product = R::dispense('product');
        $product->name         = htmlspecialchars($data['name'] ?? '');
        $product->description  = htmlspecialchars($data['description'] ?? '');
        $product->base_price   = (float) ($data['base_price'] ?? 0);
        $product->category_id  = (int) ($data['category_id'] ?? 0);
        $product->is_available = isset($data['is_available']) ? 1 : 0;
        $product->is_presale   = isset($data['is_presale']) ? 1 : 0;
        $product->image_url    = htmlspecialchars($data['image_url'] ?? '');
        R::store($product);

        return $response->withHeader('Location', '/Sage-Sucre-Website/admin/products')->withStatus(302);
    }

    public function editProduct(Request $request, Response $response, array $args): Response
    {
        $view       = Twig::fromRequest($request);
        $product    = R::load('product', (int) $args['id']);
        $categories = R::findAll('category');

        return $view->render($response, 'admin/product-form.html.twig', [
            'title'      => 'Edit Product — Sage Sucre Admin',
            'product'    => $product,
            'categories' => $categories,
        ]);
    }

    public function updateProduct(Request $request, Response $response, array $args): Response
    {
        $data    = $request->getParsedBody();
        $product = R::load('product', (int) $args['id']);

        $product->name         = htmlspecialchars($data['name'] ?? '');
        $product->description  = htmlspecialchars($data['description'] ?? '');
        $product->base_price   = (float) ($data['base_price'] ?? 0);
        $product->category_id  = (int) ($data['category_id'] ?? 0);
        $product->is_available = isset($data['is_available']) ? 1 : 0;
        $product->is_presale   = isset($data['is_presale']) ? 1 : 0;
        $product->image_url    = htmlspecialchars($data['image_url'] ?? '');
        R::store($product);

        return $response->withHeader('Location', '/Sage-Sucre-Website/admin/products')->withStatus(302);
    }

    public function deleteProduct(Request $request, Response $response, array $args): Response
    {
        $product = R::load('product', (int) $args['id']);
        R::trash($product);

        return $response->withHeader('Location', '/Sage-Sucre-Website/admin/products')->withStatus(302);
    }

    public function calendar(Request $request, Response $response): Response
    {
        $view      = Twig::fromRequest($request);
        $locations = R::findAll('pickuplocation');
        $blocked   = R::findAll('blockeddate');

        return $view->render($response, 'admin/calendar.html.twig', [
            'title'     => 'Calendar — Sage Sucre Admin',
            'locations' => $locations,
            'blocked'   => $blocked,
        ]);
    }

    public function blockDate(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $blocked = R::dispense('blockeddate');
        $blocked->location_id = (int) ($data['location_id'] ?? 0);
        $blocked->date        = $data['date'] ?? '';
        R::store($blocked);

        return $response->withHeader('Location', '/Sage-Sucre-Website/admin/calendar')->withStatus(302);
    }

    public function unblockDate(Request $request, Response $response): Response
    {
        $data    = $request->getParsedBody();
        $blocked = R::load('blockeddate', (int) ($data['id'] ?? 0));
        R::trash($blocked);

        return $response->withHeader('Location', '/Sage-Sucre-Website/admin/calendar')->withStatus(302);
    }

    public function analytics(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);

        $totalRevenue = 0;
        $totalOrders  = R::count('order');
        $topProducts  = R::getAll('SELECT product_type, COUNT(*) as total FROM `order` GROUP BY product_type ORDER BY total DESC LIMIT 5');

        return $view->render($response, 'admin/analytics.html.twig', [
            'title'        => 'Analytics — Sage Sucre Admin',
            'totalRevenue' => $totalRevenue,
            'totalOrders'  => $totalOrders,
            'topProducts'  => $topProducts,
        ]);
    }
}