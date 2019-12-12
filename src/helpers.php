<?php

use Siga98\Helpers\ArrayHelper;
use Siga98\Helpers\HttpHelper;
use Siga98\Helpers\StringHelper;

//! NOTE: SYSTEM HELPERS
if (!function_exists('dd')) {
	ini_set('xdebug.overload_var_dump', 1);
	
	/**
	 * Dump & Die
	 */
	function dd()
	{
		echo '<pre>';
		
		array_map(function ($arg) {
			var_dump($arg);
		}, func_get_args());
		
		die;
	}
}

if (!function_exists('d')) {
	ini_set('xdebug.overload_var_dump', 1);
	
	/**
	 * Dump & Die
	 */
	function d()
	{
		echo '<pre>';
		
		array_map(function ($arg) {
			var_dump($arg);
		}, func_get_args());
	}
}

// STRING HELPERS
if (!function_exists('toCamelCase')) {
	/**
	 * @param string $word
	 * @param string $separateBy
	 *
	 * @return string
	 */
	function toCamelCase(string $word, string $separateBy = '_'): string
	{
		return StringHelper::camelCase($word, $separateBy);
	}
}

if (!function_exists('startWith')) {
	/**
	 * @param string $word
	 * @param string $startWith
	 *
	 * @return bool
	 */
	function startWith(string $word, string $startWith): bool
	{
		return StringHelper::startWith($word, $startWith);
	}
}

if (!function_exists('strContains')) {
	/**
	 * @param string $word
	 * @param string $contains
	 *
	 * @return bool|false
	 */
	function strContains(string $word, string $contains)
	{
		return StringHelper::contains($word, $contains);
	}
}

if (function_exists('randomString')) {
	/**
	 * @param int $length
	 *
	 * @return string
	 */
	function randomString(int $length): string
	{
		try {
			return StringHelper::randomString($length);
		} catch (Exception $e) {
			return '';
		}
	}
}

//! NOTE: ARRAY HELPERS
if (!function_exists('arrayFlatten')) {
	/**
	 * Return a non multidimensional array
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	function arrayFlatten(array $array): array
	{
		return ArrayHelper::arrayFlatten($array);
	}
}
//! NOTE: HTTP HELPERS
if (!function_exists('getHttpStatus')) {
	/**
	 * @param int $code
	 *
	 * @return string
	 */
	function getHttpStatus(int $code)
	{
		return HttpHelper::getHttpStatus($code);
	}
}

if (!function_exists('transferFromPFXContent')) {
	/**
	 * Convert a PXF content to PEM file
	 *
	 * @param string $contentPFX
	 * @param string $passwordPFX
	 * @param string $pathPrivatePEM
	 * @param string $pathPublicPEM
	 */
	function transferFromPFXContent(string $contentPFX, string $passwordPFX, string $pathPrivatePEM, string $pathPublicPEM)
	{
		$certs = [];
		$worked = openssl_pkcs12_read($contentPFX, $certs, $passwordPFX);
		$pub_key = openssl_pkey_get_public(file_get_contents($pathPrivatePEM));
		$keyData = openssl_pkey_get_details($pub_key);
		file_put_contents($pathPublicPEM, $keyData['key']);
	}
}

if (!function_exists('transferFromPXFPath')) {
	/**
	 * Convert a PXF file to PEM file
	 *
	 * @param string $pathPFX
	 * @param string $passwordPFX
	 * @param string $pathPrivatePEM
	 * @param string $pathPublicPEM
	 */
	function transferFromPXFPath(string $pathPFX, string $passwordPFX, string $pathPrivatePEM, string $pathPublicPEM)
	{
		transferFromPFXContent(file_get_contents($pathPFX), $passwordPFX, $pathPrivatePEM, $pathPublicPEM);
	}
}

if (!function_exists('check')) {
	/**
	 * Check certificate matching
	 *
	 * @param string $passwordPFX
	 * @param string $pathPrivatePEM
	 * @param string $pathPublicPEM
	 *
	 * @return bool
	 */
	function check(string $passwordPFX, string $pathPrivatePEM, string $pathPublicPEM)
	{
		$privateKey = openssl_pkey_get_private(file_get_contents($pathPrivatePEM), $passwordPFX);
		$publicKey = openssl_pkey_get_public(file_get_contents($pathPublicPEM));
		$data = "asdf";
		
		$crypted = [];
		openssl_private_encrypt($data, $crypted, $privateKey);
		$crypted = base64_encode($crypted);
		
		$res = '';
		$res = openssl_public_decrypt(base64_decode($crypted), $res, $publicKey);
		
		return $res;
	}
}