<?php

namespace App\Payments;

use App\Payments\Contracts\PaymentGateway;
use App\Payments\Exceptions\PaymentDisabled;
use App\Payments\Gateways\MockPaymentGateway;
use Illuminate\Contracts\Container\Container;

class PaymentManager
{
    public function __construct(private Container $container) {}

    public function gateway(): PaymentGateway
    {
        if (! config('payment.enabled')) throw new PaymentDisabled('Online payments are disabled.');
        $provider = config('payment.provider');
        if ($provider === 'mock' && config('payment.mock.enabled') && app()->environment(['local', 'testing'])) {
            return $this->container->make(MockPaymentGateway::class);
        }
        throw new PaymentDisabled('No supported payment provider is configured.');
    }
}
