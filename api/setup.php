<?php
// ONE-TIME SETUP: creates the table and adds dummy data.
// Visit this page once, then DELETE this file from your project.
require_once 'api/config.php';

$pdo->exec("CREATE TABLE IF NOT EXISTS profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    tagline VARCHAR(255) DEFAULT '',
    location VARCHAR(150) DEFAULT '',
    email VARCHAR(150) DEFAULT '',
    services TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Only insert dummy data if the table is empty (prevents duplicates)
$count = $pdo->query('SELECT COUNT(*) FROM profiles')->fetchColumn();
if ($count > 0) {
    echo "Table already has $count profiles. Nothing added. Delete setup.php now.";
    exit;
}

$rows = [
    ['ABC Car Care Center', 'Your trusted partner for car maintenance and repair.', 'Dhaka, Bangladesh', 'abccarcare@example.com', 'Car Washing, Oil Change, Engine Repair, General Servicing'],
    ['SpeedFix Auto Garage', 'Fast, reliable repairs you can count on.', 'Chattogram, Bangladesh', 'info@speedfix.example.com', 'Engine Repair, Brake Service, Clutch Repair, Wheel Alignment'],
    ['Shine & Drive Car Wash', 'Sparkling clean cars, every single time.', 'Gulshan, Dhaka', 'hello@shinedrive.example.com', 'Car Washing, Interior Cleaning, Wax Polish, Ceramic Coating'],
    ['Dhaka Motor Works', 'Over 15 years of expert car servicing.', 'Mirpur, Dhaka', 'contact@dhakamotor.example.com', 'General Servicing, Oil Change, Battery Replacement, AC Repair'],
    ['Sylhet Tyre & Auto Care', 'Safe tyres, smooth journeys.', 'Sylhet, Bangladesh', 'support@sylhettyre.example.com', 'Tyre Replacement, Wheel Balancing, Wheel Alignment, Puncture Repair'],
    ['Khulna Auto Electric', 'Specialists in car electrical and AC systems.', 'Khulna, Bangladesh', 'service@khulnaauto.example.com', 'Battery Replacement, Wiring Repair, AC Repair, Headlight Restoration'],
];

$stmt = $pdo->prepare('INSERT INTO profiles (name, tagline, location, email, services) VALUES (?, ?, ?, ?, ?)');
foreach ($rows as $r) $stmt->execute($r);

echo "Done! Table created and " . count($rows) . " profiles added. Now DELETE setup.php from your project.";
