<?php

namespace Siga98\Helpers;

use Carbon\Carbon;
use Exception;

final class CryptoHelper
{
    private static $secretKey = 'This1sm4s3cr3Tk34';

    private static $secretIv = 'This1sm4s3cr3T1V';

    private static $encryptMethod = 'AES-256-CBC';

    /**
     * IV - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning.
     *
     * @param $hash
     *
     * @return string
     */
    public static function encrypt(string $hash): string
    {
        // hash
        $key = \hash('sha256', self::$secretKey);
        $iv = \mb_substr(\hash('sha256', self::$secretIv), 0, 16);
        $output = \openssl_encrypt($hash, self::$encryptMethod, $key, 0, $iv);

        return \base64_encode($output);
    }

    /**
     * @param $hash
     *
     * @return string
     */
    public static function decrypt(string $hash): string
    {
        $key = \hash('sha256', self::$secretKey);
        $iv = \mb_substr(\hash('sha256', self::$secretIv), 0, 16);

        return \openssl_decrypt(\base64_decode($hash, true), self::$encryptMethod, $key, 0, $iv) ?: '';
    }

    /**
     * Generate a strong password.
     *
     * @param string $hash
     * @param int    $splitBy
     *
     * @return string
     */
    public static function encryptStrong(string $hash, int $splitBy = 2): string
    {
        // TO-DO: Send warning about spaces. It not allowed
        $hash = StringHelper::hasSpaces($hash) ? StringHelper::withoutSpaces($hash) : $hash;
        $length = \mb_strlen($hash);

        if (0 !== $length % 2) {
            $splitBy = 3;
        }

        $splitedHash = \str_split($hash, (int) \round($length / $splitBy));
        $splitedHash[] = Carbon::now()->timestamp . '_';
        $splitedHash = \array_reverse($splitedHash, true);
        $base64 = \base64_encode(\implode('.', $splitedHash));

        return self::encrypt($base64);
    }

    /**
     * Decryp a strong password.
     *
     * @param [type] $hash
     *
     * @throws Exception
     *
     * @return string
     */
    public static function decryptStrong($hash): string
    {
        $hash = self::decrypt($hash);
        $base64 = \base64_decode($hash, true);
        $splitedHash = \explode('.', $base64);
        $splitedHash = \array_reverse($splitedHash, true);

        $timestamp = \str_replace('_', '', \array_pop($splitedHash));

        if (!StringHelper::isTimestamp($timestamp)) {
            throw new Exception('Password invalid');
        }

        return \implode('', $splitedHash);
    }

    /**
     * Validate if a password and a "storage" hash are equivalent.
     *
     * @param string $password
     * @param string $hash
     *
     * @throws Exception
     *
     * @return bool
     */
    public static function checkStrong(string $password, string $hash): bool
    {
        return self::decryptStrong($hash) === $password;
    }

    /**
     * Generate a digest value.
     *
     * @param        $value
     * @param string $alg
     * @param bool   $raw
     *
     * @return bool|string
     */
    public static function generateDigest($value, string $alg = 'sha512', bool $raw = true): ?string
    {
        if (StringHelper::endsWith($alg, '512')) {
            $alg = 'sha512';
        } elseif (StringHelper::endsWith($alg, '256')) {
            $alg = 'sha256';
        }

        return \base64_encode(\hash($alg, \mb_convert_encoding($value, 'UTF-8'), $raw));
//        return StringHelper::base64URLEncode(\hash($alg, \mb_convert_encoding($value, 'UTF-8'), $raw));
    }
}
