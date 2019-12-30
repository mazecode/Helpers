<?php

namespace Siga98\Helpers;

class ByteHelper
{
	/**
	 * @param $string
	 *
	 * @return array|false
	 */
	public static function string2ByteArray($string)
	{
		return unpack('C*', $string);
	}
	
	/**
	 * @param $byteArray
	 *
	 * @return string
	 */
	public static function byteArray2String($byteArray): string
	{
		$chars = array_map("chr", $byteArray);
		
		return implode($chars);
	}
	
	/**
	 * @param $byteArray
	 *
	 * @return string
	 */
	public static function byteArray2Hex($byteArray): string
	{
		$chars = array_map("chr", $byteArray);
		$bin = implode($chars);
		return bin2hex($bin);
	}
	
	/**
	 * @param $hexString
	 *
	 * @return array|false
	 */
	public static function hex2ByteArray($hexString)
	{
		$string = hex2bin($hexString);
		
		return unpack('C*', $string);
	}
	
	/**
	 * @param $string
	 *
	 * @return string
	 */
	public static function string2Hex($string): string
	{
		return bin2hex($string);
	}
	
	/**
	 * @param $hexString
	 *
	 * @return false|string
	 */
	public static function hex2String($hexString)
	{
		return hex2bin($hexString);
	}
}