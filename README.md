# Plugin SDK for PHP

If you are developing your own plugin for your Staffbase app we describe the authentication flow of a plugin at https://developers.staffbase.com/api/plugin-sso/. While this documentation just covers the conceptual ideas of the interface of plugins though – the so called Plugin SSO – we want to provide a library to help you develop your first plugin for Staffbase even faster. This SDK provides the basic functionality to parse and verify a provided token for PHP.

## Installation

We provide our Plugin SDK via Composer (https://packagist.org/packages/staffbase/plugins-sdk-php). Thus, you can just use Composer for installation:

```
composer require staffbase/plugins-sdk-php
```

## Dependencies

Dependencies are also managed by Composer. When using this repository keep the following dependencies in mind (cf. [composer.json](composer.json)):

* php: >=5.5.9
* lcobucci/jwt: ^3.2

## API Reference

For the API reference of this SDK please consult the [docs](doc/api.md).

## Code Example

```php
use Exception;
use Staffbase\plugins\sdk\SSOToken;

try {

	$sso = new SSOToken('<rsa-public-key>', $_GET['jwt']);
	print "Hello again ". $sso->getFullName();

} catch (Exception $e) {

	print "Sorry we could not authenticate You.";
	exit;
}
```

## Contribution

- Fork it
- Create a branch `git checkout -b feature-description`
- Put your name into authors.txt
- Commit your changes `git commit -am "Added ...."`
- Push to the branch `git push origin feature-description`
- Open a Pull Request

## Running Tests

To run the tests a simple `# composer test` command in the root directory will suffice. Please consult [composer.json](composer.json) to learn which phpunit version is currently in use.


## License

Copyright 2017 Staffbase GmbH.

Licensed under the Apache License, Version 2.0: http://www.apache.org/licenses/LICENSE-2.0
