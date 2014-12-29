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

    public function tearDown() {
        unset($this->sut);
    }

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


}
