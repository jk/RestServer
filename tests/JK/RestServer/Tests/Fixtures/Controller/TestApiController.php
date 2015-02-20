<?php
namespace JK\RestServer\Tests\Fixtures\Controller;


class TestApiController
{
    /**
     * Unorderd params
     * @url GET /unorderd
     * @url GET /unorderd/$param1
     * @url GET /unorderd/$param2/test/$param1
     * @url GET /unorderd/param1/$param1/param2/$param2
     */
    public function unorderd($param1 = 'default_value_1', $param2 = 'default_value_2')
    {
        return array('param1' => $param1, 'param2' => $param2);
    }

    /**
     * @url GET /without_default_parameter/$param1
     * @param mixed $param1 Parameter 1
     * @return array echoed param1
     */
    public function methodWithoutDefaultParamets($param1)
    {
        return array('param1' => $param1);
    }
}
