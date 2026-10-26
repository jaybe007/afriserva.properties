<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Helpers\AuditLogger;

class CompanyBankAccount
{
    /**
     * Get the single active approved receiving account for buyers.
     */
    public static function getActiveAccount(string $currency = 'NGN'): ?array
    {
        $account = Database::fetch(
            "SELECT id, account_name, bank_name, account_number, account_type, currency, payment_instructions, is_active
             FROM company_bank_accounts 
             WHERE is_active = 1 AND currency = ? 
             ORDER BY updated_at DESC 
             LIMIT 1",
            [$currency]
        );

        if (!$account) {
            // Fallback to any active currency if NGN not specifically found
            $account = Database::fetch(
                "SELECT id, account_name, bank_name, account_number, account_type, currency, payment_instructions, is_active
                 FROM company_bank_accounts 
                 WHERE is_active = 1 
                 ORDER BY updated_at DESC 
                 LIMIT 1"
            );
        }

        return $account;
    }

    /**
     * Get all company bank accounts for administrative management.
     */
    public static function getAll(): array
    {
        return Database::query(
            "SELECT cba.*, u.full_name AS creator_name 
             FROM company_bank_accounts cba
             LEFT JOIN users u ON u.id = cba.created_by
             ORDER BY cba.is_active DESC, cba.created_at DESC"
        );
    }

    public static function findById(int $id): ?array
    {
        return Database::fetch(
            "SELECT * FROM company_bank_accounts WHERE id = ? LIMIT 1",
            [$id]
        );
    }

    /**
     * Create a new company receiving account.
     */
    public static function create(array $data, ?int $createdBy = null): int
    {
        // If this new account is set as active, deactivate other accounts of the same currency
        $isActive = !empty($data['is_active']) ? 1 : 0;
        $currency = strtoupper(trim($data['currency'] ?? 'NGN'));

        if ($isActive) {
            Database::execute(
                "UPDATE company_bank_accounts SET is_active = 0 WHERE currency = ?",
                [$currency]
            );
        }

        $sql = "INSERT INTO company_bank_accounts (
            account_name, bank_name, account_number, account_type, currency, 
            payment_instructions, is_active, created_by, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        Database::execute($sql, [
            trim($data['account_name']),
            trim($data['bank_name']),
            trim($data['account_number']),
            trim($data['account_type'] ?? 'corporate_current'),
            $currency,
            trim($data['payment_instructions'] ?? ''),
            $isActive,
            $createdBy
        ]);

        $id = (int)Database::lastInsertId();
        AuditLogger::log('company_bank_account_created', 'company_bank_account', $id, null, [
            'bank_name' => $data['bank_name'],
            'account_number_masked' => '******' . substr($data['account_number'], -4),
            'is_active' => $isActive
        ]);

        return $id;
    }

    /**
     * Update an existing receiving account.
     */
    public static function update(int $id, array $data, ?int $userId = null): bool
    {
        $existing = self::findById($id);
        if (!$existing) {
            return false;
        }

        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : (int)$existing['is_active'];
        $currency = strtoupper(trim($data['currency'] ?? $existing['currency']));

        if ($isActive) {
            Database::execute(
                "UPDATE company_bank_accounts SET is_active = 0 WHERE currency = ? AND id != ?",
                [$currency, $id]
            );
        }

        $sql = "UPDATE company_bank_accounts 
                SET account_name = ?, bank_name = ?, account_number = ?, account_type = ?,
                    currency = ?, payment_instructions = ?, is_active = ?
                WHERE id = ?";

        $success = Database::execute($sql, [
            trim($data['account_name'] ?? $existing['account_name']),
            trim($data['bank_name'] ?? $existing['bank_name']),
            trim($data['account_number'] ?? $existing['account_number']),
            trim($data['account_type'] ?? $existing['account_type']),
            $currency,
            trim($data['payment_instructions'] ?? $existing['payment_instructions']),
            $isActive,
            $id
        ]) >= 0;

        AuditLogger::log('company_bank_account_updated', 'company_bank_account', $id, null, [
            'updated_by' => $userId,
            'is_active' => $isActive,
            'bank_name' => $data['bank_name'] ?? $existing['bank_name']
        ]);

        return $success;
    }

    /**
     * Activate a receiving account as the single primary destination.
     */
    public static function setActive(int $id, ?int $userId = null): bool
    {
        $account = self::findById($id);
        if (!$account) return false;

        Database::execute(
            "UPDATE company_bank_accounts SET is_active = 0 WHERE currency = ?",
            [$account['currency']]
        );

        Database::execute(
            "UPDATE company_bank_accounts SET is_active = 1 WHERE id = ?",
            [$id]
        );

        AuditLogger::log('company_bank_account_activated', 'company_bank_account', $id, null, [
            'activated_by' => $userId
        ]);
        return true;
    }
}
