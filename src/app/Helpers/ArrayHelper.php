<?php

namespace Siga98\Helpers;

class ArrayHelper
{
	public static function getValueByKey(String $needle, array $arr): array
	{
		$result = [];
		
		foreach ($arr as $k => $v) {
			if (is_array($v)) {
				$tmp = self::getValueByKey($needle, $v);
				$result = array_merge($result, $tmp);
			} else {
				if ($k === $needle) {
					array_push($result, $v);
				}
			}
		}
		
		return $result;
	}
	
	public static function arraySliceAssoc($array, $keys): array
	{
		return array_intersect_key($array, array_flip($keys));
	}
	
	/**
	 * @param $array
	 *
	 * @return array
	 */
	public static function arrayFlatten($array): array
	{
		$return = [];
		
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$return = array_merge($return, array_flatten($value));
			} else {
				$return[$key] = $value;
			}
		}
//
//		array_walk_recursive($array, function ($v) use (&$result) {
//			$result[] = $v;
//		});
		
		return $return;
	}
}