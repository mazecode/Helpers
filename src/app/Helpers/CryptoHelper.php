<?php

namespace Siga98\Helpers;

use Carbon\Carbon;
use DateTime;
use Exception;

class CryptoHelper
{
    private static $secretKey = 'This1sm4s3cr3Tk34';
    private static $secretIv  = 'This1sm4s3cr3T1V';

    private static $encryptMethod = 'AES-256-CBC';

    /**
     * IV - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
     *
     * @param $hash
     *
     * @return string
     */
    public static function encrypt(string $hash): string
    {
        // hash
        $key = hash('sha256', self::$secretKey);

        $iv = substr(hash('sha256', self::$secretIv), 0, 16);

        $output = openssl_encrypt($hash, self::$encryptMethod, $key, 0, $iv);

        return base64_encode($output);
    }

    /**
     * @param $hash
     *
     * @return string
     */
    public static function decrypt(string $hash): string
    {
        $key = hash('sha256', self::$secretKey);
        $iv  = substr(hash('sha256', self::$secretIv), 0, 16);

        return openssl_decrypt(base64_decode($hash), self::$encryptMethod, $key, 0, $iv) ?: '';
    }

    /**
     * Generate a strong password
     *
     * @param  string  $hash
     * @param  integer $splitBy
     * @return string
     */
    public static function encryptStrong(string $hash, int $splitBy = 2): string
    {
		// TO-DO: Send warning about spaces. It not allowed
		$hash = StringHelper::hasSpaces($hash) ? StringHelper::withoutSpaces($hash) : $hash;
		$length = strlen($hash);
		
        if ($length%2 !== 0) {
			$splitBy = 3;
		}

		$splitedHash = str_split($hash, (int) round($length / $splitBy));

        array_push($splitedHash, Carbon::now()->timestamp . '_');

		$splitedHash = array_reverse($splitedHash, true);
		$base64 = base64_encode(join('.', $splitedHash));
		$crypt = self::encrypt($base64);

		return $crypt;
	}

	/**
	 * Decryp a strong password 
	 *
	 * @param [type] $hash
	 * @return string
	 */
	public static function decryptStrong($hash): string {
		$hash  = self::decrypt($hash);
		$base64 = base64_decode($hash);
        $splitedHash = explode('.', $base64);
		$splitedHash = array_reverse($splitedHash, true);

		$timestamp = str_replace('_', '', array_pop($splitedHash));

        if(!self::isTimestamp($timestamp)) {
            throw new Exception('Password invalid');
		}

        return join('', $splitedHash);
	}

	/**
	 * Check if is a valid timestamp
	 *
	 * @param int $timestamp
	 * @return boolean
	 */
	private static function isTimestamp(int $timestamp): bool {
		if (ctype_digit($timestamp) && strtotime(date('Y-m-d H:i:s', $timestamp)) === (int)$timestamp) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Validate if a password and a "storage" hash are equivalent
	 *
	 * @param string $password
	 * @param string $hash
	 * @return boolean
	 */
	public static function checkStrong(string $password, string $hash): bool{
        return $password === self::decryptStrong($hash);
	}
}