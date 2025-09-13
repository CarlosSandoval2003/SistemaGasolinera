
<?php
namespace App\Core;

class Auth {
    const ROLE_ADMIN          = 1; 
    const ROLE_CASHIER        = 0; 
    const ROLE_MAINTENANCE    = 2; 
    const ROLE_READONLY       = 3; 
    const ROLE_PURCHASER      = 4; 
    const ROLE_SUPPLY         = 5; 

    public static function role(): int {
        if (session_status()===PHP_SESSION_NONE) session_start();
        return (int)($_SESSION['type'] ?? self::ROLE_READONLY);
    }

    public static function is(int ...$allowed): bool {
        return in_array(self::role(), $allowed, true);
    }
}
