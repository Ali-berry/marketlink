<?php

namespace App\Services;

use App\Enums\FarmerApprovalStatus;
use App\Models\FarmerProfile;
use App\Notifications\FarmerApprovalStatusChanged;

// Farmer ko approve / suspend karna - admin pages aur admin AI dono yahi use karte hain
class FarmerApprovalService
{
    // suspended stall ko dobara approve bhi yahi karta hai
    public function approve(FarmerProfile $farmer): void
    {
        $farmer->update([
            'approval_status' => FarmerApprovalStatus::Approved,
            'approved_at' => now(),
            'suspension_reason' => null,
        ]);

        $farmer->user->notify(new FarmerApprovalStatusChanged($farmer));
    }

    // reason farmer ko dikhta hai, caller check kare ke khali na ho
    public function suspend(FarmerProfile $farmer, string $reason): void
    {
        $farmer->update([
            'approval_status' => FarmerApprovalStatus::Suspended,
            'suspension_reason' => $reason,
        ]);

        $farmer->user->notify(new FarmerApprovalStatusChanged($farmer));
    }
}
