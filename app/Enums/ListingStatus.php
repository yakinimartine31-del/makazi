<?php

namespace App\Enums;

enum ListingStatus: string
{
    case Draft = 'draft';
    case PendingPayment = 'pending_payment';
    case PendingApproval = 'pending_approval';
    case Published = 'published';
    case Rejected = 'rejected';
    case Suspended = 'suspended';
    case Rented = 'rented';
    case Unavailable = 'unavailable';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingPayment => 'Pending Payment',
            self::PendingApproval => 'Pending Approval',
            self::Published => 'Published',
            self::Rejected => 'Rejected',
            self::Suspended => 'Suspended',
            self::Rented => 'Rented',
            self::Unavailable => 'Unavailable',
        };
    }

    public function isPubliclyVisible(): bool
    {
        return $this === self::Published;
    }

    public function requiresPayment(): bool
    {
        return $this === self::PendingPayment || $this === self::PendingApproval;
    }
}
