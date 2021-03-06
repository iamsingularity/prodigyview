<?php
/**
 * PVTools is a class that has random tools to be utilized in an application.
 *
 * The tools in this class do not have a direct affiliation with any other class and can be
 * considered more of general tools.
 *
 * Example:
 * ```php
 * //Create random string of capital letters A -F that is 10 letters long
 * $string = PVTools::generateRandomString( 10, $chars = 'ABCDEF');
 *
 * //Search a recursive array
 * $data = array(
 * 	'fruits' => array('Strawberries', 'Oranges')
 * 	'vegetables' => array('celery', 'salad'),
 * 	'meat' => array(
 * 		'white' => array('chicken', 'turkey'),
 * 		'red' => array('beef', 'goat')
 * 	)
 * );
 *
 * $item = PVTools::arraySearchRecursive('turkey', $data);
 * ```
 * @package util
 */
class PVTools extends PVStaticObject {

	/**
	 * Generates a random string of lettters and numbers. String can be customized on the length
	 * and the characters used to generate the string.
	 *
	 * @param int $char_count The length of characters the string will be. Default is 15 chars
	 * @param string $chars The characters that will be used to make up the string. Default is
	 * 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890'
	 *
	 * @return string $string The auto generated string
	 * @access public
	 */
	public static function generateRandomString($char_count = 15, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890') {

		if (self::_hasAdapter(get_class(), __FUNCTION__))
			return self::_callAdapter(get_class(), __FUNCTION__, $char_count, $chars);

		$filtered = self::_applyFilter(get_class(), __FUNCTION__, array(
			'char_count' => $char_count,
			'chars' => $chars
		), array('event' => 'args'));
		
		$char_count = $filtered['char_count'];
		$chars = $filtered['chars'];

		$charLength = (strlen($chars) - 1);
		$returnString = $chars{rand(0, $charLength)};

		for ($i = 1; $i < $char_count; $i = strlen($returnString)) {

			$newchar = $chars{rand(0, $charLength)};

			if ($newchar != $returnString{$i - 1}) {
				$returnString .= $newchar;
			}
		}//end for

		self::_notify(get_class() . '::' . __FUNCTION__, $returnString, $char_count, $chars);
		$returnString = self::_applyFilter(get_class(), __FUNCTION__, $returnString, array('event' => 'return'));

		return $returnString;
	}

	/**
	 * Truncates a strings of text to a certain length and applies trailing characters. Generally used
	 * for
	 * creating 'Read More...' text descrptions.
	 *
	 * @param string $string The string to truncate
	 * @param int $length The length to truncate the string too. Default is 10 characters.
	 * @param string $trailing Trailing text to add at the end of string once it is truncated. Default
	 * text is '...'
	 * @param boolean $strip_tags Strips out any html tags. Default is true.
	 * @param string $allowed_tags Tags to allow if strip_tags is set to true.
	 *
	 * @return string $truncated A The string when truncated
	 * @access public
	 */
	public static function truncateText($string, $length = 10, $trailing = '...', $strip_tags = TRUE, $allowed_tags = '') {

		if (self::_hasAdapter(get_class(), __FUNCTION__))
			return self::_callAdapter(get_class(), __FUNCTION__, $string, $length, $trailing, $strip_tags, $allowed_tags);

		$filtered = self::_applyFilter(get_class(), __FUNCTION__, array(
			'string' => $string,
			'length' => $length,
			'trailing' => $trailing,
			'strip_tags' => $strip_tags,
			'allowed_tags' => $allowed_tags
		), array('event' => 'args'));
		
		$string = $filtered['string'];
		$length = $filtered['length'];
		$trailing = $filtered['trailing'];
		$strip_tags = $filtered['strip_tags'];
		$allowed_tags = $filtered['allowed_tags'];

		if ($strip_tags === TRUE && !empty($string)) {
			$string = strip_tags($string, $allowed_tags);
		}

		$truncated = '';

		if (mb_strlen($string) > $length) {
			$truncated = mb_substr($string, 0, $length) . $trailing;
		} else {
			$truncated = $string . $trailing;
		}

		self::_notify(get_class() . '::' . __FUNCTION__, $truncated, $string, $length, $trailing, $strip_tags, $allowed_tags);
		$truncated = self::_applyFilter(get_class(), __FUNCTION__, $truncated, array('event' => 'return'));

		return $truncated;
	}//end truncateText

	/**
	 * Returns the full url of the current page. Inclded in the return will be if the page is being https
	 * connect,
	 * a port if any, and the uri.
	 *
	 * @return string $url Url of the current page.
	 * @access public
	 */
	public static function getCurrentUrl() {

		if (self::_hasAdapter(get_class(), __FUNCTION__))
			return self::_callAdapter(get_class(), __FUNCTION__);

		$current_page_url = 'http';

		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
			$current_page_url .= 's';
		}

		$current_page_url .= '://';

		if ($_SERVER['SERVER_PORT'] != '80') {
			$current_page_url .= $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
		} else {
			$current_page_url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		}

		self::_notify(get_class() . '::' . __FUNCTION__, $current_page_url);
		$current_page_url = self::_applyFilter(get_class(), __FUNCTION__, $current_page_url, array('event' => 'return'));

		return $current_page_url;
	}//end getCurrentCurl

	/**
	 * Returns the current url with the uri. The url at max will only be
	 * www.example.com
	 *
	 * @return string $url The current url without the uri
	 * @access public
	 */
	public static function getCurrentBaseUrl() {

		if (self::_hasAdapter(get_class(), __FUNCTION__))
			return self::_callAdapter(get_class(), __FUNCTION__);

		$current_page_url = 'http';

		if (@$_SERVER['HTTPS'] === 'on') { $current_page_url .= 's';
		}
		$current_page_url .= '://';

		if ($_SERVER['SERVER_PORT'] != '80') {
			$current_page_url .= $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'];
		} else {
			$current_page_url .= $_SERVER['HTTP_HOST'];
		}

		self::_notify(get_class() . '::' . __FUNCTION__, $current_page_url);
		$current_page_url = self::_applyFilter(get_class(), __FUNCTION__, $current_page_url, array('event' => 'return'));

		return $current_page_url;
	}//end getCurrentCurl

	/**
	 * Takes in an array and forms that array into a query string with ? & =. Passing in array such as
	 * array('arg1'='doo', 'arg2'=>'sec''rae', 'arg3'=>'me') with return '?$arg1=doo&arg2=rae&arg3=me'
	 *
	 * @param array variables A string of variables to turn into a query string
	 *
	 * @return string The array uri into string format
	 * @access public
	 */
	public static function formUrlParameters($variables) {

		if (self::_hasAdapter(get_class(), __FUNCTION__))
			return self::_callAdapter(get_class(), __FUNCTION__, $variables);

		$variables = self::_applyFilter(get_class(), __FUNCTION__, $variables, array('event' => 'args'));

		$appendix = '?';

		$first = 1;
		foreach ($variables as $key => $value) {
			if ($first === 1) {
				$appendix .= $key . '=' . urlencode($value);
			} else {
				$appendix .= '&' . $key . '=' . urlencode($value);
			}
			$first = 0;
		}//end foreach

		self::_notify(get_class() . '::' . __FUNCTION__, $appendix, $variables);
		$appendix = self::_applyFilter(get_class(), __FUNCTION__, $appendix, array('event' => 'return'));

		return $appendix;

	}//end form url

	/**
	 * Takes in an array and forms that array into a query string with /'s. Passing in array such as
	 * array('arg1'='doo', 'arg2'=>'sec''rae', 'arg3'=>'me') with return 'doo/rae/me'
	 *
	 * @param array variables A string of variables to turn into a query string
	 *
	 * @return string The array uri into string format
	 * @access public
	 */
	public static function formUrlPath($variables) {

		if (self::_hasAdapter(get_class(), __FUNCTION__))
			return self::_callAdapter(get_class(), __FUNCTION__, $variables);

		$variables = self::_applyFilter(get_class(), __FUNCTION__, $variables, array('event' => 'args'));

		$appendix = '';

		$first = 1;
		foreach ($variables as $key => $value) {
			if ($first === 1) {
				$appendix .= urlencode($value);
			} else {
				$appendix .= '/' . urlencode($value);
			}
			$first = 0;
		}//end foreach

		self::_notify(get_class() . '::' . __FUNCTION__, $appendix, $variables);
		$appendix = self::_applyFilter(get_class(), __FUNCTION__, $appendix, array('event' => 'return'));

		return $appendix;

	}//end form url

	/**
	 * Searched for an value within another array recursively.
	 *
	 * @param mixed $needle The needle can either be a value or an array of values to be searched for
	 * @param array $haystack The array to be search in
	 * @param boolean $strict Sets if comparison is performed loosely or tightly
	 * @param array $path The path in the array in which the needle was found
	 *
	 * @return mixed $path Returns a path if the array was found, otherwise returns false
	 * @access public
	 */
	function arraySearchRecursive($needle, $haystack, $strict = false, $path = array()) {

		if (self::_hasAdapter(get_class(), __FUNCTION__))
			return self::_callAdapter(get_class(), __FUNCTION__, $needle, $haystack, $strict, $path);

		$filtered = self::_applyFilter(get_class(), __FUNCTION__, array(
			'needle' => $needle,
			'haystack' => $haystack,
			'strict' => $strict,
			'path' => $path
		), array('event' => 'args'));
		$needle = $filtered['needle'];
		$haystack = $filtered['haystack'];
		$strict = $filtered['strict'];
		$path = $filtered['path'];

		if (!is_array($haystack)) {
			return false;
		}

		if (is_array($needle)) {
			foreach ($needle as $point) {
				$return = self::arraySearchRecursive($point, $haystack, $strict, $path);
				if (!empty($return))
					$path[] = $return;
			}//dnc vpfdz h

			if (!empty($path))
				return $path;

			return false;
		}

		foreach ($haystack as $key => $value) {
			if (is_array($value) && $sub_path = self::arraySearchRecursive($needle, $value, $strict, $path)) {
				$path = array_merge($path, array($key), $sub_path);
				return $path;
			} else if ((!$strict && $value == $needle) || ($strict && $value === $needle)) {
				$path[] = $key;
				return $path;
			}
		}
		return false;
	}

	/**
	 * Parse a string into valid SQL WHERE CLAUSE based on passed parameters.
	 *
	 * @param string $string A string of parameters to parse and derive a sql arguement from
	 * @param string $content_term The parameters in the query that will relate to the values in the
	 * string
	 * @param boolean $encapsulate Wrap the arguements in ()
	 * @param string $syntax The syntax that will be used for parsing the string. Standrd uses
	 * ProdigyView implementation of marsk suchas
	 * 			',', '!','+' for parsing content. Otherwise a more sql way is used.
	 *
	 * @return string $string a SQL string to place in a where clause
	 * @access public
	 */
	public static function parseSQLOperators($string, $content_term, $encapsulate = TRUE, $syntax = 'standard') {

		if (self::_hasAdapter(get_class(), __FUNCTION__))
			return self::_callAdapter(get_class(), __FUNCTION__, $string, $content_term, $encapsulate, $syntax);

		$filtered = self::_applyFilter(get_class(), __FUNCTION__, array(
			'string' => $string,
			'content_term' => $content_term,
			'encapsulate' => $encapsulate,
			'syntax' => $syntax
		), array('event' => 'args'));
		$string = $filtered['string'];
		$content_term = $filtered['content_term'];
		$encapsulate = $filtered['encapsulate'];
		$syntax = $filtered['syntax'];

		if ($syntax === 'standard') {

			$string = trim($string);
			$string = PVDatabase::makeSafe($string);

			//$string.=$content_term
			/*
			 if( strstr($string, '!') != 1){
			 $string=$content_term.'='
			 }
			 $string=str_replace('+', ' AND '.$content_term.'=', $string );
			 $string=str_replace(',', ' OR '.$content_term.'=', $string );*/

			$length = strlen($string);

			$ADD_PREFIX = true;
			$output = '';
			for ($i = 0; $i < $length; $i++) {

				if ($string[$i] === '!') {

					$output .= ' ' . $content_term . '!=\'';

					if ($i == 0) {
						$ADD_PREFIX = false;
					}
				} else if ($string[$i] === '+') {
					if (@$string[$i + 1] != '!') {
						$output .= ' AND ' . $content_term . '=\'';
					} else {
						$output .= ' AND ';
					}
				} else if ($string[$i] === ',') {
					if (@$string[$i + 1] != '!') {
						$output .= ' OR ' . $content_term . '=\'';

					} else {
						$output .= ' OR ';
					}

				}

				if ($string[$i] != '!' && $string[$i] != '+' && $string[$i] != ',') {

					$output .= $string[$i];

					if (@$string[$i + 1] === ',' || @$string[$i + 1] === '+' || @$string[$i + 1] === '!' || $i == $length || $i == $length - 1) {
						$output .= '\'';
					}
				}
			}//end for

			if ($ADD_PREFIX == true) {
				$output = $content_term . '=\'' . $output;
			}

			if ($encapsulate) {
				$output = '(' . $output . ')';
			}

			self::_notify(get_class() . '::' . __FUNCTION__, $output, $string, $content_term, $encapsulate);
			$output = self::_applyFilter(get_class(), __FUNCTION__, $output, array('event' => 'return'));
		} else {

			$string = preg_replace('/[A-Za-z0-9%_-]+/', "'$0'", $string);

			$string = preg_replace('/\=|\!\=|<>|<|<=|>|>=|~/', "$content_term $0 ", $string);

			$string = str_replace('~', 'LIKE', $string);

			$string = preg_replace('/\&|\,/', ' AND ', $string);

			$string = str_replace('|', ' OR ', $string);

			$output = $string;
		}

		return $output;
	}//end parseSQLOperator

}//end tools
?>
