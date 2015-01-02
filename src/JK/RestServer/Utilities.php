<?php


namespace JK\RestServer;


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
     * @param object $data object
     * @return array Array
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
     * @param array|string $data Array data
     * @return object Object
     */
    public static function arrayToObject($data) {
        if (is_array($data)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            $self_name = 'self::'.__FUNCTION__;
            return (object) array_map($self_name, $data);
        }
        else {
            // Return object
            return $data;
        }
    }
}