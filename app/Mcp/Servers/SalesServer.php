<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;

class SalesServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'Sales & Orders';

    /**
     * The MCP server's version.
     */
    protected string $version = '1.0.0';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = <<<'MARKDOWN'
        This MCP server provides tools for AI agents to create and manage sales orders.

        ## Available Tools

        ### CreateSaleTool (Create Sales Order)
        - Create new sales orders for authenticated users with pending payment
        - Automatically uses user's customer account or creates one if needed
        - Validates stock availability before creating order
        - Creates pending payment record and generates secure payment links
        - Supports multiple items with quantities
        - Supports optional coupon codes for discounts
        - Returns order summary with payment links for customer to complete payment
        - Payment links work for both public and shop modules (if available)

        ## Authentication

        All tools in this server require authentication via Sanctum token.

        ## Usage Flow

        1. **Create an order** using CreateSaleTool with product items and optional coupon
           - User must be authenticated with valid Sanctum token
           - Provide product_id OR product_code for each item with quantity
           - Optionally provide coupon_code for discount
           - If user has no customer account, provide customer details to create one
           - System validates stock availability for all items
           - System fetches current prices from product database (never user-provided)
           - Order is created with pending payment status
           - Secure payment links are generated and returned
           - Share payment link with customer to complete payment via gateway (PayPal, Card, etc.)

        ## Important Notes

        - All prices are fetched from product database - never accept user-provided prices
        - Date is always set to today - no custom dates allowed
        - Authenticated user's customer account is used automatically
        - If no customer account exists, one will be created with provided details
        - Orders are created with pending payment status (received = false)
        - Payment links are generated for secure payment processing
        - Customers complete payment via web interface (PayPal, Stripe, etc.)
        - Orders are created in the authenticated user's selected store
        - Stock availability is validated before order creation
        - Coupon codes are validated for expiry, usage limits, and active status
    MARKDOWN;

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<Server\Tool>>
     */
    protected array $tools = [
        \App\Mcp\Tools\CreateSaleTool::class,
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
