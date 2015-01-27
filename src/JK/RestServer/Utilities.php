<?php


namespace JK\RestServer;

use ReflectionClass;
use ReflectionObject;
use ReflectionParameter;

class Utilities
{
    /**
     * Pass any content negotiation header such as Accept,
     * Accept-Language to break it up and sort the resulting array by
     * the order of negotiation.
     *
     * @static
     *
     * @param string $accept header value
     *
     * @return array sorted by the priority
     */
    public static function sortByPriority($accept)
    {
        if ($accept == '') {
            return array();
        }

        $acceptList = array();
        $accepts = explode(',', strtolower($accept));

        foreach ($accepts as $pos => $accept) {
            $parts = explode(';q=', trim($accept));
            $type = $parts[0];
            $quality = isset($parts[1]) ? floatval($parts[1]) : 1;
            $acceptList[$type] = $quality;
        }
        arsort($acceptList);

        return $acceptList;
    }

    /**
     * Converts an object into an array
     *
     * @param  object $data object
     * @return array  Array
     */
    public static function objectToArray($data)
    {
        if (is_object($data)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $data = get_object_vars($data);
        }

        if (is_array($data)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            $self_name = 'self::'.__FUNCTION__;

            return array_map($self_name, $data);
        } else {
            // Return array
            return $data;
        }
    }

    /**
     * Converts an array into an object
     *
     * @param  array|string  $data Array data
     * @return object|string Object
     */
    public static function arrayToObject($data)
    {
        if (is_array($data)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            $self_name = 'self::'.__FUNCTION__;

            return (object) array_map($self_name, $data);
        } else {
            // Return object
            return $data;
        }
    }

    /**
     * Auxiliary method to help converting a PHP array into a XML representation.
     *
     * This XML representation is one of various possible representation.
     *
     * @access protected
     * @param  array      $data      PHP array
     * @return string     XML representation
     */
    public static function arrayToXml(array $data)
    {
        $xml = '';
        foreach ($data as $key => $value) {
            $tag = (is_numeric($key)) ? 'item' : $key;

            $xml = (!empty($xml)) ? $xml : '';
            if (is_array($value)) {
                $xml .= "<$tag index=\"".$key."\">".self::arrayToXml($value)."</$tag>";
            } else {
                $xml .= "<$tag>".$value."</$tag>";
            }
        }

        return $xml;
    }

    /**
     * @param  object|string $object_or_class Object (instance of a class) or class name
     * @return ReflectionClass
     */
    public static function reflectionClassFromObjectOrClass($object_or_class)
    {
        $reflection = null;

        if (is_object($object_or_class)) {
            $reflection = new ReflectionObject($object_or_class);
        } elseif (class_exists($object_or_class)) {
            $reflection = new ReflectionClass($object_or_class);
        }

        return $reflection;
    }

    /**
     * @param object $obj Class object
     * @param string $method Class method
     * @param string $type_hint_class Class name of the type hint
     * @return array Position of parameters of type $type_hint_class
     */
    public static function getPositionsOfParameterWithTypeHint($obj, $method, $type_hint_class)
    {
        $reflection_class = Utilities::reflectionClassFromObjectOrClass($obj);
        $reflection_method = $reflection_class->getMethod($method);
        $reflection_parameters = $reflection_method->getParameters();

        $positions = array();

        /** @var ReflectionParameter $parameter */
        foreach ($reflection_parameters as $parameter) {
            $type_hint = '';
            if (isset($parameter->getClass()->name)) {
                $type_hint = $parameter->getClass()->name;
            }

            if ($type_hint == $type_hint_class) {
                $positions[$parameter->name] = $parameter->getPosition();
            }
        }

        return $positions;
    }
}
