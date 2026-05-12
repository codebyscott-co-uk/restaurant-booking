<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends \Laravel\Cashier\Http\Controllers\WebhookController
{
    protected function handleCheckoutSessionCompleted(array $payload): Response
    {
        return $this->successMethod();
    }

    protected function handleInvoicePaymentSucceeded(array $payload): Response
    {
        return $this->successMethod();
    }

    protected function handleInvoicePaymentFailed(array $payload): Response
    {
        return $this->successMethod();
    }
}
