<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use RedBeanPHP\R;

class AuthController
{
    public function loginPage(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'admin/login.html.twig', [
            'title' => 'Admin Login — Sage Sucre',
        ]);
    }

    public function login(Request $request, Response $response): Response
    {

        $data = $request->getParsedBody();

        $admin = \RedBeanPHP\R::findOne('admin', 'email = ?', [
            $data['email'] ?? ''
        ]);

        if ($admin && password_verify($data['password'], $admin->password_hash)) {
            $_SESSION['admin_id'] = $admin->id;

            return $response
                ->withHeader('Location', '/admin')
                ->withStatus(302);
        }

        // failed login → back to login page
        return $response
            ->withHeader('Location', '/admin/login')
            ->withStatus(302);
    }

    public function logout(Request $request, Response $response): Response
    {
        session_destroy();
        return $response->withHeader('Location', '/admin/login')->withStatus(302);
    }
}