<?php

namespace JK\RestServer\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use JK\RestServer\RestException;

#[CoversClass(RestException::class)]
class RestExceptionTest extends TestCase
{
    /**
     * @throws RestException
     */
    public function testRestException()
    {
        $this->expectException(RestException::class);
        $this->expectExceptionMessage('Something went wrong');

        throw new RestException(400, 'Something went wrong');
    }

    public function testRestExceptionHasACode()
    {
        $code = 400;
        $message = 'Something went wrong';
        try {
            throw new RestException($code, $message);
        } catch (RestException $e) {
            $this->assertEquals($code, $e->getCode());
            $this->assertEquals($message, $e->getMessage());
            return;
        }

        $this->fail('RestException was not thrown.');
    }
}
