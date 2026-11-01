<?php

namespace JK\RestServer;

/**
 * Class RestHttpStatusCodes
 * @package JK\RestServer
 * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
 */
final class HttpStatusCodes
{
    // 1xx: Informational - Request received, continuing process
    /** Continue */
    const CONT = 100;
    /** Switching Protocols */
    const SWITCHING_PROTOCOLS = 101;
    /** Processing */
    const PROCESSING = 102;

    // 2xx: Success - The action was successfully received, understood, and accepted
    /** OK */
    const OK = 200;
    /** Created */
    const CREATED = 201;
    /** Accepted */
    const ACCEPTED = 202;
    /** Non-Authoritative Information */
    const NON_AUTHORITATIVE_INFORMATION = 203;
    /** No Content */
    const NO_CONTENT = 204;
    /** Reset Content */
    const RESET_CONTENT = 205;
    /** Partial Content */
    const PARTIAL_CONTENT = 206;

    // 3xx: Redirection - Further action must be taken in order to complete the request
    /** Multiple Choices */
    const MULTIPLE_CHOICES = 300;
    /** Moved Permanently */
    const MOVED_PERMANENTLY = 301;
    /** Found */
    const FOUND = 302;
    /** See Other */
    const SEE_OTHER = 303;
    /** Not Modified */
    const NOT_MODIFIED = 304;
    /** Use Proxy */
    const USE_PROXY = 305;
    /** Temporary Redirect */
    const TEMPORARY_REDIRECT = 307;

    // 4xx: Client Error - The request contains bad syntax or cannot be fulfilled
    /** Bad Request */
    const BAD_REQUEST = 400;
    /** Unauthorized */
    const UNAUTHORIZED = 401;
    /** Payment Required */
    const PAYMENT_REQUIRED = 402;
    /** Forbidden */
    const FORBIDDEN = 403;
    /** Not Found */
    const NOT_FOUND = 404;
    /** Method Not Alloews */
    const METHOD_NOT_ALLOWED = 405;
    /** Not Acceptable */
    const NOT_ACCEPTABLE = 406;
    /** Conflict */
    const CONFLICT = 409;
    /** Gone */
    const GONE = 410;
    /** Length Required */
    const LENGTH_REQUIRED = 411;
    /**  Precondition Failed */
    const PRECONDITION_FAILED = 412;
    /** Request Entity Too Large */
    const REQUEST_ENTITY_TOO_LARGE = 413;
    /** Request Uri Too Long */
    const REQUEST_URI_TOO_LONG = 414;
    /** Unsupported Media Type */
    const UNSUPPORTED_MEDIA_TYPE = 415;
    /** Requested Range Not Satisfiable */
    const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    /** Expectation Failed   */
    const EXPECTATION_FAILED = 417;

    // 5xx: Server Error - The server failed to fulfill an apparently valid request
    /** Internal Server Error */
    const INTERNAL_SERVER_ERROR = 500;
    /** Not Implemented */
    const NOT_IMPLEMENTED = 501;
    /** Service Unavailable */
    const SERVICE_UNAVAILABLE = 503;

    /** @var array Maps status codes to its string representation */
    protected static $codes_to_string_map = array(
        self::CONT => 'Continue',
        self::OK => 'OK',
        self::CREATED => 'Created',
        self::ACCEPTED => 'Accepted',
        self::NON_AUTHORITATIVE_INFORMATION => 'Non-Authoritative Information',
        self::NO_CONTENT => 'No Content',
        self::RESET_CONTENT => 'Reset Content',
        self::PARTIAL_CONTENT => 'Partial Content',
        self::MULTIPLE_CHOICES => 'Multiple Choices',
        self::MOVED_PERMANENTLY => 'Moved Permanently',
        self::FOUND => 'Found',
        self::SEE_OTHER => 'See Other',
        self::NOT_MODIFIED => 'Not Modified',
        self::USE_PROXY => 'Use Proxy',
        self::TEMPORARY_REDIRECT => 'Temporary Redirect',
        self::BAD_REQUEST => 'Bad Request',
        self::UNAUTHORIZED => 'Unauthorized',
        self::PAYMENT_REQUIRED => 'Payment Required',
        self::FORBIDDEN => 'Forbidden',
        self::NOT_FOUND => 'Not Found',
        self::METHOD_NOT_ALLOWED => 'Method Not Allowed',
        self::NOT_ACCEPTABLE => 'Not Acceptable',
        self::CONFLICT => 'Conflict',
        self::GONE => 'Gone',
        self::LENGTH_REQUIRED => 'Length Required',
        self::PRECONDITION_FAILED => 'Precondition Failed',
        self::REQUEST_ENTITY_TOO_LARGE => 'Request Entity Too Large',
        self::REQUEST_URI_TOO_LONG => 'Request-URI Too Long',
        self::UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
        self::REQUESTED_RANGE_NOT_SATISFIABLE => 'Requested Range Not Satisfiable',
        self::EXPECTATION_FAILED => 'Expectation Failed',
        self::INTERNAL_SERVER_ERROR => 'Internal Server Error',
        self::NOT_IMPLEMENTED => 'Not Implemented',
        self::SERVICE_UNAVAILABLE => 'Service Unavailable',
    );

    public function __construct()
    {
        throw new \Exception(__CLASS__ . ' class can not be instantiatiated.');
    }

    /**
     * Get the description for a given HTTP status code
     *
     * @paramint int $code HTTP status code
     * @return string|bool Description of the status code, otherwise false
     */
    public static function getDescription($code)
    {
        if (array_key_exists($code, self::$codes_to_string_map)) {
            return self::$codes_to_string_map[$code];
        } else {
            return false;
        }
    }
}
