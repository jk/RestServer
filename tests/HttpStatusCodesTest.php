<?php
namespace JK\RestServer\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use JK\RestServer\HttpStatusCodes;

/**
 * Class HttpStatusCodesTest
 */
#[CoversClass(HttpStatusCodes::class)]
class HttpStatusCodesTest extends TestCase
{
    public function testConstructorCanNotBeInstantiated()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('HttpStatusCodes class can not be instantiatiated.');

        new HttpStatusCodes();
    }

    public function testGetDescription()
    {
        $result = HttpStatusCodes::getDescription(404);

        $this->assertEquals('Not Found', $result);
    }

    public function testGetDescriptionForUnknownCode()
    {
        $result = HttpStatusCodes::getDescription(-1);

        $this->assertFalse($result);
    }
}
