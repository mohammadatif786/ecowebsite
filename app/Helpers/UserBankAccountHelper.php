<?php

namespace App\Helpers;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserBankAccountHelper
{
    public static function safeDecrypt(callable $callback) {

        try {

            return $callback();
        } catch (DecryptException $e) {
            Log::warning("Decryption faild", [ "error", $e->getMessage()]);
            return null;
        } catch (Throwable $e) {
            Log::error("Unexpected error", ["error",$e->getMessage()]);
            return null;
        }
    }
}

