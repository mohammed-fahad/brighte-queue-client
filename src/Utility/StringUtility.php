<?php

namespace BrighteCapital\QueueClient\Utility;

class StringUtility
{
    /**
     * Convert camel case to snake case
     * @param String $input
     * @return string
     */
    public static function camelCaseToSnakeCase(string $input): string
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('_', $ret);
    }

    /**
     * Convert snake case to camel case
     * @param $input
     * @return string
     */
    public static function snakeCaseToCamelCase(string $input): string
    {
        return lcfirst(str_replace('_', '', ucwords($input, '_')));
    }
}
