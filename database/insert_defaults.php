<?php
// اضافه کردن ارزها
$currencies = [
    ['code' => 'IRR', 'name' => 'ریال', 'symbol' => '﷼'],
    ['code' => 'USD', 'name' => 'دلار', 'symbol' => '$'],
    ['code' => 'EUR', 'name' => 'یورو', 'symbol' => '€']
];

foreach ($currencies as $currency) {
    $stmt = $db->prepare("INSERT IGNORE INTO currencies (code, name, symbol) VALUES (?, ?, ?)");
    $stmt->execute([$currency['code'], $currency['name'], $currency['symbol']]);
}

// اضافه کردن انواع مالیات
$tax_types = [
    ['name' => 'مالیات بر ارزش افزوده', 'rate' => 9.00],
    ['name' => 'معاف از مالیات', 'rate' => 0.00]
];

foreach ($tax_types as $tax_type) {
    $stmt = $db->prepare("INSERT IGNORE INTO tax_types (name, rate) VALUES (?, ?)");
    $stmt->execute([$tax_type['name'], $tax_type['rate']]);
}

// اضافه کردن واحدهای مالیاتی
$tax_units = [
    ['name' => 'واحد پایه', 'code' => 'BASE'],
    ['name' => 'معاف', 'code' => 'EXEMPT']
];

foreach ($tax_units as $tax_unit) {
    $stmt = $db->prepare("INSERT IGNORE INTO tax_units (name, code) VALUES (?, ?)");
    $stmt->execute([$tax_unit['name'], $tax_unit['code']]);
}