<?php
namespace JK\RestServer\Tests\JK\RestServer\Tests;


use JK\RestServer\HttpStatusCodes;

/**
 * Class HttpStatusCodesTest
 * @package JK\RestServer\Tests\JK\RestServer\Tests
 * @coversDefaultClass \JK\RestServer\HttpStatusCodes
 */
class HttpStatusCodesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage HttpStatusCodes class can not be instantiatiated.
     * @covers ::__construct()
     */
    public function testConstructorCanNotBeInstantiated()
    {
        new HttpStatusCodes();
    }

    /**
     * @covers ::getDescription()
     */
    public function testGetDescription()
    {
        $result = HttpStatusCodes::getDescription(404);

        $this->assertEquals('Not Found', $result);
    }

    /**
     * @covers ::getDescription()
     */
    public function testGetDescriptionForUnknownCode()
    {
        $result = HttpStatusCodes::getDescription(-1);

        $this->assertFalse($result);
    }
}
