<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class PersonalAccessTokenSeeder extends Seeder
{
    public function run()
    {
        // Get user #1
        $user = User::find(1);

        if ($user) {
            // Generate a personal access token
            $token = $user->createToken('datatable')->plainTextToken;

            // Optionally log it so you can copy it
            \Log::info("User #1 token: " . $token);
            echo "User #1 token: " . $token . PHP_EOL;
        } else {
            echo "User #1 not found!" . PHP_EOL;
        }
    }
}
