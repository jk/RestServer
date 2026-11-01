<?php

namespace JK\RestServer;

class Mode
{
    /** Debug mode means no caching and more elaborate error messages */
    public const DEBUG = 'debug';
    /** Production mode means caching turned on if available and not as much error messages */
    public const PRODUCTION = 'production';
}
