<?php

class AP_Array_SortByKey {
	/**
	 * array key to sort by
	 *
	 * @var string
	 */
	private static $key;

	/**
	 * Sort a multidimensional array by a given key
	 *
	 * @param array $array
	 * @param string $key
	 * @return array
	 */
	public static function sort_by_key($array, $key) {
		self::$key = $key;
		usort($array, array(self::class, 'sort_by'));
		return $array;
	}

	public static function sort_by_key_reversed($array, $key) {
		self::$key = $key;
		usort($array, array(self::class, 'sort_by_reversed'));
		return $array;
	}

	/**
	 * comparison function for usort
	 *
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	private static function sort_by($a, $b) {
		if(strtotime($b[self::$key]) === NULL)
			return (strcasecmp(strtotime($a[self::$key]), strtotime($b[self::$key])));
		else
			return (strcasecmp($a[self::$key], $b[self::$key]));
	}
	private static function sort_by_reversed($a, $b) {
		if(strtotime($b[self::$key]) === NULL)
			return (strcasecmp(strtotime($b[self::$key]), strtotime($a[self::$key])));
		else
			return (strcasecmp($b[self::$key], $a[self::$key]));
	}
}