<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$apiKey = config('services.qoyod.api_key');
$baseUrl = config('services.qoyod.base_url', 'https://api.qoyod.com/2.0');
$headers = [
    'API-KEY' => $apiKey,
    'Accept' => 'application/json',
    'Content-Type' => 'application/json',
];

$contactsRes = \Illuminate\Support\Facades\Http::withHeaders($headers)->get("{$baseUrl}/customers", ['page' => 1, 'per_page' => 1]);
$contactId = $contactsRes->json('customers.0.id') ?? $contactsRes->json('contacts.0.id') ?? 7;

$invRes = \Illuminate\Support\Facades\Http::withHeaders($headers)->get("{$baseUrl}/inventories");
$inventoryId = $invRes->json('inventories.0.id') ?? 1;

// Test with percentage discount
$pPercent = [
    'invoice' => [
        'contact_id' => $contactId,
        'inventory_id' => (int) $inventoryId,
        'issue_date' => date('Y-m-d'),
        'due_date' => date('Y-m-d'),
        'reference' => 'TEST-PCT-' . time(),
        'description' => 'تجربة نسبة الخصم',
        'status' => 'Draft',
        'is_inclusive' => true,
        'line_items' => [
            [
                'product_id' => 1,
                'inventory_id' => (int) $inventoryId,
                'quantity' => 1,
                'unit_price' => 274.68,
                'tax_percent' => 15,
                'discount' => number_format((67.03 / 274.68) * 100, 2, '.', ''),
                'discount_type' => 'percentage',
                'is_inclusive' => true,
            ],
        ],
    ],
];

$resPct = \Illuminate\Support\Facades\Http::withHeaders($headers)->post("{$baseUrl}/invoices", $pPercent);
$invPct = $resPct->json('invoice') ?? $resPct->json();
echo "PERCENT TEST: total=" . ($invPct['total'] ?? '') . "\n";
echo "Line items: " . json_encode($invPct['line_items'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

if (!empty($invPct['id'])) {
    \Illuminate\Support\Facades\Http::withHeaders($headers)->delete("{$baseUrl}/invoices/{$invPct['id']}");
}
