<?php
namespace JK\RestServer;

/**
 * Manages HTTP headers in a central place
 *
 * @package JK\RestServer
 */
final class HeaderManager
{
    /** @var string|null Special HTTP protocol header */
    private $status_header = null;
    /** @var array Colletion of HTTP headers */
    private $headers = array();

    /**
     * Sets some defaults
     */
    public function __construct()
    {
        $this->setStatusHeader('200 OK');
    }

    /**
     * Add one header to the header manager
     *
     * @param string $name Header name
     * @param string $value Header value
     */
    public function addHeader($name, $value)
    {
        $this->headers[trim($name)] = $value;
    }

    /**
     * Add multiple headers at once.
     *
     * The array should be an assoiated array, where the key is the header name.
     *
     * @param array $headers Headers to add
     */
    public function addHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->addHeader($name, $value);
        }
    }

    /**
     * Get Header by its name
     *
     * @param string $name Header name
     * @return string|null Header value, otherwise null if not existing
     */
    public function getHeader($name)
    {
        if ($this->headerExists($name)) {
            return $this->headers[$name];
        } else {
            return null;
        }
    }

    /**
     * Checks if the header already exists
     *
     * @param string $name Header name
     * @return bool True if header exists, otherwirse false
     */
    public function headerExists($name)
    {
        return isset($this->headers[$name]);
    }

    /**
     * Remove Header by its name
     *
     * @param string $name Header name
     */
    public function removeHeader($name)
    {
        if (array_key_exists($name, $this->headers)) {
            unset($this->headers[$name]);
        }
    }

    /**
     * Set the HTTP status header
     *
     * @param string $status HTTP status header (e.g. 200 OK)
     * @param string $protocol Server protocol [HTTP/1.1 by default]
     */
    public function setStatusHeader($status, $protocol = null)
    {
        if (is_null($protocol) && isset($_SERVER['SERVER_PROTOCOL'])) {
            $protocol = $_SERVER['SERVER_PROTOCOL'];
        } elseif (is_null($protocol) && !isset($_SERVER['SERVER_PROTOCOL'])) {
            $protocol = 'HTTP/1.1';
        }

        $this->status_header = trim($protocol . ' ' . $status);
    }

    /**
     * Get HTTP status header
     *
     * @return string HTTP status header
     */
    public function getStatusHeader()
    {
        return $this->status_header;
    }

    /**
     * Send all headers at once
     */
    public function sendAllHeaders()
    {
        if ($this->status_header !== null) {
            header($this->status_header);
        }

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
    }
}
