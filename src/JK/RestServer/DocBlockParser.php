<?php
namespace JK\RestServer;

/**
 * Parses DocBlocks
 * @package JK\RestServer
 */
class DocBlockParser
{

    /**
     * Get doc keys from a method's docblock documentation
     *
     * @param \ReflectionMethod $reflection_method Reflected method
     * @return array|bool List of found keys, if there are none, false is returned
     */
    public static function getDocKeys(\ReflectionMethod $reflection_method)
    {
        $doc_comment_string = $reflection_method->getDocComment();

        $keys_as_array = array('url');
        if (preg_match_all('/@(\w+)([ \t](.*?))?\n/', $doc_comment_string, $matches, PREG_SET_ORDER)) {
            $keys = array();
            foreach ($matches as $match) {
                if (in_array($match[1], $keys_as_array)) {
                    $keys[$match[1]][] = $match[3];
                } else {
                    if (!isset($match[2])) {
                        $keys[$match[1]] = true;
                    } else {
                        $keys[$match[1]] = $match[3];
                    }
                }
            }

            return $keys;
        }

        return false;
    }
}
