<?php
namespace JK\RestServer;

/**
 * Class Language
 * Should represent the parsed Accept-Language
 *
 * Probably will change in the future
 *
 * @package JK\RestServer
 * @see http://tools.ietf.org/html/bcp47
 */
class Language
{
    /** @var array Server Provided Languages */
    protected $supported_langauges;
    /** @var array Client Accepted Languages */
    protected $client_accepted_languages;
    /** @var array Negotiated Languages */
    protected $negotiated_langauges;
    /** @var string Default Language */
    protected $default_language;

    public function __construct(array $supported_languages, $default_langauge = 'en', $accepted_languages_string = null) {
        $this->setSupportedLangauges($supported_languages);
        $this->setDefaultLanguage(trim($default_langauge));

        if ($accepted_languages_string != null) {
            $this->parseAcceptLanguageRequestHeader($accepted_languages_string);
        }
    }

    public function parseAcceptLanguageRequestHeader($accept_language_string) {
        $this->client_accepted_languages = Utilities::sortByPriority($accept_language_string);
    }

    /**
     * @return string first negoiated language, otherwise default language
     */
    public function getPreferedLanguage() {
        $negotiated_languages = $this->getNegotiatedLangauges();

        if (count($negotiated_languages) > 0) {
            return $this->negotiated_langauges[0][0];
        } else {
            // Use default language when negoiation failed
            return $this->default_language;
        }
    }

    /**
     * @return array get all negotiated languages
     */
    public function getNegotiatedLangauges()
    {
        if (!isset($this->negotiated_langauges)) {
            $this->negotiated_langauges = $this->negotiateLanguages();
        }

        return $this->negotiated_langauges;
    }

    private function negotiateLanguages()
    {
        $negotiated_langauges = array();

        foreach ($this->client_accepted_languages as $language => $quality) {
            foreach ($this->supported_langauges as $supported) {
                if (strcasecmp($supported, $language) == 0) {
                    $negotiated_langauges[] = array($language, $quality);
                }
            }
        }

        return $negotiated_langauges;
    }

    /**
     * @return array get back all accepted languages requested by client
     */
    public function getClientAcceptedLanguages()
    {
        return $this->client_accepted_languages;
    }

    /**
     * @return array get all servier-side supported languages
     */
    public function getSupportedLangauges()
    {
        return $this->supported_langauges;
    }

    /**
     * @param array $supported_langauges A list of supported languages
     * @see http://tools.ietf.org/html/bcp47
     */
    public function setSupportedLangauges($supported_langauges)
    {
        $this->supported_langauges = $supported_langauges;
    }

    /**
     * @return string Default Language
     */
    public function getDefaultLanguage()
    {
        return $this->default_language;
    }

    /**
     * @param string $default_language Default language
     */
    public function setDefaultLanguage($default_language)
    {
        $this->default_language = $default_language;
    }
}
