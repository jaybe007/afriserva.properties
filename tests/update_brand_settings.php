<?php

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Helpers/Formatters.php';

use App\Core\Database;

$db = Database::getInstance();

$settings = [
    ['key' => 'app_name', 'value' => 'Afriserva Real Estate', 'group' => 'general', 'description' => 'Platform display brand name'],
    ['key' => 'brand_name', 'value' => 'Afriserva Real Estate', 'group' => 'general', 'description' => 'Official trading brand'],
    ['key' => 'company_name', 'value' => 'Cloud Current International Limited', 'group' => 'general', 'description' => 'Operating and parent corporation'],
    ['key' => 'company_website', 'value' => 'https://cloudcurrentng.com', 'group' => 'general', 'description' => 'Operating company website'],
    ['key' => 'main_website', 'value' => 'https://afriserva.net', 'group' => 'general', 'description' => 'Main portal and ecosystem website'],
    ['key' => 'contact_email', 'value' => 'support@afriserva.net', 'group' => 'contact', 'description' => 'Primary corporate email address']
];

$stmt = $db->prepare("INSERT INTO system_settings (`key`, `value`, `group`, description) VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `description` = VALUES(`description`)");

foreach ($settings as $s) {
    $stmt->execute([$s['key'], $s['value'], $s['group'], $s['description']]);
    echo "Set {$s['key']} => {$s['value']}\n";
}

echo "System settings updated successfully.\n";
