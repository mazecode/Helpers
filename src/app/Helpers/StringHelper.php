<?php

namespace Siga98\Helpers;

use Exception;

final class StringHelper
{
    /**
     * Check if a word start with some character or word.
     *
     * @param string $word
     * @param string $startWith
     *
     * @return bool
     */
    public static function startWith(string $word, string $startWith): bool
    {
        return 0 === \mb_strpos($word, $startWith);
    }

    /**
     * @param string $word
     * @param string $separateBy
     *
     * @return null|string|string[]
     */
    public static function camelCase(string $word, string $separateBy = '_')
    {
        return \preg_replace_callback("/{$separateBy}(.?)/", static function ($matches) {
            return \ucfirst($matches[1]);
        }, $word);
    }

    /**
     * @param string $word
     * @param string $endWith
     *
     * @return bool
     */
    public static function endsWith(string $word, string $endWith): bool
    {
        $len = \mb_strlen($endWith);

        if (0 === $len) {
            return true;
        }

        return \mb_substr($word, -$len) === $endWith;
    }

    /**
     * @param string $word
     * @param string $contain
     *
     * @return false
     */
    public static function contains(string $word, string $contain): bool
    {
        return (bool) \preg_match('/(\w.*)?(' . \preg_quote(\mb_strtolower($contain), '/') . ')(\w.*)?/', \mb_strtolower($word));
    }

    /**
     * Generate a random string, using a cryptographically secure
     * pseudorandom number generator (random_int).
     *
     * For PHP 7, random_int is a PHP core function
     * For PHP 5.x, depends on https://github.com/paragonie/random_compat
     *
     * @param int    $length   How many characters do we want?
     * @param string $keySpace A string of all possible characters to select from
     *
     * @throws Exception
     *
     * @return string
     */
    public static function randomString(int $length = 8, string $keySpace = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?'): string
    {
        $string = '';

        while (0 === \preg_match('/[a-z]/', $string) &&
            0 === \preg_match('/[A-Z]/', $string) &&
            0 === \preg_match('/[0-9]/', $string) &&
            0 === \preg_match('/[\!\@\#\$\%\^\&\*\(\)\_\-\=\+\;\:\,\.\?]/', $string)) {
            \mt_srand();
            $string = \mb_substr(\str_shuffle($keySpace), \random_int(0, \mb_strlen($keySpace) - 1), $length);
        }

        return $string;
    }

    /**
     * Generates a strong password of N length containing at least one lower case letter,
     * one uppercase letter, one digit, and one special character. The remaining characters
     * in the password are chosen at random from those four sets.
     *
     * The available characters in each set are user friendly - there are no ambiguous
     * characters such as i, l, 1, o, 0, etc. This, coupled with the $add_dashes option,
     * makes it much easier for users to manually type or speak their passwords.
     *
     *      Note: the $add_dashes option will increase the length of the password by floor(sqrt(N)) characters.
     *
     * @param int    $length
     * @param bool   $addDashes
     * @param string $availableSets 'luds' '{lower}{upper}{digits}{symbols}'
     *
     * @return false|string
     */
    public static function generateStrongPassword($length = 9, $addDashes = false, $availableSets = 'luds')
    {
        $sets = [];

        if (false !== \mb_strpos($availableSets, 'l')) {
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        }

        if (false !== \mb_strpos($availableSets, 'u')) {
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        }

        if (false !== \mb_strpos($availableSets, 'd')) {
            $sets[] = '23456789';
        }

        if (false !== \mb_strpos($availableSets, 's')) {
            $sets[] = '!@#$%&*?';
        }

        $all = '';
        $password = '';

        foreach ($sets as $set) {
            $password .= $set[\array_rand(\str_split($set))];
            $all .= $set;
        }

        $all = \str_split($all);

        for ($i = 0; $length - \count($sets) > $i; ++$i) {
            $password .= $all[\array_rand($all)];
        }

        $password = \str_shuffle($password);

        if (!$addDashes) {
            return $password;
        }

        $dash_len = \floor(\sqrt($length));
        $dash_str = '';

        while (\mb_strlen($password) > $dash_len) {
            $dash_str .= \mb_substr($password, 0, $dash_len) . '-';
            $password = \mb_substr($password, $dash_len);
        }

        $dash_str .= $password;

        return $dash_str;
    }

    /**
     * Check if a string word contains any space.
     *
     * @param string $word
     *
     * @return bool
     */
    public static function hasSpaces(string $word): bool
    {
        return \preg_match('/\s/', $word);
    }

    /**
     * Return any string without spaces, include between words.
     *
     * @param string $word
     *
     * @return string
     */
    public static function withoutSpaces(string $word): string
    {
        return \preg_replace('/\s/', '', \trim($word));
    }

    /**
     * Encode data to Base64URL.
     *
     * @param string $data
     *
     * @return bool|string
     */
    public static function base64URLEncode(string $data): ?string
    {
        if (false === $b64 = \base64_encode($data)) {
            return false;
        }

        return \rtrim(\strtr($b64, '+/', '-_'), '=');
    }

    /**
     * @param string $data
     *
     * @return bool
     */
    public static function isBase64(string $data): bool
    {
        try {
            if (!\preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $data)) {
                return false;
            }
            // Decode the string in strict mode and check the results
            $decoded = \base64_decode($data, true);

            if (false === $decoded) {
                return false;
            }
            // Encode the string again
            if (\base64_encode($decoded) !== $data) {
                return false;
            }

            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Decode data from Base64URL.
     *
     * @param string $data
     * @param bool   $strict
     *
     * @return bool|string
     */
    public static function base64URLDecode(string $data, $strict = false): ?string
    {
        return \base64_decode(\strtr($data, '-_', '+/'), $strict);
    }

    /**
     * Check if is a valid timestamp.
     *
     * @param int $timestamp
     *
     * @return bool
     */
    public static function isTimestamp(int $timestamp): bool
    {
        return \ctype_digit($timestamp) && \strtotime(\date('Y-m-d H:i:s', $timestamp)) === (int) $timestamp;
    }
}
