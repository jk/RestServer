<?php
namespace JK\RestServer\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use JK\RestServer\Format;

/**
 * Class FormatTest
 */
#[CoversClass(Format::class)]
class FormatTest extends TestCase
{
    public function testGetMimeTypeFromFormatAbbreviation()
    {
        $result = Format::getMimeTypeFromFormatAbbreviation('json');

        $this->assertEquals(Format::JSON, $result);
    }

    public function testGetMimeTypeFromFormatNonExistentAbbreviation()
    {
        $result = Format::getMimeTypeFromFormatAbbreviation('non-existent');

        $this->assertFalse($result);
    }

    public function testIsMimeTypeSupported()
    {
        $result = Format::isMimeTypeSupported('application/json');

        $this->assertTrue($result);
    }

    public function testIsMimeTypeNotSupported()
    {
        $result = Format::isMimeTypeSupported('non/existent');

        $this->assertFalse($result);
    }
}
