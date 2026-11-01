<?php


namespace JK\RestServer;

use Exception;

/**
 * Representes an exception which can be passed back to the client (like 404 Not found)
 * @package JK\RestServer
 * @author Jens Kohl <jens.kohl@gmail.com>
 * @since 2015-02-09
 */
class RestException extends Exception
{
    /**
     * This exception eases to propagate some error deep in the RestServer code back to client. The error code matches
     * the HTTP status code.
     *
     * # Example
     * ```php
     * throw new RestException(404, 'User not found')
     * ```
     *
     * will give you `HTTP/1.1 404 Not found` with body 'Not found: User not found'
     *
     * @param int $code Error and HTTP status code
     * @param string $message An optional message
     * @see \JK\RestServer\HttpStatusCodes
     */
    public function __construct($code, $message = null)
    {
        parent::__construct($message, $code);
    }
}
