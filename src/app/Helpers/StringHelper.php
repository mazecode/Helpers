<?php

namespace Siga98\Helpers;

use Exception;

class StringHelper
{
	/**
	 * Check if a word start with some character or word
	 *
	 * @param String $word
	 * @param String $startWith
	 *
	 * @return bool
	 */
	public static function startWith(string $word, string $startWith): bool
	{
		return (strpos($word, $startWith) === 0);
	}
	
	/**
	 * @param string $word
	 * @param string $separateBy
	 *
	 * @return string|string[]|null
	 */
	public static function camelCase(string $word, string $separateBy = '_')
	{
		return preg_replace_callback("/$separateBy(.?)/", function ($matches) {
			return ucfirst($matches[1]);
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
		$len = strlen($endWith);
		
		if ($len === 0) {
			return true;
		}
		
		return (substr($word, -$len) === $endWith);
	}
	
	/**
	 * @param string $word
	 * @param string $contain
	 *
	 * @return false
	 */
	public static function contains(string $word, string $contain): bool
	{
		return (bool)preg_match('/(\w.*)?(' . preg_quote(strtolower($contain), '/') . ')(\w.*)?/', strtolower($word));
	}
	
	/**
	 * Generate a random string, using a cryptographically secure
	 * pseudorandom number generator (random_int)
	 *
	 * For PHP 7, random_int is a PHP core function
	 * For PHP 5.x, depends on https://github.com/paragonie/random_compat
	 *
	 * @param int    $length   How many characters do we want?
	 * @param string $keySpace A string of all possible characters to select from
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function randomString(int $length = 8, string $keySpace = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?'): string
	{
		$string = '';
		while (preg_match('/[a-z]/', $string) === 0 &&
			preg_match('/[A-Z]/', $string) === 0 &&
			preg_match('/[0-9]/', $string) === 0 &&
			preg_match('/[\!\@\#\$\%\^\&\*\(\)\_\-\=\+\;\:\,\.\?]/', $string) === 0) {
			mt_srand();
			$string = substr(str_shuffle($keySpace), random_int(0, strlen($keySpace) - 1), $length);
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
		
		if (strpos($availableSets, 'l') !== false) {
			$sets[] = 'abcdefghjkmnpqrstuvwxyz';
		}
		
		if (strpos($availableSets, 'u') !== false) {
			$sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
		}
		
		if (strpos($availableSets, 'd') !== false) {
			$sets[] = '23456789';
		}
		
		if (strpos($availableSets, 's') !== false) {
			$sets[] = '!@#$%&*?';
		}
		
		$all = '';
		$password = '';
		
		foreach ($sets as $set) {
			$password .= $set[array_rand(str_split($set))];
			$all .= $set;
		}
		
		$all = str_split($all);
		
		for ($i = 0; $i < $length - count($sets); $i++) {
			$password .= $all[array_rand($all)];
		}
		
		$password = str_shuffle($password);
		
		if (!$addDashes) {
			return $password;
		}
		
		$dash_len = floor(sqrt($length));
		$dash_str = '';
		
		while (strlen($password) > $dash_len) {
			$dash_str .= substr($password, 0, $dash_len) . '-';
			$password = substr($password, $dash_len);
		}
		
		$dash_str .= $password;
		
		return $dash_str;
	}

	/**
	 * Check if a string word contains any space
	 *
	 * @param string $word
	 * @return boolean
	 */
	public static function hasSpaces(string $word): bool {
        return preg_match('/\s/', $word);
	}

	/**
	 * Return any string without spaces, include between words
	 *
	 * @param string $word
	 * @return string
	 */
	public static function withoutSpaces(string $word): string {
        return preg_replace('/\s/', '', trim($word));
	}
}