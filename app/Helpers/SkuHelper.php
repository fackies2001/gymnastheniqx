<?php

namespace App\Helpers;

class SkuHelper
{
    public static function generateSystemSku($abbrv): string
    {
        return $abbrv . '-' . now()->year . '-' . strtoupper(substr(uniqid(), -6));
    }
}
