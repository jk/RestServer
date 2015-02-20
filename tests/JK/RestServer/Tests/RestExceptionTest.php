<?php


namespace JK\RestServer\Tests;

use JK\RestServer\RestException;

class RestExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws RestException
     * @expectedException \JK\RestServer\RestException
     * @expectedExceptionMessage Something went wrong
     */
    public function testRestException()
    {
        throw new RestException(400, 'Something went wrong');
    }

    public function testRestExceptionHasACode()
    {
        $code = 400;
        $message = 'Something went wrong';
        try {
            throw new RestException($code, $message);
        }
        catch (RestException $e) {
            $this->assertEquals($code, $e->getCode());
            $this->assertEquals($message, $e->getMessage());
            return;
        }

        $this->fail('RestException was not thrown.');
    }
}
