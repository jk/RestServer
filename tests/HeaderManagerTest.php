<?php

namespace JK\RestServer\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use JK\RestServer\HeaderManager;

/**
 * Class HeaderManagerTest
 */
#[CoversClass(HeaderManager::class)]
class HeaderManagerTest extends TestCase
{
    /** @var HeaderManager Header manager system under test */
    protected $sut;

    protected function setUp(): void
    {
        $this->sut = new HeaderManager();
    }

    protected function tearDown(): void
    {
        unset($this->sut);
    }

    public function testAddHeader()
    {
        $name = 'Content-Type';
        $value = 'application/json';
        $this->sut->addHeader($name, $value);

        $result = $this->sut->getHeader($name);

        $this->assertEquals($value, $result);
    }

    public function testGetHeaderForAlreadyAddedHeader()
    {
        $name = 'Content-Type';
        $value = 'application/json';
        $this->sut->addHeader($name, $value);

        $result = $this->sut->getHeader($name);

        $this->assertEquals($value, $result);
    }

    public function testGetHeaderNonExistingHeader()
    {
        $result = $this->sut->getHeader('non-existing');

        $this->assertNull($result);
    }

    public function testAddHeaders()
    {
        $headers = [
            'Header1' => 'Value1',
            'Header2' => 'Value2',
        ];
        $this->sut->addHeaders($headers);

        $this->assertEquals($this->sut->getHeader('Header1'), $headers['Header1']);
        $this->assertEquals($this->sut->getHeader('Header2'), $headers['Header2']);
    }

    public function testSetStatusHeader()
    {
        $this->sut->setStatusHeader('404 Not Found', 'HTTP/2');

        $this->assertEquals('HTTP/2 404 Not Found', $this->sut->getStatusHeader());
    }

    public function testSetStatusHeaderWithoutProtocol()
    {
        $this->sut->setStatusHeader('404 Not Found');

        $this->assertEquals('HTTP/1.1 404 Not Found', $this->sut->getStatusHeader());
    }

    public function testGetStatusHeaderDefaultValue()
    {
        $this->assertEquals('HTTP/1.1 200 OK', $this->sut->getStatusHeader());
    }

    public function testSetStatusHeaderDefaultValueViaServerProtocol()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/2';
        $header_manager = new HeaderManager();

        $this->assertEquals('HTTP/2 200 OK', $header_manager->getStatusHeader());
    }

    public function testHeaderExists()
    {
        $name = 'Content-Type';
        $value = 'application/json';
        $this->sut->addHeader($name, $value);

        $result = $this->sut->headerExists($name);

        $this->assertTrue($result);
    }

    public function testRemoveHeader()
    {
        $name = 'Content-Type';
        $value = 'application/json';
        $this->sut->addHeader($name, $value);

        $this->assertTrue($this->sut->headerExists($name));

        $this->sut->removeHeader($name);

        $this->assertFalse($this->sut->headerExists($name));
    }

    #[RunInSeparateProcess]
    public function testSendAllHeaders()
    {
        if (!extension_loaded('xdebug')) {
            $this->markTestSkipped(__FUNCTION__ . ' skipped because XDebug extension is not installed');
        }

        $this->sut->addHeader('Content-Type', 'application/json');

        $this->sut->setStatusHeader('404 Not Found');

        $this->sut->sendAllHeaders();

        // BC: http_response_code() only exists in PHP 5.4+
        if (function_exists('http_response_code')) {
            $this->assertEquals(404, http_response_code());
        }

        $result = xdebug_get_headers();

        $this->assertTrue(in_array('Content-Type: application/json', $result), "Header wasn't sent.");
    }
}
