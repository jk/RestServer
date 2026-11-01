# RestServer.php
[![CI](https://github.com/jk/RestServer/actions/workflows/ci.yml/badge.svg?branch=develop)](https://github.com/jk/RestServer/actions/workflows/ci.yml)
[![Latest Stable Version](https://poser.pugx.org/jk/restserver/v/stable.svg)](https://packagist.org/packages/jk/restserver)
[![License](https://poser.pugx.org/jk/restserver/license.svg)](https://packagist.org/packages/jk/restserver)
[![Total Downloads](https://poser.pugx.org/jk/restserver/downloads.svg)](https://packagist.org/packages/jk/restserver)
[![Documentation Status](https://readthedocs.org/projects/restserver/badge/?version=develop)](https://readthedocs.org/projects/restserver/?badge=develop)

A PHP REST server for providing a very light-weight REST API. Very easy to set
up and get going. Independent from other libraries and frameworks. Supports
HTTP authentication.

As of [Classification of HTTP-based APIs][classification] this implementation
should be considered as an HTTP based type II type of an API. So it is not an
academic version of a real RESTful API, but this is by intention, so it's easy
to understand and install.

[classification]: https://algermissen.io/classification_of_http_apis.html

## Development

Requires PHP 8.3 or newer.

```sh
composer install
composer test     # run the PHPUnit test suite
composer cs       # check coding style (PER-CS)
composer cs-fix   # fix coding style
```
