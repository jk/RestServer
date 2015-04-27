<?php
namespace JK\RestServer\Tests\Fixtures\Controller;


use JK\RestServer\Language;

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

    /**
     * @url GET /method_with_various_doc_block_keys
     * @param1 value1
     * @flag
     * @return bool
     */
    public function methodWithVariousDocBlockKeys()
    {
        return true;
    }

    /**
     * No doc blocks key are defined
     */
    public function methodWithoutDocBlockKeys()
    {
        return true;
    }


    /**
     * @url GET /method_with_language_object_and_data
     *
     * @param Language $language Language object
     * @param string $data Request body data
     * @return array input parameter echoed
     */
    public function methodWithLanguageObjectAndData(Language $language, $data = null)
    {
        return $language->getPreferedLanguage();
    }

    /**
     * @url GET /method_with_several_verbs_to_test_preflight
     * @url POST /method_with_several_verbs_to_test_preflight
     * @url DELETE /method_with_several_verbs_to_test_preflight
     */
    public function methodWithSeveralVerbsToTestPreflight()
    {
        return true;
    }

    /**
     * @url DELETE /method_with_several_verbs_to_test_preflight_and_url_param/$param1
     */
    public function methodWithSeveralVerbsAndUrlParamToTestPreflight($param1)
    {
        return isset($param1);
    }

    /**
     * @url GET /method_with_several_verbs_to_test_preflight_and_get_params
     */
    public function methodWithSeveralVerbsAndGetParamsTestPreflight()
    {
        return $_GET['param1'];
    }

    /**
     * @noAuth
     * @url GET /string/$string
     * @url GET /string/$string/first/$first_id
     * @url GET /string/$string/first/$first_id/second/$second_id
     */
    public function wrongParameterOrder($string, $first_id = 23, $second_id = 42)
    {
        return array(
            'string' => $string,
            'first_id' => $first_id,
            'second_id' => $second_id
        );
    }
}
