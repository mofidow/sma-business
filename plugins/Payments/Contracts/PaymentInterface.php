<?php

declare(strict_types=1);

namespace Plugins\Payments\Contracts;

/**
 * Describes a payment method that can be plugged into the application checkout workflow.
 */
interface PaymentInterface
{
    /**
     * Unique identifier that downstream code can use to resolve the payment method.
     */
    public static function key(): string;

    /**
     * Human readable label that is shown to operators and customers.
     */
    public static function displayName(): string;

    /**
     * Payment configuration fields required for this payment method.
     */
    public static function settingFields(): array;

    /**
     * Payment gateway input fields i.e. name, card no, expiry, etc.
     */
    public static function gatewayFields(): array;

    /**
     * Process or finalize a payment with the gateway. When a provider reference is provided, implementations may
     * complete a previously initiated authorization.
     */
    public function purchase(PaymentContextInterface $context): PaymentResultInterface;

    /**
     * Refund an amount (in the currency minor unit) back to the customer.
     */
    public function refund(PaymentContextInterface $context, string $providerReference, int $amount): PaymentResultInterface;

    /**
     * Determine if the payment method supports refunding a subset of the captured total.
     */
    public function supportsPartialRefunds(): bool;

    /**
     * Register routes for this payment method (callbacks, webhooks, redirects, etc.).
     * Return an array of route definitions or null if no routes are needed.
     *
     * @return array<string, array{method: string, uri: string, action: string}>|null
     */
    public static function routes(): ?array;
}
