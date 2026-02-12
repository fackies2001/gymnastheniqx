<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Throwable;

class TransactionHelper
{
    public static function run(callable $callback)
    {
        DB::beginTransaction();

        try {
            $result = $callback(); // execute the main logic
            DB::commit();

            return $result;
        } catch (Throwable $e) {
            DB::rollBack();

            // Optionally: log the error or rethrow
            report($e);

            // You can rethrow or return a specific response
            throw $e; // or return ['error' => $e->getMessage()];
        }
    }
}
