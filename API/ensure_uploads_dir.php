<?php
// Script to ensure uploads directory exists and has proper permissions
$uploadsDir = __DIR__ . '/public/uploads';

echo "Checking uploads directory...\n";

if (!file_exists($uploadsDir)) {
    echo "Creating uploads directory...\n";
    if (mkdir($uploadsDir, 0755, true)) {
        echo "✓ Uploads directory created successfully\n";
    } else {
        echo "✗ Failed to create uploads directory\n";
        exit(1);
    }
} else {
    echo "✓ Uploads directory already exists\n";
}

// Create today's subdirectory for testing
$todayDir = $uploadsDir . '/' . date('Ymd');
if (!file_exists($todayDir)) {
    echo "Creating today's upload directory...\n";
    if (mkdir($todayDir, 0755, true)) {
        echo "✓ Today's upload directory created: " . $todayDir . "\n";
    } else {
        echo "✗ Failed to create today's upload directory\n";
        exit(1);
    }
} else {
    echo "✓ Today's upload directory already exists\n";
}

// Test file creation
$testFile = $todayDir . '/test_permissions.txt';
file_put_contents($testFile, 'Permission test file');
if (file_exists($testFile)) {
    echo "✓ File creation successful - permissions are correct\n";
    unlink($testFile);
} else {
    echo "✗ File creation failed - check permissions\n";
    exit(1);
}

echo "\n✓ All checks passed! Uploads directory is ready.\n";
echo "You can now restart Docker containers:\n";
echo "cd API && docker-compose down && docker-compose up -d -build\n";
