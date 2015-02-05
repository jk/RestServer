<?php


namespace JK\RestServer\Tests;

use JK\RestServer\Utilities;
use stdClass;

class TestClass
{
    public function method($param1, stdClass $param2)
    {
    }
}

class UtilitiesTest extends \PHPUnit_Framework_TestCase
{

    public function testSortByPriority()
    {
        $accept_language_str = 'de_DE;q=0.75,de;q=.6,en;q=.2';

        $result = Utilities::sortByPriority($accept_language_str);

        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('de', $result);
        $this->assertArrayHasKey('de_de', $result);
        $this->assertArrayHasKey('en', $result);

        $this->assertEquals(0.75, $result['de_de']);
        $this->assertEquals(0.6, $result['de']);
        $this->assertEquals(0.2, $result['en']);

        $last_value = null;
        foreach ($result as $key => $value) {
            $this->assertTrue(is_numeric($value));
            $this->assertTrue(($last_value == null || $last_value >= $value), 'result array should be sorted by priority');
            $last_value = $value;
        }
    }

    /**
     * @regression
     */
    public function testSortByPriorityWithEqualValuesAndReverseLexigraphicalOrder()
    {
        $accept_str = 'a,x';

        $result = Utilities::sortByPriority($accept_str);

        $this->assertInternalType('array', $result);

        $results = array();
        foreach ($result as $type => $quality) {
            $results[] = $type;
        }

        $this->assertEquals('a', $results[0], 'First element should be a, but is: ' . $results[0]);
        $this->assertEquals('x', $results[1], 'Last element should be x, but is: ' . $results[1]);


    }

    public function testSortByPriorityWithOnlyOneElementWithoutQuality()
    {
        $accept_language_str = 'de';

        $result = Utilities::sortByPriority($accept_language_str);

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('de', $result);
        $this->assertEquals(1, $result['de']);
    }

    public function testSortByPriorityWithOnlyOneElementWithQuality()
    {
        $accept_language_str = 'de;q=.75';

        $result = Utilities::sortByPriority($accept_language_str);

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('de', $result);
        $this->assertEquals(0.75, $result['de']);
    }

    public function testSortByPriorityWithTwoElementsWithoutQuality()
    {
        $str = 'de,en';

        $result = Utilities::sortByPriority($str);

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('de', $result);
        $this->assertArrayHasKey('en', $result);
        $this->assertEquals(1, $result['de']);
        $this->assertEquals(1, $result['en']);
    }

    public function testSortByPriorityWithoutElements()
    {
        $str = '';

        $result = Utilities::sortByPriority($str);

        $this->assertInternalType('array', $result);
        $this->assertCount(0, $result, 'An empty header value should lead to an empty result array');
    }

    public function testSortByPriorityWithAsterisc()
    {
        $str = '*';

        $result = Utilities::sortByPriority($str);

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('*', $result);
        $this->assertEquals(1, $result['*']);
    }

    public function testSortByPriorityWithAsteriscAndOtherValues()
    {
        $str = 'de,*;q=.75';

        $result = Utilities::sortByPriority($str);

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('de', $result);
        $this->assertArrayHasKey('*', $result);
        $this->assertEquals(1, $result['de']);
        $this->assertEquals(.75, $result['*']);
    }

    public function testSortByPriorityWithChromeAcceptLanguageString()
    {
        $str = 'de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4';

        $result = Utilities::sortByPriority($str);

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('de-de', $result);
        $this->assertArrayHasKey('de', $result);
        $this->assertArrayHasKey('en-us', $result);
        $this->assertArrayHasKey('en', $result);
        $this->assertEquals(1, $result['de-de']);
        $this->assertEquals(.8, $result['de']);
        $this->assertEquals(.6, $result['en-us']);
        $this->assertEquals(.4, $result['en']);
    }

    public function testSimpleArrayToObject()
    {
        $array = array(
            'key1' => 'value1'
        );

        $object = Utilities::arrayToObject($array);

        $this->assertObjectHasAttribute('key1', $object);
        $this->assertEquals('value1', $object->key1);
    }

    public function testTwoElementArrayToObject()
    {
        $array = array(
            'key1' => 'value1',
            'key2' => 'value2'
        );

        $object = Utilities::arrayToObject($array);

        $this->assertObjectHasAttribute('key1', $object);
        $this->assertEquals('value1', $object->key1);
        $this->assertObjectHasAttribute('key2', $object);
        $this->assertEquals('value2', $object->key2);
    }

    public function testNestedArrayToObject()
    {
        $array = array(
            'key1' => array(
                'key2' => 'value'
            )
        );

        $object = Utilities::arrayToObject($array);

        $this->assertObjectHasAttribute('key1', $object);
        $this->assertObjectHasAttribute('key2', $object->key1);
        $this->assertEquals('value', $object->key1->key2);
    }

    public function testSimpleObjectToArray()
    {
        $object = new stdClass();
        $object->key1 = 'value1';

        $array = Utilities::objectToArray($object);

        $this->assertArrayHasKey('key1', $array);
        $this->assertEquals('value1', $array['key1']);
    }

    public function testObjectWithTwoPropertiesToArray()
    {
        $object = new stdClass();
        $object->key1 = 'value1';
        $object->key2 = 'value2';

        $array = Utilities::objectToArray($object);

        $this->assertArrayHasKey('key1', $array);
        $this->assertEquals('value1', $array['key1']);
        $this->assertArrayHasKey('key2', $array);
        $this->assertEquals('value2', $array['key2']);
    }

    public function testNestedObjectToArray()
    {
        $object = new stdClass();
        $object->key1 = new stdClass();
        $object->key1->key2 = 'value2';

        $array = Utilities::objectToArray($object);

        $this->assertArrayHasKey('key1', $array);
        $this->assertArrayHasKey('key2', $array['key1']);
        $this->assertEquals('value2', $array['key1']['key2']);
    }

    public function testArrayToXmlWithEmptyArray()
    {
        $array = array();

        $xml = Utilities::arrayToXml($array);

        $this->assertEquals('', $xml);
    }

    protected function parseXmlString($xml)
    {
        $tmp  = '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
        $tmp .= "<result>".$xml.'</result>';
        return simplexml_load_string($tmp);
    }

    public function testArrayToXmlWithStringArray()
    {
        $array = array('one', 'two');

        $xml = Utilities::arrayToXml($array);
        $obj = $this->parseXmlString($xml);

        $this->assertObjectHasAttribute('item', $obj);
        $this->assertEquals(2, count($obj->item)); // can't use assertCount here, since it's not an array
        $this->assertEquals($array[0], $obj->item[0]);
        $this->assertEquals($array[1], $obj->item[1]);
    }

    public function testArrayToXmlWithNestedArray()
    {
        $array = array(
            'key1' => 'value1',
            'key2' => array(
                'key3' => 'value2'
            )
        );

        $xml = Utilities::arrayToXml($array);
        $obj = $this->parseXmlString($xml);

        $this->assertObjectHasAttribute('key1', $obj);
        $this->assertObjectHasAttribute('key2', $obj);
        $this->assertObjectHasAttribute('key3', $obj->key2);

        $this->assertEquals($array['key1'], $obj->key1);
        $this->assertEquals($array['key2']['key3'], $obj->key2->key3);
    }

    public function testReflectionClassFromObjectOrClassWithObject()
    {
        $object = new stdClass();

        $result = Utilities::reflectionClassFromObjectOrClass($object);

        $this->assertInstanceOf('ReflectionClass', $result);
        $this->assertEquals('stdClass', $result->getName());
    }

    public function testReflectionClassFromObjectOrClassWithClassName()
    {
        $result = Utilities::reflectionClassFromObjectOrClass('stdClass');

        $this->assertInstanceOf('ReflectionClass', $result);
        $this->assertEquals('stdClass', $result->getName());
    }

    public function testGetPositionsOfParameterWithTypeHint()
    {
        $test_class = new TestClass();
        $result = Utilities::getPositionsOfParameterWithTypeHint($test_class, 'method', 'stdClass');

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('param2', $result);
        $this->assertEquals(1, $result['param2']);
    }
}
