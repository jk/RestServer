<?php
namespace JK\RestServer;

class Mode extends \SplEnum
{
    /** Debug mode means no caching and more elaborate error messages */
    const DEBUG = 'debug';
    /** Production mode means caching turned on if available and not as much error messages */
    const PRODUCTION = 'production';
}
