<?php


namespace JK\RestServer\Tests;


use JK\RestServer\DocBlockParser;

/**
 * Class DocBlockParserTest
 * @package JK\RestServer\Tests
 * @coversDefaultClass \JK\RestServer\DocBlockParser
 */
class DocBlockParserTest extends \PHPUnit_Framework_TestCase
{
    /** @var \ReflectionMethod Reflection method with doc block keys */
    public $reflection_method_with_doc_keys;
    /** @var \ReflectionMethod Reflection method without doc block keys */
    public $reflection_method_without_doc_keys;

    public function setUp()
    {
        $reflection_class = new \ReflectionClass('\JK\RestServer\Tests\Fixtures\Controller\TestApiController');
        $this->reflection_method_with_doc_keys = $reflection_class->getMethod('methodWithVariousDocBlockKeys');
        $this->reflection_method_without_doc_keys = $reflection_class->getMethod('methodWithoutDocBlockKeys');
    }

    public function tearDown()
    {
        unset($this->reflection_method_with_doc_keys);
        unset($this->reflection_method_without_doc_keys);
    }

    /**
     * @covers ::getDocKeys()
     */
    public function testGetDocKeysKeyValue()
    {
        $result = DocBlockParser::getDocKeys($this->reflection_method_with_doc_keys);

        $this->assertArrayHasKey('param1', $result);
        $this->assertArrayHasKey('return', $result);

        $this->assertEquals('value1', $result['param1']);
        $this->assertEquals('bool', $result['return']);
    }

    /**
     * @covers ::getDocKeys()
     */
    public function testGetDocKeysUrlKeysAsArray()
    {
        $result = DocBlockParser::getDocKeys($this->reflection_method_with_doc_keys);

        $this->assertArrayHasKey('url', $result);
        $this->assertInternalType('array', $result['url']);
    }

    /**
     * @covers ::getDocKeys()
     */
    public function testGetDocKeysFlag()
    {
        $result = DocBlockParser::getDocKeys($this->reflection_method_with_doc_keys);

        $this->assertArrayHasKey('flag', $result);
        $this->assertTrue($result['flag']);
    }

    /**
     * @covers ::getDocKeys()
     */
    public function testGetDocKeysWithoutAnyKeys()
    {
        $result = DocBlockParser::getDocKeys($this->reflection_method_without_doc_keys);

        $this->assertFalse($result);
    }
}
