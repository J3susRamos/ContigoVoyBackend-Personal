<?php

namespace App\Traits;

trait EncryptHelper
{
    public static function decrypt(string $value): string
    {
        $encryption_iv = substr(hash('sha256', config('app.key')), 0, 16);

        return openssl_decrypt($value, 'AES-128-CTR', config('app.key'), 0, $encryption_iv);
    }

    public static function encrypt(string $value): string
    {
        $encryption_iv = substr(hash('sha256', config('app.key')), 0, 16);

        return openssl_encrypt($value, 'AES-128-CTR', config('app.key'), 0, $encryption_iv);
    }
}
