<?php
/**
 * @project RestServer
 * @author Jens Kohl <jens.kohl@milchundzucker.de>
 * @since 2014-07-31 12:27
 */

namespace JK\RestServer;

class Mode extends \SplEnum
{
    const DEBUG = 'debug';
    const PRODUCTION = 'production';
}
