<?php


namespace JK\RestServer\Tests;

use JK\RestServer\Format;
use JK\RestServer\Mode;
use JK\RestServer\RestException;
use JK\RestServer\RestServer;

/**
 * Class RestServerTest
 * @package JK\RestServer\Tests
 * @coversDefaultClass \JK\RestServer\RestServer
 */
class RestServerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \JK\RestServer\RestServer */
    protected $sut;

    /**
     * @coversNothing
     */
    public function setUp()
    {
        // @codeCoverageIgnoreStart
        $this->sut = new RestServer();
//        $this->sut->setDefaultFormat(Format::PLAIN);
        // @codeCoverageIgnoreEnd

        unset($_SERVER['HTTP_ACCEPT']);
        unset($_SERVER['REQUEST_URI']);
        unset($_SERVER['CONTENT_TYPE']);
    }

    public function tearDown()
    {
        unset($this->sut);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        $result = new RestServer();

        $this->assertInstanceOf('\JK\RestServer\RestServer', $result);
        $this->assertEquals(Mode::PRODUCTION, $result->mode);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructorInDebugMode()
    {
        $result = new RestServer(Mode::DEBUG);

        $this->assertInstanceOf('\JK\RestServer\RestServer', $result);
        $this->assertEquals(Mode::DEBUG, $result->mode);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructorInProductionMode()
    {
        $result = new RestServer(Mode::PRODUCTION);

        $this->assertInstanceOf('\JK\RestServer\RestServer', $result);
        $this->assertEquals(Mode::PRODUCTION, $result->mode);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructorInNonExistentMode()
    {
        $result = new RestServer('non-existent');

        $this->assertInstanceOf('\JK\RestServer\RestServer', $result);
        $this->assertEquals(Mode::PRODUCTION, $result->mode);
    }

    public function requestUriProvider()
    {
        return array(
            array('/controller/action.format', Format::JSON),
            array('/controller/action', Format::JSON),
            array('/controller/action.html', Format::HTML),
            array('/controller/action.json', Format::JSON),
            array('/controller/action.xml', Format::XML)
        );
    }

    /**
     * @dataProvider requestUriProvider
     * @covers ::getFormat
     */
    public function testGetFormat($request_uri, $format)
    {
        $_SERVER['REQUEST_URI'] = $request_uri;
        $result = $this->sut->getFormat();

        $this->assertEquals($format, $result);
    }

    /**
     * @regression
     * @dataProvider keyValueBodyProvider
     * @covers ::getData
     */
    public function testGetDataWithEmptyContentType($array_input_data)
    {
        $mock = $this->mockGetRawHttpRequestBody(json_encode($array_input_data));
        $_SERVER['CONTENT_TYPE'] = '';

        try {
            $result = $mock->getData();

            $this->assertInternalType('array', $result);
            $this->assertCount(count($array_input_data), $result);
            foreach ($array_input_data as $key => $value) {
                $this->assertArrayHasKey($key, $result);
                $this->assertEquals($value, $result[$key]);
            }
        } catch (RestException  $e) {
            $this->fail('There should not be an exception thrown, when Content-Type is empty');
        }
    }

    public function httpHeaderAcceptProvider()
    {
        return array(
            array('application/json', Format::JSON),
            array('application/json;q=1,application/xml;q=.5', Format::JSON),
            array('application/json,application/xml;q=.5', Format::JSON),
            array('application/json,application/xml', Format::JSON),
            array('application/json-p', Format::JSONP),
            array('text/html', Format::HTML),
            array('text/plain', Format::PLAIN),
            array('application/xml', Format::XML)
        );
    }

    /**
     * @dataProvider httpHeaderAcceptProvider
     * @covers ::getFormat
     */
    public function testGetFormatViaHttpAcceptHeader($http_accept, $format)
    {
        $_SERVER['REQUEST_URI'] = '/controller/action';
        $_SERVER['HTTP_ACCEPT'] = $http_accept;

        $result = $this->sut->getFormat();

        $this->assertEquals($format, $result);
    }

    /**
     * @covers ::getFormat
     */
    public function testGetFormatViaUrl()
    {
        $_SERVER['REQUEST_URI'] = '/controller.text/action.json?key=/value.xml';

        $result = $this->sut->getFormat();

        $this->assertEquals(Format::JSON, $result);
    }

    /**
     * @covers ::getFormat
     */
    public function testGetFormatViaWrongUrl()
    {
        // We can't have a '?' in the middle of an URL
        $_SERVER['REQUEST_URI'] = '/controller.text?/action.txt?key=/value.xml';

        $result = $this->sut->getFormat();

        $this->assertEquals(Format::JSON, $result);
    }

    public function keyValueBodyProvider()
    {
        return array(
            array(array()),
            array(array('key1' => 'value1&specialchars=1')),
            array(
                array(
                    'key1' => 'value1&specialchars=1',
                    'key2' => 'value2',
                )
            )
        );
    }

    /**
     * @dataProvider keyValueBodyProvider
     * @covers ::getData
     */
    public function testGetDataAsWwwFormUrlencoded(array $array_input_data)
    {
        $mock = $this->prepareGetDataForWwwFormUrlencodedCall($array_input_data);

        $result = $mock->getData();

        $this->assertInternalType('array', $result);
        $this->assertCount(count($array_input_data), $result);
        foreach ($array_input_data as $key => $value) {
            $this->assertArrayHasKey($key, $result);
            $this->assertEquals($value, $result[$key]);
        }
    }

    /**
     * @dataProvider keyValueBodyProvider
     * @covers ::getData
     */
    public function testGetDataAsJsonWithoutContentType(array $array_input_data)
    {
        $mock = $this->mockGetRawHttpRequestBody(json_encode($array_input_data));
        unset($_SERVER['CONTENT_TYPE']);

        $result = $mock->getData();

        $this->assertInternalType('array', $result);
        $this->assertCount(count($array_input_data), $result);
        foreach ($array_input_data as $key => $value) {
            $this->assertArrayHasKey($key, $result);
            $this->assertEquals($value, $result[$key]);
        }
    }

    /**
     * @expectedException \JK\RestServer\RestException
     * @expectedExceptionMessage Content-Type "not/supported" not supported
     * @expectedExceptionCode 500
     * @covers ::getData
     */
    public function testGetDataAsJsonWithUnsupportedContentType()
    {
        $_SERVER['CONTENT_TYPE'] = 'not/supported';

        $this->sut->getData();
    }

    /**
     * @dataProvider keyValueBodyProvider
     * @covers ::getData
     */
    public function testGetDataAsJsonWithContentType(array $array_input_data)
    {
        $mock = $this->mockGetRawHttpRequestBody(json_encode($array_input_data));
        unset($_SERVER['CONTENT_TYPE']);
        $_SERVER['CONTENT_TYPE'] = 'application/json';

        $result = $mock->getData();

        $this->assertInternalType('array', $result);
        $this->assertCount(count($array_input_data), $result);
        foreach ($array_input_data as $key => $value) {
            $this->assertArrayHasKey($key, $result);
            $this->assertEquals($value, $result[$key]);
        }
    }

    /**
     * @param array $input_data_array Key-Value-Array input params
     * @return string application/x-www-form-urlencoded formated string
     */
    protected static function convertInputDataArrayToText(array $input_data_array)
    {
        $input_data_array = array_map(function ($value) {
            return urlencode($value);
        }, $input_data_array);

        $tmp = array();
        foreach ($input_data_array as $key => $value) {
            array_push($tmp, $key . '=' . $value);
        }
        $text_input_data = implode('&', $tmp);

        return $text_input_data;
    }

    /**
     * @param string $text_input_data
     * @return \Mockery\MockInterface|\JK\RestServer\RestServer
     */
    protected function mockGetRawHttpRequestBody($text_input_data)
    {
        $mock = \Mockery::mock('JK\RestServer\RestServer[getRawHttpRequestBody]');
        $mock->shouldReceive('getRawHttpRequestBody')->withNoArgs()->andReturn($text_input_data);

        return $mock;
    }

    /**
     * @param $array_input_data
     * @return RestServer|\Mockery\MockInterface
     */
    protected function prepareGetDataForWwwFormUrlencodedCall($array_input_data)
    {
        $text_input_data = self::convertInputDataArrayToText($array_input_data);
        $mock = $this->mockGetRawHttpRequestBody($text_input_data);
        $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';

        return $mock;
    }

    /**
     * @covers ::getPath
     */
    public function testGetPath()
    {
        $_SERVER['REQUEST_URI'] = '/controller/action.json/';

        $result = $this->sut->getPath();

        // /controller/action.json -> /controller/action
        $this->assertEquals('controller/action', $result);
    }

    public function requestMethodProvider()
    {
        return array(
            array('PUT'),
            array('GET'),
            array('DELETE'),
            array('PUSH')
        );
    }

    /**
     * @dataProvider requestMethodProvider
     * @param $request_method
     * @covers ::getMethod
     */
    public function testGetMethod($request_method)
    {
        $_SERVER['REQUEST_METHOD'] = $request_method;

        $result = $this->sut->getMethod();

        $this->assertEquals($request_method, $result);
    }

    /**
     * @group bug004
     * @group regression
     * @group integration
     * @group no-travis
     * @throws \Exception
     * @runInSeparateProcess
     * @coversNothing
     */
    public function testUrlDefaultParameters()
    {
        $this->sut->addClass(new \JK\RestServer\Tests\Fixtures\Controller\TestApiController(), 'test');
        $_SERVER['REQUEST_URI'] = '/test/unorderd';
        $_SERVER['HTTP_ACCEPT'] = Format::JSON;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        ob_start();
        $this->sut->handle();
        $result = ob_get_contents();
        ob_end_clean();

        $expected = array(
            'param1' => 'default_value_1',
            'param2' => 'default_value_2'
        );

        $this->assertJsonStringEqualsJsonString(json_encode($expected), $result);
    }

    /**
     * @group integration
     * @group no-travis
     * @throws \Exception
     * @runInSeparateProcess
     * @coversNothing
     */
    public function testUrlParameters()
    {
        $this->sut->addClass(new \JK\RestServer\Tests\Fixtures\Controller\TestApiController(), 'test');
        $_SERVER['REQUEST_URI'] = '/test/unorderd/param1/value_1/param2/value_2';
        $_SERVER['HTTP_ACCEPT'] = Format::JSON;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        ob_start();
        $this->sut->handle();
        $result = ob_get_contents();
        ob_end_clean();

        $expected = array(
            'param1' => 'value_1',
            'param2' => 'value_2'
        );

        $this->assertJsonStringEqualsJsonString(json_encode($expected), $result);
    }

    /**
     * @group regression
     * @group integration
     * @group no-travis
     * @runInSeparateProcess
     * @coversNothing
     */
    public function testAMethodWithoutDefaultParameters()
    {
        $this->sut->addClass(new \JK\RestServer\Tests\Fixtures\Controller\TestApiController(), 'test');
        $_SERVER['REQUEST_URI'] = '/test/without_default_parameter/value_1';
        $_SERVER['HTTP_ACCEPT'] = Format::JSON;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        ob_start();
        $this->sut->handle();
        $result = ob_get_contents();
        ob_end_clean();

        $expected = array(
            'param1' => 'value_1',
        );

        $this->assertJsonStringEqualsJsonString(json_encode($expected), $result);
    }

    /**
     * @covers ::setStatus()
     */
    public function testSetStatus()
    {
        unset($_SERVER['SERVER_PROTOCOL']);
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/2';

        $this->sut->setStatus(404);

        $result = $this->sut->header_manager->getStatusHeader();

        $this->assertEquals('HTTP/2 404 Not Found', $result);
    }

    /**
     * @covers ::sendData()
     * @runInSeparateProcess
     */
    public function testSendDataDefaultsToJson()
    {
        $data = array(
            'key1' => 'value1'
        );

        ob_start();
        $this->sut->sendData($data);
        $result = ob_get_contents();
        ob_end_clean();

        // Assert HTTP headers
        $this->assertEquals('no-cache, must-revalidate', $this->sut->header_manager->getHeader('Cache-Control'));
        $this->assertEquals(0, $this->sut->header_manager->getHeader('Expires'));
        $this->assertEquals(Format::JSON, $this->sut->header_manager->getHeader('Content-Type'));

        // Assert body
        $this->assertJsonStringEqualsJsonString(json_encode($data), $result);
    }

    /**
     * @covers ::sendData()
     * @runInSeparateProcess
     */
    public function testSendDataJsonP()
    {
        $data = array(
            'key1' => 'value1'
        );

        $this->sut->format = Format::JSONP;
        $_GET['callback'] = 'callback';

        ob_start();
        $this->sut->sendData($data);
        $result = ob_get_contents();
        ob_end_clean();

        // Assert HTTP headers
        $this->assertEquals('no-cache, must-revalidate', $this->sut->header_manager->getHeader('Cache-Control'));
        $this->assertEquals(0, $this->sut->header_manager->getHeader('Expires'));
        $this->assertEquals(Format::JSONP, $this->sut->header_manager->getHeader('Content-Type'));

        // Assert body
        $this->assertEquals('callback({"key1":"value1"})', $result);
    }

    /**
     * @covers ::sendData()
     * @runInSeparateProcess
     * @expectedException \JK\RestServer\RestException
     * @expectedExceptionMessage No callback given.
     * @expectedExceptionCode 400
     */
    public function testSendDataJsonPWithoutCallback()
    {
        $this->sut->format = Format::JSONP;
        unset($_GET['callback']);

        $this->sut->sendData(array());
    }

    /**
     * @covers ::sendData()
     * @runInSeparateProcess
     */
    public function testSendDataXml()
    {
        $data = array(
            'key1' => 'value1'
        );

        $this->sut->format = Format::XML;

        ob_start();
        $this->sut->sendData($data);
        $result = ob_get_contents();
        ob_end_clean();

        // Assert HTTP headers
        $this->assertEquals('no-cache, must-revalidate', $this->sut->header_manager->getHeader('Cache-Control'));
        $this->assertEquals(0, $this->sut->header_manager->getHeader('Expires'));
        $this->assertEquals(Format::XML, $this->sut->header_manager->getHeader('Content-Type'));

        // Assert body
        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0" encoding="UTF-8" ?><result><key1>value1</key1></result>',
            $result
        );
    }

    /**
     * @covers ::setRoot()
     */
    public function testSetRootToEmpty()
    {
        $result = $this->sut->setRoot(null);

        $this->assertNull($result);
    }

    /**
     * @group regression
     * @group integration
     * @group no-travis
     * @runInSeparateProcess
     * @covers ::injectLanguageIntoMethodParameters()
     */
    public function testMethodWithLanguageObjectAndData()
    {
        $this->sut->addClass(new \JK\RestServer\Tests\Fixtures\Controller\TestApiController(), 'test');
        $this->sut->setSupportedLanguages(array('en', 'de'));
        $this->sut->setDefaultLanguage('en');

        $_SERVER['REQUEST_URI'] = '/test/method_with_language_object_and_data';
        $_SERVER['HTTP_ACCEPT'] = Format::JSON;
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        ob_start();
        $this->sut->handle();
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertJsonStringEqualsJsonString(json_encode('de'), $result);

    }

    /**
     * @group integration
     * @group no-travis
     * @throws \Exception
     * @runInSeparateProcess
     * @covers ::handle()
     * @covers ::handleCorsPreflightRequest()
     * @covers ::setCorsMaxAge()
     * @covers ::setCorsAllowedOrigin()
     * @covers ::setCorsAllowedHeaders()
     */
    public function testCorsPreflightRequest()
    {
        $cors_max_age = 15;
        $cors_allowed_origin = array('http://example.tld');
        $cors_allowed_headers = array('content-type');

        $this->sut->addClass(new \JK\RestServer\Tests\Fixtures\Controller\TestApiController(), 'test');
        $this->sut->setCorsMaxAge($cors_max_age);
        $this->sut->setCorsAllowedOrigin($cors_allowed_origin);
        $this->sut->setCorsAllowedHeaders($cors_allowed_headers);

        $_SERVER['REQUEST_URI'] = '/test/method_with_several_verbs_to_test_preflight?param1=value1&param2=value2';
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] = 'accept-language, accept, content-type';
        $_SERVER['HTTP_REFERER'] = 'http://example.tld/path/';
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate, sdch';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4';


        ob_start();
        $this->sut->handle();
        ob_end_clean();

        $hm = $this->sut->header_manager;

        $this->assertEquals(implode(', ', $cors_allowed_origin), $hm->getHeader('Access-Control-Allow-Origin'));
        $this->assertTrue(is_int($hm->getHeader('Access-Control-Max-Age')),
            'Access-Control-Max-Age CORS header should be in seconds');
        $this->assertEquals($cors_max_age, $hm->getHeader('Access-Control-Max-Age'));

        $methods = explode(',', $hm->getHeader('Access-Control-Allow-Methods'));
        foreach (array('GET', 'OPTIONS', 'POST', 'DELETE') as $method) {
            $this->assertContains($method, $methods, 'Access-Control-Allow-Methods should contain: ' . $method);
        }

        $headers = explode(',', $hm->getHeader('Access-Control-Allow-Headers'));
        foreach ($cors_allowed_headers as $header) {
            $this->assertContains($header, $headers, 'Access-Control-Allow-Headers should contain: ' . $header);
        }
    }

    /**
     * @covers ::addCorsAllowedHeader()
     * @covers ::getCorsAllowedHeaders()
     */
    public function testaddCorsAllowedHeader()
    {
        $this->sut->addCorsAllowedHeader('my-test-header');

        $result = $this->sut->getCorsAllowedHeaders();

        $this->assertContains('my-test-header', $result);
    }

    /**
     * @group regression
     * @group integration
     * @group no-travis
     * @throws \Exception
     * @runInSeparateProcess
     * @covers ::handle()
     * @covers ::handleCorsPreflightRequest()
     * @covers ::setCorsMaxAge()
     * @covers ::setCorsAllowedOrigin()
     * @covers ::setCorsAllowedHeaders()
     */
    public function testCorsPreflightRequestWithUrlParam()
    {
        $cors_max_age = 15;
        $cors_allowed_origin = array('http://example.tld');
        $cors_allowed_headers = array('content-type');

        $this->sut->addClass(new \JK\RestServer\Tests\Fixtures\Controller\TestApiController(), 'test');
        $this->sut->setCorsMaxAge($cors_max_age);
        $this->sut->setCorsAllowedOrigin($cors_allowed_origin);
        $this->sut->setCorsAllowedHeaders($cors_allowed_headers);

        $_SERVER['REQUEST_URI'] = '/test/method_with_several_verbs_to_test_preflight_and_url_param/value1';
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] = 'accept-language, accept, content-type';
        $_SERVER['HTTP_REFERER'] = 'http://example.tld/path/';
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate, sdch';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4';


        ob_start();
        $this->sut->handle();
        ob_end_clean();

        $hm = $this->sut->header_manager;

        $this->assertEquals(implode(', ', $cors_allowed_origin), $hm->getHeader('Access-Control-Allow-Origin'));
        $this->assertTrue(is_int($hm->getHeader('Access-Control-Max-Age')),
            'Access-Control-Max-Age CORS header should be in seconds');
        $this->assertEquals($cors_max_age, $hm->getHeader('Access-Control-Max-Age'));

        $methods = explode(',', $hm->getHeader('Access-Control-Allow-Methods'));
        foreach (array('DELETE', 'OPTIONS') as $method) {
            $this->assertContains($method, $methods, 'Access-Control-Allow-Methods should contain: ' . $method);
        }

        $headers = explode(',', $hm->getHeader('Access-Control-Allow-Headers'));
        foreach ($cors_allowed_headers as $header) {
            $this->assertContains($header, $headers, 'Access-Control-Allow-Headers should contain: ' . $header);
        }

    }

}
