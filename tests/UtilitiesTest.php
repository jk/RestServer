<?php


namespace JK\RestServer\Tests;


use JK\RestServer\Utilities;

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


}
