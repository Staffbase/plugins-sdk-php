<?php

/**
 * Get the given variable from $_REQUEST or from the url
 * @param string $variableName
 * @param mixed $default
 * @return mixed|null
 */
function getParam(string $variableName, $default = null)
{

    // Was the variable actually part of the request
    if (array_key_exists($variableName, $_REQUEST)) {
        return $_REQUEST[$variableName];
    }

    // Was the variable part of the url
    $urlParts = explode('/', preg_replace('/\?.+/', '', $_SERVER['REQUEST_URI']));
    $position = array_search($variableName, $urlParts, true);
    if ($position !== false && array_key_exists($position+1, $urlParts)) {
        return $urlParts[$position + 1];
    }

    return $default;
}
