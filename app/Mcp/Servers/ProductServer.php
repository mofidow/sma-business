<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;

class ProductServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'Product Catalog';

    /**
     * The MCP server's version.
     */
    protected string $version = '1.0.0';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = <<<'MARKDOWN'
        This MCP server provides tools for AI agents to browse and search the product catalog.

        ## Available Tools

        ### 1. SearchProductsTool (Browse Products)
        - Search and filter multiple products with flexible criteria
        - Search by name, code, or description
        - Filter by category, brand, type, price range, stock availability
        - Returns list of products with basic info (ID, code, name, price, stock)
        - No authentication required - publicly accessible
        - Use this to browse and find products

        ### 2. GetProductTool (Get Product Details)
        - Get comprehensive details about a specific product by ID or code
        - Returns detailed information including pricing, stock levels, variations, taxes
        - No authentication required - publicly accessible
        - Use this after SearchProductsTool to get full details about a specific product

        ## Authentication

        All tools in this server are publicly accessible - no authentication required.

        ## Usage Flow

        1. **Browse products** using SearchProductsTool with filters
        2. **Get detailed info** using GetProductTool with product ID or code
        3. **Create orders** by using the separate Sales & Orders MCP server (requires authentication)

        ## Important Notes

        - All prices are in the system's base currency
        - Stock information reflects current availability
        - Product data is read-only through this server
        - To create orders, use the Sales & Orders MCP server with authentication
    MARKDOWN;

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<Server\Tool>>
     */
    protected array $tools = [
        \App\Mcp\Tools\SearchProductsTool::class,
        \App\Mcp\Tools\GetProductTool::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<Server\Resource>>
     */
    protected array $resources = [
        //
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<Server\Prompt>>
     */
    protected array $prompts = [
        //
    ];
}
