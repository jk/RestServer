<?php
/**
 * @project RestServer
 * @author Jens Kohl <jens.kohl@milchundzucker.de>
 * @since 2014-07-31 12:32
 */

namespace JK\RestServer;

class Format extends \SplEnum
{
    const PLAIN = 'text/plain';
    const HTML = 'text/html';
    const JSON = 'application/json';
    const JSONP = 'application/json-p';
    const XML = 'application/xml';
}
