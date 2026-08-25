<?php

namespace App\Services;

use App\Models\Business;

/**
 * Computes commission server-side only, from the gross transaction amount
 * plus the active commission configuration (platform default or a seller
 * override). Never trust commission_amount/seller_amount supplied by a
 * client — this is the only place those numbers are allowed to originate.
 * Callers must persist the returned values onto the Payment row as a
 * permanent snapshot; see PaymentService::initializeForOrder(). Once
 * saved, that snapshot is authoritative forever, even after the platform
 * or seller rate later changes — reports must read the stored values,
 * never recompute them from the current live configuration.
 */
class CommissionService
{
    public function __construct(private readonly PlatformSettingsService $settings) {}

    /**
     * @return array{rate: float, commission_amount: int, seller_amount: int}
     */
    public function calculate(Business $business, int $grossAmountMinorUnits): array
    {
        if (! $this->settings->commissionEnabled()) {
            return ['rate' => 0.0, 'commission_amount' => 0, 'seller_amount' => $grossAmountMinorUnits];
        }

        $rate = $this->resolveRate($business);

        $commissionAmount = $this->settings->commissionType() === 'fixed'
            ? (int) round($rate * 100) // fixed rate is expressed in major currency units, like Payment::amount
            : (int) round($grossAmountMinorUnits * $rate / 100);

        $commissionAmount = max(0, min($commissionAmount, $grossAmountMinorUnits));

        return [
            'rate' => $rate,
            'commission_amount' => $commissionAmount,
            'seller_amount' => $grossAmountMinorUnits - $commissionAmount,
        ];
    }

    private function resolveRate(Business $business): float
    {
        $rate = $business->commission_rate ?? $this->settings->commissionRate();

        $min = $this->settings->commissionMin();
        $max = $this->settings->commissionMax();

        if ($min !== null) {
            $rate = max($rate, $min);
        }

        if ($max !== null) {
            $rate = min($rate, $max);
        }

        return $rate;
    }
}
