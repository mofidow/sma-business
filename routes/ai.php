<?php

use App\Mcp\Servers;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/products', Servers\ProductServer::class)->middleware(['throttle:mcp']);
Mcp::web('/mcp/sales', Servers\SalesServer::class)->middleware(['auth:sanctum', 'throttle:mcp']);
