<?php

namespace JK\RestServer\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use JK\RestServer\HttpMethods;

/**
 * Class HttpMethodsTest
 */
#[CoversClass(HttpMethods::class)]
class HttpMethodsTest extends TestCase
{
    public function testGetAllMethods()
    {
        $result = HttpMethods::getAllMethods();

        $http_methods_expected = [
            HttpMethods::GET,
            HttpMethods::POST,
            HttpMethods::PUT,
            HttpMethods::DELETE,
            HttpMethods::HEAD,
            HttpMethods::OPTIONS,
            HttpMethods::TRACE,
            HttpMethods::CONNECT,
        ];

        foreach ($http_methods_expected as $http_method_expected) {
            $this->assertContains(
                $http_method_expected,
                $result,
                'HTTP method ' . $http_method_expected . ' is not present in getAllMethods()',
            );
        }
    }

    public function testGetMethodsWhereRequestBodyIsAllowed()
    {
        $result = HttpMethods::getMethodsWhereRequestBodyIsAllowed();

        $http_methods_expected = [
            HttpMethods::POST,
            HttpMethods::PUT,
        ];

        foreach ($http_methods_expected as $http_method_expected) {
            $this->assertContains(
                $http_method_expected,
                $result,
                'HTTP method ' . $http_method_expected . ' is not present in getAllMethods()',
            );
        }
    }
}
