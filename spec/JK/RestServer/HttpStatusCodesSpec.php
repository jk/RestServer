<?php

namespace spec\JK\RestServer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HttpStatusCodesSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('JK\RestServer\HttpStatusCodes');
    }

    function it_is_an_enumeration() {
        $this->shouldHaveType('\SplEnum');
    }
}
