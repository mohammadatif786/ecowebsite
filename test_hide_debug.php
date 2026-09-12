<?php

require_once 'vendor/autoload.php';

use App\Models\HideSpecificUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== HideSpecificUser Debug Test ===\n\n";

try {
    // Test 1: Check if database connection works
    echo "1. Testing database connection...\n";
    $connection = DB::connection();
    echo "   Database connected: " . $connection->getDatabaseName() . "\n\n";

    // Test 2: Check if users table has data
    echo "2. Checking users table...\n";
    $users = User::limit(2)->get();
    echo "   Found " . $users->count() . " users\n";
    if ($users->count() >= 2) {
        echo "   User 1 ID: " . $users[0]->id . " - " . $users[0]->name . "\n";
        echo "   User 2 ID: " . $users[1]->id . " - " . $users[1]->name . "\n";
    }
    echo "\n";

    // Test 3: Try to create a HideSpecificUser record
    echo "3. Testing HideSpecificUser creation...\n";
    if ($users->count() >= 2) {
        $hideRecord = HideSpecificUser::create([
            'user_id' => $users[0]->id,
            'target_user_id' => $users[1]->id,
            'is_hidden' => true
        ]);
        
        echo "   Record created successfully!\n";
        echo "   Record ID: " . $hideRecord->id . "\n";
        echo "   User ID: " . $hideRecord->user_id . "\n";
        echo "   Target User ID: " . $hideRecord->target_user_id . "\n";
        echo "   Is Hidden: " . ($hideRecord->is_hidden ? 'true' : 'false') . "\n";
    } else {
        echo "   Not enough users to create test record\n";
    }
    echo "\n";

    // Test 4: Query the records
    echo "4. Testing HideSpecificUser query...\n";
    $records = HideSpecificUser::all();
    echo "   Total records in hide_specific_users: " . $records->count() . "\n";
    
    foreach ($records as $record) {
        echo "   - ID: " . $record->id . ", User: " . $record->user_id . ", Target: " . $record->target_user_id . ", Hidden: " . ($record->is_hidden ? 'Yes' : 'No') . "\n";
    }
    echo "\n";

    // Test 5: Test the specific query used in IndexController
    echo "5. Testing IndexController query logic...\n";
    if ($users->count() >= 1) {
        $currentUser = $users[0];
        $hiddenUsers = DB::table('hide_specific_users')
            ->where('user_id', $currentUser->id)
            ->where('is_hidden', true)
            ->pluck('target_user_id')
            ->toArray();
        
        echo "   User " . $currentUser->id . " has hidden users: " . implode(', ', $hiddenUsers) . "\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Debug Complete ===\n";
