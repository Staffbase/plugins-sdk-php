<?php

namespace Staffbase\plugins\sdk\Helper;

class URLHelper
{

	/**
	 * Search a path param and return an array with the name and the following value. The value
	 * is true, if the param is only solely available
	 *
	 * @param string $paramName The name of the path parameter
	 * @example
	 *   /path/12345 -> [ "path" => 12345 ]
	 *   /path -> [ "path" => true ]
	 *
	 */
	private static function getPathParam(string $paramName): ?array
	{
		$urlParts = explode('/', preg_replace('/\?.+/', '', $_SERVER['REQUEST_URI']));
		if ($position = array_search($paramName, $urlParts, true)) {
			return [
				$paramName => $urlParts[$position + 1] ?? true
			];
		}

		return null;
	}

	/**
	 * Get the given variable from the url
	 * @param string $variableName
	 * @param mixed $default
	 * @return string|boolean|null
	 */
	public static function getParam(string $variableName, $default = null)
	{
		$paramValue = self::getPathParam($variableName);

		return $paramValue[$variableName] ?? $default;
	}

}
