# Description

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ndberg/laravel-oauth-introspection-client.svg?style=flat-square)](https://packagist.org/packages/ndberg/laravel-oauth-introspection-client)
[![Build Status](https://img.shields.io/travis/ndberg/laravel-oauth-introspection-client/master.svg?style=flat-square)](https://travis-ci.org/ndberg/laravel-oauth-introspection-client)
[![Quality Score](https://img.shields.io/scrutinizer/g/ndberg/laravel-oauth-introspection-client.svg?style=flat-square)](https://scrutinizer-ci.com/g/ndberg/laravel-oauth-introspection-client)
[![Total Downloads](https://img.shields.io/packagist/dt/ndberg/laravel-oauth-introspection-client.svg?style=flat-square)](https://packagist.org/packages/ndberg/laravel-oauth-introspection-client)

Sorry, still in development.

Laravel oAuth Middleware for Laravel/Passport separated Resource Servers with Introspection.

It uses a small JWT decoding library to avoid unnecessary roundtrips to the Introspection Endpoint,
for example if the delivered token is invalid or expired. This is only possible, if you are able to copy
the public key from the laravel/passport server to this resource servers.

It can cache Introspection Results if you want to reduce the network traffic and don't have
ultimate high security concerns. Con of caching is, it won't detect a revoked token in the cached time.


## Installation

You can install the package via composer:

```bash
composer require ndberg/laravel-oauth-introspection-client
```

- publish config
- Copy public key from laravel/passport

Add the Middleware to the routes:
``` php
Route::middleware(['VerifyAccessToken:you-need-this-scope'])->group(function () {
    Route::get('/companies', ['as' => 'company.index', 'uses' => 'CompanyController@index']);
});
```

Add

## Usage

``` php
// Usage description here
```

## Security
If you decide to use caching of the introspection results, it will not detect revoked tokens until
the cache time of this token is expired. You have to think of you use case and your security concerns
how you want to use this function.

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email security@bergerweb.ch instead of using the issue tracker.

## Credits

- [Andreas Berger](https://github.com/ndberg)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
