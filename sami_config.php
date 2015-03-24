<?php

use Sami\Sami;
use Sami\Version\GitVersionCollection;
use Symfony\Component\Finder\Finder;

$dir = __DIR__ . '/src';
$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in($dir)
;

$versions = GitVersionCollection::create($dir)
    ->addFromTags(function ($version) { return preg_match('/^v?\d+\.\d+\.\d+$/', $version); })
    ->add('master', 'production')
    ->add('develop', 'development')
;

$options = array(
    'title'                => 'jk/restserver API',
    'versions'             => $versions,
    'build_dir'            => __DIR__.'/build/sami/%version%',
    'cache_dir'            => __DIR__.'/build/sami_cache/%version%',
    'default_opened_level' => 2,
);

return new Sami($iterator, $options);
