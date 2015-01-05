<?php


namespace JK\RestServer\Tests;

use JK\RestServer\Utilities;
use stdClass;

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
}
