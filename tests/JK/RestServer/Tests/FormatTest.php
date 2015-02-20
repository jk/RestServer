<?php
namespace JK\RestServer\Tests;

use JK\RestServer\Format;

/**
 * Class FormatTest
 * @package JK\RestServer\Tests
 * @covers JK\RestServer\Format
 */
class FormatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers JK\RestServer\Format::getMimeTypeFromFormatAbbreviation
     */
    public function testGetMimeTypeFromFormatAbbreviation()
    {
        $result = Format::getMimeTypeFromFormatAbbreviation('json');

        $this->assertEquals(Format::JSON, $result);
    }

    /**
     * @covers JK\RestServer\Format::getMimeTypeFromFormatAbbreviation
     */
    public function testGetMimeTypeFromFormatNonExistentAbbreviation()
    {
        $result = Format::getMimeTypeFromFormatAbbreviation('non-existent');

        $this->assertFalse($result);
    }

    /**
     * @covers JK\RestServer\Format::isMimeTypeSupported
     */
    public function testIsMimeTypeSupported()
    {
        $result = Format::isMimeTypeSupported('application/json');

        $this->assertTrue($result);
    }

    /**
     * @covers JK\RestServer\Format::isMimeTypeSupported
     */
    public function testIsMimeTypeNotSupported()
    {
        $result = Format::isMimeTypeSupported('non/existent');

        $this->assertFalse($result);
    }
}
