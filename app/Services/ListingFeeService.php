<?php

namespace App\Services;

use App\Enums\ListingStatus;
use App\Enums\PaymentStatus;
use App\Models\ListingFee;
use App\Models\Room;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class ListingFeeService
{
    /**
     * Calculate the listing fee for a room.
     * Formula: listing_fee = listing price × fee_percentage / 100
     */
    public function calculateAndCreate(Room $room): ListingFee
    {
        $feePercentage = (float) Setting::get('listing_fee_percentage', 5.00);

        $listingPrice = $room->transaction_type === 'sale' ? $room->sale_price : $room->monthly_rent;
        $amount = $listingPrice * ($feePercentage / 100);

        return DB::transaction(function () use ($room, $amount, $feePercentage) {
            return ListingFee::create([
                'room_id' => $room->id,
                'amount' => $amount,
                'fee_percentage' => $feePercentage,
                'status' => PaymentStatus::Waived,
            ]);
        });
    }

    /**
     * Charge the listing fee to an owner-created room.
     * The fee_exempt flag must be set server-side, never from user input.
     */
    public function chargeForOwner(Room $room): ListingFee
    {
        $fee = $this->calculateAndCreate($room);

        if ($room->fee_exempt) {
            $fee->update(['status' => PaymentStatus::Waived]);
        } else {
            $fee->update(['status' => PaymentStatus::Unpaid]);
        }

        return $fee;
    }

    public function repriceUnpaidFee(Room $room): void
    {
        if ($room->fee_exempt) {
            return;
        }

        $fee = $room->listingFee()->latest()->first();
        if (! $fee || $fee->status !== PaymentStatus::Unpaid) {
            return;
        }

        $feePercentage = $this->getFeePercentage();
        $listingPrice = $room->transaction_type === 'sale' ? $room->sale_price : $room->monthly_rent;

        $fee->update([
            'amount' => $listingPrice * ($feePercentage / 100),
            'fee_percentage' => $feePercentage,
        ]);
    }

    /**
     * Waive the listing fee for admin-created listings.
     */
    public function waiveForAdmin(Room $room): ListingFee
    {
        $fee = $this->calculateAndCreate($room);
        $fee->update([
            'amount' => 0,
            'status' => PaymentStatus::Waived,
        ]);

        return $fee;
    }

    /**
     * Verify that a room is ready for publication.
     * A room can only be published if:
     * 1. Payment is verified (for non-exempt listings)
     * 2. Admin approval is granted
     */
    public function canPublish(Room $room): bool
    {
        if ($room->fee_exempt) {
            return $room->listing_status === ListingStatus::PendingApproval->value;
        }

        $fee = $room->listingFee()->latest()->first();

        if (! $fee) {
            return false;
        }

        if ($fee->status !== PaymentStatus::Paid && $fee->status !== PaymentStatus::Waived) {
            return false;
        }

        return $room->listing_status === ListingStatus::PendingApproval->value;
    }

    /**
     * Get the fee percentage from settings.
     */
    public function getFeePercentage(): float
    {
        return (float) Setting::get('listing_fee_percentage', 5.00);
    }
}
