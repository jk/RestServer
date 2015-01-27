<?php


namespace JK\RestServer\Tests;

use JK\RestServer\Language;

class LanguageTest extends \PHPUnit_Framework_TestCase
{

    protected $supported_languages = array('en', 'de');
    protected $default_language = 'en';

    protected $safari_accept_language = 'de-de';
    protected $chrome_accept_language = 'de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4';

    public function testGetPreferedLanguage()
    {
        $language = new Language($this->supported_languages, $this->default_language, $this->chrome_accept_language);

        $result = $language->getPreferedLanguage();

        $this->assertInternalType('string', $result);
        $this->assertEquals('de', $result);
    }

    public function testGetPreferedLanguageWithAnyMatches()
    {
        $supported_languages = array();
        $default_langauge = 'fr';
        $accepted_languages_string = 'de';
        $language = new Language($supported_languages, $default_langauge, $accepted_languages_string);

        $result = $language->getPreferedLanguage();

        $this->assertInternalType('string', $result);
        $this->assertEquals($default_langauge, $result);
    }

    public function testGetDefaultLanguage()
    {
        $supported_languages = array();
        $default_langauge = 'de';
        $accepted_languages_string = '';
        $language = new Language($supported_languages, $default_langauge, $accepted_languages_string);

        $result = $language->getDefaultLanguage();

        $this->assertInternalType('string', $result);
        $this->assertEquals($default_langauge, $result);
    }

    public function testGetSupportedLanguages()
    {
        $supported_languages = array('de-de', 'de', 'en');
        $default_langauge = 'de';
        $accepted_languages_string = '';
        $language = new Language($supported_languages, $default_langauge, $accepted_languages_string);

        $result = $language->getSupportedLangauges();

        $this->assertInternalType('array', $result);
        for ($i = 0; $i < count($supported_languages); $i++) {
            $expected_language = $supported_languages[$i];
            $actual_language = $result[$i];
            $this->assertEquals($expected_language, $actual_language);
        }
    }

    public function testGetClientAcceptedLanguages()
    {
        $supported_languages = array();
        $default_langauge = 'de';
        $accepted_languages_string = 'fr,en';
        $language = new Language($supported_languages, $default_langauge, $accepted_languages_string);

        $result = $language->getClientAcceptedLanguages();

        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('en', $result);
        $this->assertArrayHasKey('fr', $result);
    }

    public function testGetClientAcceptedLanguagesWithEmptyString()
    {
        $supported_languages = array();
        $default_langauge = 'de';
        $accepted_languages_string = null;
        $language = new Language($supported_languages, $default_langauge, $accepted_languages_string);

        $result = $language->getClientAcceptedLanguages();

        $this->assertInternalType('array', $result);
        $this->assertCount(0, $result);
    }

    public function testNegotiatedLanguages()
    {
        $language = new Language($this->supported_languages, $this->default_language, $this->chrome_accept_language);

        $result = $language->getNegotiatedLangauges();

        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertCount(2, $result[0]);
        $this->assertEquals('de', $result[0][0]);
        $this->assertEquals(.8, $result[0][1]);
        $this->assertCount(2, $result[1]);
        $this->assertEquals('en', $result[1][0]);
        $this->assertEquals(.4, $result[1][1]);
    }
}
