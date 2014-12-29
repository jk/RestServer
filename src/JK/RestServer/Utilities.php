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
}