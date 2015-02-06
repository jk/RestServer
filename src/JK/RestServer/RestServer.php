<?php
////////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2009 Jacob Wright
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
//
////////////////////////////////////////////////////////////////////////////////

namespace JK\RestServer;

use Exception;
use ReflectionException;
use ReflectionMethod;

/**
 * Description of RestServer
 *
 * @author jacob
 * @author Jens Kohl <jens.kohl@gmail.com>
 */
class RestServer
{
    public $url;
    public $method;
    public $params;
    public $format;
    public $cacheDir = '.';
    public $realm;
    /** @var Mode|string Operation mode, can be one of [debug, production] */
    public $mode;
    protected $root;

    protected $map = array();
    protected $errorClasses = array();
    protected $cached;
    protected $data;
    /** @var array Supported Languages */
    protected $supported_languages = array();
    /** @var string Default Language */
    protected $default_language = 'en';
    /** @var Format Default Format */
    protected $default_format = Format::JSON;

    /**
     * The constructor.
     *
     * @param string $mode Operation mode, can be one of [debug, production]
     * @param string $realm Can be debug or production
     */
    public function __construct($mode = Mode::PRODUCTION, $realm = 'Rest Server')
    {
        if (!in_array($mode, array(Mode::PRODUCTION, Mode::DEBUG))) {
            $mode = Mode::PRODUCTION;
        }
        $this->mode = $mode;
        $this->realm = $realm;
        if (php_sapi_name() !== 'cli') {
            $this->root = ltrim(dirname($_SERVER['SCRIPT_NAME']).DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
        } else {
            $this->root = '/';
        }
    }


    /**
     * @return string
     */
    public function getRawHttpRequestBody()
    {
        return file_get_contents('php://input');
    }

    public function __destruct()
    {
        if ($this->mode == Mode::PRODUCTION && !$this->cached) {
            if (function_exists('apc_store')) {
                apc_store('urlMap', $this->map);
            } else {
                file_put_contents($this->cacheDir.DIRECTORY_SEPARATOR.'urlMap.cache', serialize($this->map));
            }
        }
    }

    public function refreshCache()
    {
        $this->map = array();
        $this->cached = false;
    }

    public function unauthorized($ask = false)
    {
        if ($ask) {
            header("WWW-Authenticate: Basic realm=\"$this->realm\"");
        }
        throw new RestException(401, "You are not authorized to access this resource.");
    }

    public function handle()
    {
        $this->url = $this->getPath();
        $this->method = $this->getMethod();
        $this->format = $this->getFormat();

        if ($this->method == 'PUT' || $this->method == 'POST' || $this->method == 'GET') {
            try {
                $this->data = $this->getData();
            } catch (RestException $e) {
                $this->handleError($e->getCode(), $e->getMessage());
            }
        }

        list($obj, $method, $params, $this->params, $keys) = $this->findUrl();

        if ($obj) {
            if (is_string($obj)) {
                if (class_exists($obj)) {
                    $obj = new $obj();
                } else {
                    throw new Exception("Class $obj does not exist");
                }
            }

            $obj->server = $this;

            try {
                if (method_exists($obj, 'init')) {
                    $obj->init();
                }

                if (empty($keys['noAuth'])) {
                    if (method_exists($this, 'doServerWideAuthorization')) {
                        if (!$this->doServerWideAuthorization()) {
                            $this->unauthorized(false);
                        }
                    } elseif (method_exists($obj, 'authorize')) {
                        // Standard behaviour
                        if (!$obj->authorize()) {
                            $this->unauthorized(false);
                        }
                    }
                }

                $accept_language_header = isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])
                    ? $_SERVER['HTTP_ACCEPT_LANGUAGE']
                    : '';
                $language = new Language(
                    $this->supported_languages,
                    $this->default_language,
                    $accept_language_header);
                $params = $this->injectLanguageIntoMethodParameters($language, $obj, $method, $params);

                $result = call_user_func_array(array($obj, $method), $params);

                $this->automaticContentLanguageHeaderDispatch($language);

            } catch (RestException $e) {
                $this->handleError($e->getCode(), $e->getMessage());
            }

            if (!empty($result)) {
                $this->sendData($result);
            }
        } else {
            $this->handleError(404);
        }
    }

    public function addClass($class, $basePath = '')
    {
        $this->loadCache();

        if (!$this->cached) {
            if (is_string($class) && !class_exists($class)) {
                throw new \Exception('Invalid method or class');
            } elseif (!is_string($class) && !is_object($class)) {
                throw new Exception('Invalid method or class; must be a classname or object');
            }

            // Kill the leading slash
            $basePath = ltrim($basePath, '/');

            // Add a trailing slash
            if (substr($basePath, -1) != '/') {
                $basePath .= '/';
            }

            $this->generateMap($class, $basePath);
        }
    }

    public function addErrorClass($class)
    {
        $this->errorClasses[] = $class;
    }

    public function handleError($statusCode, $errorMessage = null)
    {
        $method = "handle$statusCode";
        foreach ($this->errorClasses as $class) {
            $reflection = Utilities::reflectionClassFromObjectOrClass($class);

            if (isset($reflection) && $reflection->hasMethod($method)) {
                $obj = is_string($class) ? new $class() : $class;
                $obj->$method();

                return null;
            }
        }

        $message = $this->codes[$statusCode].($errorMessage && $this->mode == Mode::DEBUG ? ': '.$errorMessage : '');

        $this->setStatus($statusCode);
        $this->sendData(array('error' => array('code' => $statusCode, 'message' => $message)));
    }

    protected function loadCache()
    {
        if ($this->cached !== null) {
            return null;
        }

        $this->cached = false;

        if ($this->mode == Mode::PRODUCTION) {
            if (function_exists('apc_fetch')) {
                $map = apc_fetch('urlMap');
            } elseif (file_exists($this->cacheDir.DIRECTORY_SEPARATOR.'urlMap.cache')) {
                $map = unserialize(file_get_contents($this->cacheDir.DIRECTORY_SEPARATOR.'urlMap.cache'));
            }
            if (isset($map) && is_array($map)) {
                $this->map = $map;
                $this->cached = true;
            }
        } else {
            if (function_exists('apc_delete')) {
                apc_delete('urlMap');
            } else {
                @unlink($this->cacheDir.DIRECTORY_SEPARATOR.'urlMap.cache');
            }
        }
    }

    protected function findUrl()
    {
        if (count($this->map) == 0) {
            return null;
        }
        $urls = $this->map[$this->method];
        if (!$urls) {
            return null;
        }

        foreach ($urls as $url => $call) {
            $args = $call[2];

            if (!strstr($url, '$')) {
                if ($url == $this->url) {
                    if (isset($args['data'])) {
                        $params = array_fill(0, $args['data'] + 1, null);
                        $params[$args['data']] = $this->data;
                        $call[2] = $params;
                    }

                    return $call;
                }
            } else {
                // Don't know what's that for: "/$something..." => "/$something"
                $regex = preg_replace('/\\\\\$([\w\d]+)\.\.\./', '(?P<$1>.+)', str_replace('\.\.\.', '...', preg_quote($url)));
                // Find named parameters in URL /$something => $matches['something'] = $something
                $regex = preg_replace('/\\\\\$([\w\d]+)/', '(?P<$1>[^\/]+)', $regex);
                if (preg_match(":^$regex$:", urldecode($this->url), $matches)) {
                    $params = array();
                    $paramMap = array();
                    if (isset($args['data'])) {
                        $params[$args['data']] = $this->data;
                    }

                    foreach ($matches as $arg => $match) {
                        if (is_numeric($arg)) {
                            continue;
                        }
                        $paramMap[$arg] = $match;

                        // is_null is here for the case there is no default value for a method parameter
                        if (isset($args[$arg]) || is_null($args[$arg])) {
                            $params[$args[$arg]] = $match;
                        }
                    }
                    ksort($params);
                    // make sure we have all the params we need
                    end($params);
                    $max = key($params);
                    for ($i = 0; $i < $max; $i++) {
                        if (!array_key_exists($i, $params)) {
                            $params[$i] = null;
                        }
                    }
                    ksort($params);
                    $call[2] = $params;
                    $call[3] = $paramMap;

                    return $call;
                }
            }
        }

        return null;
    }

    protected function generateMap($class, $basePath)
    {
        $reflection = Utilities::reflectionClassFromObjectOrClass($class);

        if (isset($reflection)) {
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        } else {
            $methods = array();
        }

        foreach ($methods as $method) {
            $doc = $method->getDocComment();
            if (preg_match_all('/@url[ \t]+(GET|POST|PUT|DELETE|HEAD|OPTIONS)[ \t]+\/?(\S*)/s', $doc, $matches, PREG_SET_ORDER)) {
                $params = $method->getParameters();

                foreach ($matches as $match) {
                    $httpMethod = $match[1];
                    $url = $this->root.$basePath.$match[2];

                    // quick fix for running on windows
                    $url = str_replace('\\', '/', $url);
                    if ($url && $url[0] == '/') {
                        $url = substr($url, 1);
                    }
                    // end quick fix

                    if ($url && $url[strlen($url) - 1] == '/') {
                        $url = substr($url, 0, -1);
                    }
                    $call = array($class, $method->getName());
                    $args = array();
                    foreach ($params as $param) {
                        // The order of the parameters is essential, there is no name-matching
                        // inserting the name is just for easier debuging
                        try {
                            $args[$param->getName()] = $param->getDefaultValue();
                        } catch (ReflectionException $e) {
                            // If the method has no default parameter set the value to null
                            $args[$param->getName()] = null;
                        }

                    }
                    $call[] = $args;
                    $call[] = null;
                    $call[] = $this->evaluateDocKeys($doc);

                    $this->map[$httpMethod][$url] = $call;
                }
            }
        }
    }

    private function evaluateDocKeys($doc)
    {
        $keysAsArray = array('url');
        if (preg_match_all('/@(\w+)([ \t](.*?))?\n/', $doc, $matches, PREG_SET_ORDER)) {
            $keys = array();
            foreach ($matches as $match) {
                if (in_array($match[1], $keysAsArray)) {
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

    public function getPath()
    {
        $path = substr(preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']), 1);
        if ($path[strlen($path) - 1] == '/') {
            $path = substr($path, 0, -1);
        }

        // remove trailing format definition, like /controller/action.json -> /controller/action
        $path = preg_replace('/\.(\w+)$/i', '', $path);

        return $path;
    }

    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }


    /**
     * Determine the requested format by the API client
     *
     * We have basically two ways requesting an output format
     * 1. The client tells us the requsted format within the URL like /controller/action.format
     * 2. The client send the Accept: header
     *
     * The order is important only if the client specifies both. If so, the 1. varient (the URL dot syntax)
     * has precedence
     *
     * @return Format|string Client requested output format
     */
    public function getFormat()
    {
        $format = $this->default_format;

        if (isset($_SERVER['HTTP_ACCEPT'])) {
            $accept_header = Utilities::sortByPriority($_SERVER['HTTP_ACCEPT']);

            foreach ($accept_header as $mime_type => $priority) {
                if (Format::isMimeTypeSupported($mime_type)) {
                    $format = $mime_type;
                    break;
                }
            }
        }

        // Check for trailing dot-format syntax like /controller/action.format -> action.json
        $override = '';
        if (preg_match('/\.(\w+)($|\?)/i', $_SERVER['REQUEST_URI'], $matches)) {
            $override = $matches[1];
        }

        if (Format::getMimeTypeFromFormatAbbreviation($override)) {
            $format = Format::getMimeTypeFromFormatAbbreviation($override);
        }

        return $format;
    }

    public function getData()
    {
        $data = $this->getRawHttpRequestBody();

        if (isset($_SERVER['CONTENT_TYPE']) && !empty($_SERVER['CONTENT_TYPE'])) {
            $components = preg_split('/\;\s*/', $_SERVER['CONTENT_TYPE']);
            if (in_array('application/x-www-form-urlencoded', $components)) {
                $a = explode('&', $data);
                $output = array();
                foreach ($a as $entry) {
                    if (strpos($entry, '=') > 0) {
                        $tmp = explode('=', $entry);
                        $output[urldecode($tmp[0])] = urldecode($tmp[1]);
                    }
                }

                return $output;
            } elseif (in_array('application/json', $components)) {
                $data = Utilities::objectToArray(json_decode($data));
            } else {
                throw new RestException(500, 'Content-Type "'.$_SERVER['CONTENT_TYPE'].'" not supported');
            }
        } else {
            $data = Utilities::objectToArray(json_decode($data));
        }

        return $data;
    }

    public function sendData($data)
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: 0");
        header('Content-Type: '.$this->format);

        if ($this->format == Format::XML) {
            $output  = '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
            $output .= "<result>".Utilities::arrayToXml($data).'</result>';
            $data = $output;
            unset($output);
        } else {
            if (is_object($data) && method_exists($data, '__keepOut')) {
                $data = clone $data;
                foreach ($data->__keepOut() as $prop) {
                    unset($data->$prop);
                }
            }
            $data = json_encode($data);

            if ($this->format == Format::JSONP) {
                if (isset($_GET['callback']) && preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $_GET['callback'])) {
                    $data = $_GET['callback'].'('.$data.')';
                } else {
                    throw new RestException(400, 'No callback given.');
                }
            }
        }

        echo $data;
    }

    public function setStatus($code)
    {
        $code .= ' '.$this->codes[strval($code)];
        header("{$_SERVER['SERVER_PROTOCOL']} $code");
    }

    /**
     * Set an URL prefix
     *
     * You can set the root to achieve something like a base directory, so
     * you don't have to prepend that directory prefix on every addClass
     * class.
     *
     * @access public
     * @param  string $root URL prefix you type into your browser
     * @return void
     */
    public function setRoot($root)
    {
        // do nothing if root isn't a valid prefix
        if (empty($root)) {
            return null;
        }

        // Kill slash padding and add a trailing slash afterwards
        $root = trim($root, '/');
        $root .= '/';
        $this->root = $root;
    }

    private $codes = array(
        '100' => 'Continue',
        '200' => 'OK',
        '201' => 'Created',
        '202' => 'Accepted',
        '203' => 'Non-Authoritative Information',
        '204' => 'No Content',
        '205' => 'Reset Content',
        '206' => 'Partial Content',
        '300' => 'Multiple Choices',
        '301' => 'Moved Permanently',
        '302' => 'Found',
        '303' => 'See Other',
        '304' => 'Not Modified',
        '305' => 'Use Proxy',
        '307' => 'Temporary Redirect',
        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '402' => 'Payment Required',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '406' => 'Not Acceptable',
        '409' => 'Conflict',
        '410' => 'Gone',
        '411' => 'Length Required',
        '412' => 'Precondition Failed',
        '413' => 'Request Entity Too Large',
        '414' => 'Request-URI Too Long',
        '415' => 'Unsupported Media Type',
        '416' => 'Requested Range Not Satisfiable',
        '417' => 'Expectation Failed',
        '500' => 'Internal Server Error',
        '501' => 'Not Implemented',
        '503' => 'Service Unavailable',
    );

    /**
     * Set the supported languages.
     *
     * If the client states via the "Accept-Language" header multiple languages, the server chooses the best match
     * from its supported languages
     *
     * @param array $supported_languages Supported Languages
     * @see http://tools.ietf.org/html/bcp47
     */
    public function setSupportedLanguages($supported_languages)
    {
        $this->supported_languages = $supported_languages;
    }

    /**
     * Set the default language in case the server can not determinie the client's language
     *
     * @param string $default_language
     * @see http://tools.ietf.org/html/bcp47
     */
    public function setDefaultLanguage($default_language)
    {
        $this->default_language = $default_language;
    }

    /**
     * @param $obj
     * @param $method
     * @param $params
     * @return mixed
     */
    protected function injectLanguageIntoMethodParameters(Language $language, $obj, $method, $params)
    {
        $position_of_language_parameter = Utilities::getPositionsOfParameterWithTypeHint($obj, $method, 'JK\RestServer\Language');
        if (count($position_of_language_parameter) > 0) {
            foreach ($position_of_language_parameter as $var_name => $position) {
                $params[$position] = $language;
                unset($params[$var_name]);
            }
            return $params;
        }
        return $params;
    }

    /**
     * Makes sure that a "Content-Language" header is sent if not already sent (i.e. from the RestServer client code)
     *
     * @param Language $language Language object
     */
    protected function automaticContentLanguageHeaderDispatch(Language $language)
    {
        $headers_sent = headers_list();
        $content_language_header_sent = false;
        foreach ($headers_sent as $header) {
            $header_components = explode(': ', $header);
            $header_name = $header_components[0];

            if (strcasecmp($header_name, 'content-language') == 0) {
                $content_language_header_sent = true;
            }
        }

        if ($content_language_header_sent === false) {
            header('Content-Language: ' . $language->getPreferedLanguage());
        }
    }

    /**
     * Setting a default output format.
     *
     * This will be used if the client does not request any specific format.
     *
     * @param string $mime_type Default format
     * @return bool Setting of default format was successful
     */
    public function setDefaultFormat($mime_type)
    {
        if (Format::isMimeTypeSupported($mime_type)) {
            $this->default_format = $mime_type;
            return true;
        } else {
            return false;
        }
    }
}
