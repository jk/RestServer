<?php

namespace JK\RestServer\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use JK\RestServer\DocBlockParser;

/**
 * Class DocBlockParserTest
 */
#[CoversClass(DocBlockParser::class)]
class DocBlockParserTest extends TestCase
{
    /** @var \ReflectionMethod Reflection method with doc block keys */
    public $reflection_method_with_doc_keys;
    /** @var \ReflectionMethod Reflection method without doc block keys */
    public $reflection_method_without_doc_keys;

    protected function setUp(): void
    {
        $reflection_class = new \ReflectionClass('\JK\RestServer\Tests\Fixtures\Controller\TestApiController');
        $this->reflection_method_with_doc_keys = $reflection_class->getMethod('methodWithVariousDocBlockKeys');
        $this->reflection_method_without_doc_keys = $reflection_class->getMethod('methodWithoutDocBlockKeys');
    }

    protected function tearDown(): void
    {
        unset($this->reflection_method_with_doc_keys);
        unset($this->reflection_method_without_doc_keys);
    }

    public function testGetDocKeysKeyValue()
    {
        $result = DocBlockParser::getDocKeys($this->reflection_method_with_doc_keys);

        $this->assertArrayHasKey('param1', $result);
        $this->assertArrayHasKey('return', $result);

        $this->assertEquals('value1', $result['param1']);
        $this->assertEquals('bool', $result['return']);
    }

    public function testGetDocKeysUrlKeysAsArray()
    {
        $result = DocBlockParser::getDocKeys($this->reflection_method_with_doc_keys);

        $this->assertArrayHasKey('url', $result);
        $this->assertIsArray($result['url']);
    }

    public function testGetDocKeysFlag()
    {
        $result = DocBlockParser::getDocKeys($this->reflection_method_with_doc_keys);

        $this->assertArrayHasKey('flag', $result);
        $this->assertTrue($result['flag']);
    }

    public function testGetDocKeysWithoutAnyKeys()
    {
        $result = DocBlockParser::getDocKeys($this->reflection_method_without_doc_keys);

        $this->assertFalse($result);
    }
}
