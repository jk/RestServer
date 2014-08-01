<?php
/**
 * @project RestServer
 * @author Jens Kohl <jens.kohl@milchundzucker.de>
 * @since 2014-07-31 13:17
 */

namespace JK\RestServer;

/**
 * Class Language
 * Should represent the parsed Accept-Language
 *
 * Probably will change in the future
 *
 * @package JK\RestServer
 */
class Language extends \SplEnum {
    const __default = self::EN;

    const DE = "de";
    const DE_DE = "de_DE";
    const EN = "en";
}
