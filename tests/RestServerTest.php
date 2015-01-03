<?php


namespace JK\RestServer\Tests;


use JK\RestServer\RestFormat;
use JK\RestServer\RestServer;

class RestServerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \JK\RestServer\RestServer */
    protected $sut;

    public function setUp()
    {
        $this->sut = new RestServer();
    }

    public function tearDown()
    {
        unset($this->sut);
    }

//    public function before()
//    {
//        unset($_SERVER['CONTENT_TYPE']);
//    }

    public function requestUriProvider()
    {
        return array(
            array('/controller/action.format', RestFormat::PLAIN),
            array('/controller/action', RestFormat::PLAIN),
            array('/controller/action.html', RestFormat::HTML),
            array('/controller/action.json', RestFormat::JSON),
            array('/controller/action.xml', RestFormat::XML)
        );
    }

    /**
     * @dataProvider requestUriProvider
     */
    public function testGetFormat($request_uri, $format)
    {
        $_SERVER['REQUEST_URI'] = $request_uri;
        $result = $this->sut->getFormat();

        $this->assertEquals($format, $result);
    }

    public function httpHeaderAcceptProvider()
    {
        return array(
            array('application/json', RestFormat::JSON),
            array('application/json-p', RestFormat::JSONP),
            array('text/html', RestFormat::HTML),
            array('text/plain', RestFormat::PLAIN),
            array('application/xml', RestFormat::XML)
        );
    }

    /**
     * @dataProvider httpHeaderAcceptProvider
     */
    public function testGetFormatViaHttpAcceptHeader($http_accept, $format)
    {
        $_SERVER['REQUEST_URI'] = '/controller/action';
        $_SERVER['HTTP_ACCEPT'] = $http_accept;

        $result = $this->sut->getFormat();

        $this->assertEquals($format, $result);
    }

    public function keyValueBodyProvider()
    {
        return array(
            array(array()),
            array(array('key1' => 'value1&specialchars=1')),
            array(array(
                'key1' => 'value1&specialchars=1',
                'key2' => 'value2',
            ))
        );
    }

    /**
     * @dataProvider keyValueBodyProvider
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
     * @dataProvider keyValueBodyProvider
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


}
