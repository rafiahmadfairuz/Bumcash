<?php

namespace Tests\Unit;

use App\Payments\PaymentStatus;
use PHPUnit\Framework\TestCase;

class PaymentStatusTest extends TestCase
{
    public function test_pending_transitions_and_terminal_states(): void
    {
        $this->assertTrue(PaymentStatus::Pending->canTransitionTo(PaymentStatus::Paid));
        $this->assertTrue(PaymentStatus::Pending->canTransitionTo(PaymentStatus::Failed));
        $this->assertTrue(PaymentStatus::Pending->canTransitionTo(PaymentStatus::Expired));
        $this->assertTrue(PaymentStatus::Pending->canTransitionTo(PaymentStatus::Cancelled));
        $this->assertFalse(PaymentStatus::Paid->canTransitionTo(PaymentStatus::Pending));
        $this->assertTrue(PaymentStatus::Paid->canTransitionTo(PaymentStatus::Refunded));
        $this->assertFalse(PaymentStatus::Refunded->canTransitionTo(PaymentStatus::Pending));
    }
}
