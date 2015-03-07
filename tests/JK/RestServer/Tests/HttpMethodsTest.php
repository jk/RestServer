<?php
namespace JK\RestServer\Tests\JK\RestServer\Tests;


use JK\RestServer\HttpMethods;

/**
 * Class HttpMethodsTest
 * @package JK\RestServer\Tests\JK\RestServer\Tests
 * @coversDefaultClass \JK\RestServer\HttpMethods
 */
class HttpMethodsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::getAllMethods()
     */
    public function testGetAllMethods()
    {
        $result = HttpMethods::getAllMethods();

        $http_methods_expected = array(
            HttpMethods::GET,
            HttpMethods::POST,
            HttpMethods::PUT,
            HttpMethods::DELETE,
            HttpMethods::HEAD,
            HttpMethods::OPTIONS,
            HttpMethods::TRACE,
            HttpMethods::CONNECT
        );

        foreach ($http_methods_expected as $http_method_expected) {
            $this->assertContains($http_method_expected, $result,
                'HTTP method ' . $http_method_expected . ' is not present in getAllMethods()');
        }
    }

    /**
     * @covers ::getMethodsWhereRequestBodyIsAllowed()
     */
    public function testGetMethodsWhereRequestBodyIsAllowed()
    {
        $result = HttpMethods::getMethodsWhereRequestBodyIsAllowed();

        $http_methods_expected = array(
            HttpMethods::POST,
            HttpMethods::PUT
        );

        foreach ($http_methods_expected as $http_method_expected) {
            $this->assertContains($http_method_expected, $result,
                'HTTP method ' . $http_method_expected . ' is not present in getAllMethods()');
        }
    }
}
