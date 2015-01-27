<?php
/**
 * @project RestServer
 * @author Jens Kohl <jens.kohl@milchundzucker.de>
 * @since 2014-07-31 12:17
 */

namespace JK\RestServer;

use SplEnum;

/**
 * Class RestHttpStatusCodes
 * @package JK\RestServer
 * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
 */
class HttpStatusCodes extends SplEnum
{
    // 1xx: Informational - Request received, continuing process
    const CONT = 100;
    const SWITCHING_PROTOCOLS = 101;
    const PROCESSING = 102;

    // 2xx: Success - The action was successfully received, understood, and accepted
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const NON_AUTHORITATIVE_INFORMATION = 203;
    const NO_CONTENT = 204;
    const RESET_CONTENT = 205;
    const PARTIAL_CONTENT = 206;

    // 3xx: Redirection - Further action must be taken in order to complete the request
    const MULTIPLE_CHOICES = 300;
    const MOVED_PERMANENTLY = 301;
    const FOUND = 302;
    const SEE_OTHER = 303;
    const NOT_MODIFIED = 304;
    const USE_PROXY = 305;
    const TEMPORARY_REDIRECT = 307;

    // 4xx: Client Error - The request contains bad syntax or cannot be fulfilled
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const PAYMENT_REQUIRED = 402;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE = 406;
    const CONFLICT = 409;
    const GONE = 410;
    const LENGTH_REQUIRED = 411;
    const PRECONDITION_FAILED = 412;
    const REQUEST_ENTITY_TOO_LARGE = 413;
    const REQUEST_URI_TOO_LONG = 414;
    const UNSUPPORTED_MEDIA_TYPE = 415;
    const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const EXPECTATION_FAILED = 417;

    // 5xx: Server Error - The server failed to fulfill an apparently valid request
    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;
    const SERVICE_UNAVAILABLE = 503;
}
