<?php

declare(strict_types=1);

namespace App\Routes;

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\CrudController;
use App\Controllers\DashboardController;
use App\Middleware\Auth;

final class Router
{
    public function dispatch(string $method, string $path): void
    {
        $path = parse_url($path, PHP_URL_PATH) ?: '/';
        $segments = array_values(array_filter(explode('/', trim($path, '/')), 'strlen'));
        $auth = new AuthController();
        if ($path === '/login' && $method === 'GET') { $auth->login(); return; }
        if ($path === '/login' && $method === 'POST') { $auth->authenticate(); return; }
        if ($path === '/logout' && $method === 'POST') { $auth->logout(); return; }
        if ($path === '/account-access' && $method === 'GET') { $auth->accessRequest(); return; }
        if ($path === '/change-password' && $method === 'GET') { $auth->changePassword(); return; }
        if ($path === '/change-password' && $method === 'POST') { $auth->updatePassword(); return; }

        if ($path === '/' || $path === '/dashboard') { (new DashboardController())->index(); return; }
        if ($path === '/reports') { (new DashboardController())->reports(); return; }
        if ($path === '/users') { $admin = new AdminController(); $method === 'POST' ? $admin->createUser() : $admin->users(); return; }
        if ($path === '/lookups') { (new AdminController())->lookups(); return; }
        if ($path === '/audit') { (new AdminController())->audit(); return; }

        $entities = ['farmers','farms','crops','plantings','harvests'];
        if (isset($segments[0]) && in_array($segments[0], $entities, true)) {
            $entity = $segments[0];
            $crud = new CrudController();
            if ($method === 'GET' && count($segments) === 1) { $crud->index($entity); return; }
            if ($method === 'GET' && isset($segments[1]) && $segments[1] === 'create') { $crud->create($entity); return; }
            if ($method === 'GET' && isset($segments[1]) && $segments[1] === 'edit' && isset($segments[2])) { $crud->edit($entity, (int) $segments[2]); return; }
            if ($method === 'POST' && count($segments) === 1) { $crud->save($entity); return; }
            if ($method === 'POST' && isset($segments[1]) && $segments[1] === 'delete' && isset($segments[2])) { $crud->delete($entity, (int) $segments[2]); return; }
        }

        http_response_code(404);
        require dirname(__DIR__) . '/Views/errors/404.php';
    }
}
