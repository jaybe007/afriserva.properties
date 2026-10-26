<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class PricingEngine
{
    /**
     * Retrieve system-wide default commission configuration.
     */
    public static function getGlobalSettings(): array
    {
        $settings = [
            'commission_rate' => 5.00,
            'fixed_markup' => 0.00,
            'commission_mode' => 'percentage', // 'percentage', 'markup', 'combined', 'custom'
            'commission_basis' => 'added_to_asking', // 'added_to_asking', 'deducted_from_proceeds', 'percentage_of_buyer_price'
            'require_manual_review' => 1,
            'enable_manual_transfer' => 1,
            'rounding_mode' => 'round',
            'min_commission' => 0.00,
            'max_commission' => 0.00,
            'currency' => 'NGN'
        ];

        try {
            // Check default subscription plan configuration first
            $defaultPlan = Database::fetch(
                "SELECT commission_rate, fixed_markup, commission_mode, commission_basis 
                 FROM subscription_plans 
                 WHERE is_default = 1 AND is_active = 1 
                 LIMIT 1"
            );

            if ($defaultPlan) {
                if (isset($defaultPlan['commission_rate'])) {
                    $settings['commission_rate'] = (float)$defaultPlan['commission_rate'];
                }
                if (isset($defaultPlan['fixed_markup'])) {
                    $settings['fixed_markup'] = (float)$defaultPlan['fixed_markup'];
                }
                if (!empty($defaultPlan['commission_mode'])) {
                    $settings['commission_mode'] = $defaultPlan['commission_mode'];
                }
                if (!empty($defaultPlan['commission_basis'])) {
                    $settings['commission_basis'] = $defaultPlan['commission_basis'];
                }
            }

            // Overlay system_settings table if defined
            $rows = Database::query(
                "SELECT `key`, `value` FROM system_settings WHERE `key` LIKE 'commission_%'"
            );

            foreach ($rows as $row) {
                $k = str_replace('commission_', '', $row['key']);
                if ($k === 'default_rate') {
                    $settings['commission_rate'] = (float)$row['value'];
                } elseif ($k === 'default_fixed_markup') {
                    $settings['fixed_markup'] = (float)$row['value'];
                } elseif ($k === 'default_mode') {
                    $settings['commission_mode'] = $row['value'];
                } elseif ($k === 'default_basis') {
                    $settings['commission_basis'] = $row['value'];
                } elseif ($k === 'require_manual_review') {
                    $settings['require_manual_review'] = (int)$row['value'];
                } elseif ($k === 'enable_manual_transfer') {
                    $settings['enable_manual_transfer'] = (int)$row['value'];
                } elseif ($k === 'rounding_mode') {
                    $settings['rounding_mode'] = $row['value'];
                } elseif ($k === 'min_amount') {
                    $settings['min_commission'] = (float)$row['value'];
                } elseif ($k === 'max_amount') {
                    $settings['max_commission'] = (float)$row['value'];
                }
            }
        } catch (\Throwable $e) {
            error_log("PricingEngine::getGlobalSettings error: " . $e->getMessage());
        }

        return $settings;
    }

    /**
     * Compute comprehensive transparent pricing breakdown based on asking price and rules.
     *
     * @param float $askingPrice Seller's original asking price
     * @param array $overrides Optional custom/overridden rates
     * @return array Calculated pricing breakdown and immutable snapshot structure
     */
    public static function calculate(float $askingPrice, array $overrides = []): array
    {
        $settings = array_merge(self::getGlobalSettings(), $overrides);

        $askingPrice = max(0.00, round($askingPrice, 2));
        $commissionRate = max(0.00, (float)($settings['commission_rate'] ?? 0.00));
        $fixedMarkup = max(0.00, (float)($settings['fixed_markup'] ?? 0.00));
        $commissionMode = $settings['commission_mode'] ?? 'percentage';
        $commissionBasis = $settings['commission_basis'] ?? 'added_to_asking';

        // 1. Calculate percentage commission amount
        $commissionAmount = 0.00;
        if (in_array($commissionMode, ['percentage', 'combined', 'custom'], true) && $commissionRate > 0) {
            if ($commissionBasis === 'percentage_of_buyer_price' && $commissionRate < 100) {
                // If calculated on final buyer-facing price: BuyerPrice = (Asking + Markup) / (1 - Rate)
                $tempBuyerPrice = ($askingPrice + ($commissionMode === 'combined' ? $fixedMarkup : 0.00)) / (1 - ($commissionRate / 100));
                $commissionAmount = round($tempBuyerPrice * ($commissionRate / 100), 2);
            } else {
                // Default: Calculated as percentage of Seller's original asking price
                $commissionAmount = round(($askingPrice * $commissionRate) / 100, 2);
            }
        }

        // Apply min/max commission constraints if configured
        if (!empty($settings['min_commission']) && $settings['min_commission'] > 0) {
            $commissionAmount = max($commissionAmount, (float)$settings['min_commission']);
        }
        if (!empty($settings['max_commission']) && $settings['max_commission'] > 0) {
            $commissionAmount = min($commissionAmount, (float)$settings['max_commission']);
        }

        // 2. Calculate fixed markup amount
        $markupAmount = 0.00;
        if (in_array($commissionMode, ['markup', 'combined', 'custom'], true)) {
            $markupAmount = round($fixedMarkup, 2);
        }

        // 3. Compute buyer-facing price and seller's net proceeds according to calculation basis
        $buyerPrice = $askingPrice;
        $sellerNetProceeds = $askingPrice;

        if ($commissionBasis === 'added_to_asking') {
            // Buyer pays asking price + Afriserva commission + fixed markup
            $buyerPrice = round($askingPrice + $commissionAmount + $markupAmount, 2);
            // Seller receives exactly their original asking price
            $sellerNetProceeds = $askingPrice;
        } elseif ($commissionBasis === 'deducted_from_proceeds') {
            // Buyer pays asking price; Afriserva commission & markup are deducted from seller's proceeds
            $buyerPrice = $askingPrice;
            $sellerNetProceeds = max(0.00, round($askingPrice - $commissionAmount - $markupAmount, 2));
        } elseif ($commissionBasis === 'percentage_of_buyer_price') {
            // Buyer pays gross price calculated so that Afriserva receives r% of the buyer-facing price
            if ($commissionRate < 100) {
                $buyerPrice = round(($askingPrice + $markupAmount) / (1 - ($commissionRate / 100)), 2);
                $commissionAmount = round($buyerPrice * ($commissionRate / 100), 2);
            } else {
                $buyerPrice = round($askingPrice + $commissionAmount + $markupAmount, 2);
            }
            $sellerNetProceeds = $askingPrice;
        }

        $totalAfriservaCharges = round($commissionAmount + $markupAmount, 2);

        $snapshot = [
            'calculation_timestamp' => date('Y-m-d H:i:s'),
            'asking_price' => $askingPrice,
            'buyer_price' => $buyerPrice,
            'commission_rate' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'fixed_markup' => $markupAmount,
            'total_afriserva_charges' => $totalAfriservaCharges,
            'seller_expected_net' => $sellerNetProceeds,
            'commission_mode' => $commissionMode,
            'commission_basis' => $commissionBasis,
            'currency' => $settings['currency'] ?? 'NGN'
        ];

        return [
            'asking_price' => $askingPrice,
            'buyer_price' => $buyerPrice,
            'commission_rate' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'fixed_markup' => $markupAmount,
            'total_charges' => $totalAfriservaCharges,
            'seller_expected_net' => $sellerNetProceeds,
            'commission_mode' => $commissionMode,
            'commission_basis' => $commissionBasis,
            'currency' => $settings['currency'] ?? 'NGN',
            'snapshot_json' => json_encode($snapshot, JSON_UNESCAPED_SLASHES)
        ];
    }
}
