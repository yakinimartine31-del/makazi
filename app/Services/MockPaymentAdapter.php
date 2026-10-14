<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\ListingFee;
use App\Models\Payment;
use Illuminate\Support\Str;

class MockPaymentAdapter
{
    /**
     * Process a mock payment for local development.
     * Never treats mock payments as real.
     */
    public function process(ListingFee $listingFee, array $data = []): Payment
    {
        $payment = Payment::create([
            'listing_fee_id' => $listingFee->id,
            'room_id' => $listingFee->room_id,
            'amount' => $listingFee->amount,
            'payment_method' => 'mock',
            'transaction_reference' => 'MOCK_' . strtoupper(Str::random(12)),
            'status' => 'mock',
            'receipt_data' => json_encode([
                'note' => 'MOCK PAYMENT - NOT A REAL TRANSACTION',
                'simulated' => true,
            ]),
        ]);

        $listingFee->update(['status' => PaymentStatus::Paid]);

        return $payment;
    }

    /**
     * Simulate a successful payment.
     */
    public function simulateSuccess(ListingFee $listingFee): Payment
    {
        $payment = Payment::create([
            'listing_fee_id' => $listingFee->id,
            'room_id' => $listingFee->room_id,
            'amount' => $listingFee->amount,
            'payment_method' => 'mock',
            'transaction_reference' => 'MOCK_SUCCESS_' . strtoupper(Str::random(12)),
            'status' => 'paid',
            'paid_at' => now(),
            'receipt_data' => json_encode([
                'note' => 'MOCK PAYMENT SIMULATED SUCCESS - NOT A REAL TRANSACTION',
                'simulated' => true,
                'timestamp' => now()->toISOString(),
            ]),
        ]);

        $listingFee->update(['status' => PaymentStatus::Paid]);

        return $payment;
    }

    /**
     * Verify a payment callback (idempotent).
     */
    public function verify(string $transactionReference): ?Payment
    {
        return Payment::where('transaction_reference', $transactionReference)->first();
    }

    public function isMock(): bool
    {
        return true;
    }
}
