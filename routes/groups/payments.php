<?php

use Plugins\Payments\PaymentMethods;
use Illuminate\Support\Facades\Route;

/*
 * ----------------------------------------------------------
 *  Payment Gateway Routes
 * ----------------------------------------------------------
 *
 *  This file contains routes for payment gateway callbacks,
 *  webhooks, and redirects. Routes are automatically registered
 *  from payment methods.
 *
 */

foreach (PaymentMethods::resolved() as $method) {
    $routes = $method::routes();

    if ($routes === null) {
        continue;
    }

    foreach ($routes as $routeConfig) {
        $method = strtolower($routeConfig['method'] ?? 'GET');
        $uri = $routeConfig['uri'] ?? '';
        $action = $routeConfig['action'] ?? null;
        $name = $routeConfig['name'] ?? null;

        if ($uri === '' || $action === null) {
            continue;
        }

        $route = Route::{$method}($uri, $action);

        if ($name !== null) {
            $route->name($name);
        }
    }
}
