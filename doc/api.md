# API Documentation

## Table of Contents

* [SSOToken](#ssotoken)
    * [__construct](#__construct)
    * [getAudience](#getaudience)
    * [getExpireAtTime](#getexpireattime)
    * [getNotBeforeTime](#getnotbeforetime)
    * [getIssuedAtTime](#getissuedattime)
    * [getIssuer](#getissuer)
    * [getInstanceId](#getinstanceid)
    * [getInstanceName](#getinstancename)
    * [getUserId](#getuserid)
    * [getUserExternalId](#getuserexternalid)
    * [getFullName](#getfullname)
    * [getFirstName](#getfirstname)
    * [getLastName](#getlastname)
    * [getRole](#getrole)
    * [getType](#gettype)
    * [getThemeTextColor](#getthemetextcolor)
    * [getThemeBackgroundColor](#getthemebackgroundcolor)
    * [getLocale](#getlocale)
    * [isEditor](#iseditor)
    * [getData](#getdata)

## SSOToken

A container for the data transmitted from Staffbase app to a plugin
using the Staffbase single-sign-on.



* Full name: \Staffbase\plugins\sdk\SSOToken


### __construct

Constructor

```php
SSOToken::__construct( string $appSecret, string $tokenData )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$appSecret` | **string** | Either a key or a file:// URL. |
| `$tokenData` | **string** | The token text. |




---

### getAudience

Get targeted audience of the token.

```php
SSOToken::getAudience(  ): null|string
```







---

### getExpireAtTime

Get the time when the token expires.

```php
SSOToken::getExpireAtTime(  ): integer
```







---

### getNotBeforeTime

Get the time when the token starts to be valid.

```php
SSOToken::getNotBeforeTime(  ): integer
```







---

### getIssuedAtTime

Get the time when the token was issued.

```php
SSOToken::getIssuedAtTime(  ): integer
```







---

### getIssuer

Get issuer of the token.

```php
SSOToken::getIssuer(  ): null|string
```







---

### getInstanceId

Get the (plugin) instance id for which the token was issued.

```php
SSOToken::getInstanceId(  ): string
```

The id will always be present.





---

### getInstanceName

Get the (plugin) instance name for which the token was issued.

```php
SSOToken::getInstanceName(  ): null|string
```







---

### getUserId

Get the id of the authenticated user.

```php
SSOToken::getUserId(  ): null|string
```







---

### getUserExternalId

Get the id of the user in an external system.

```php
SSOToken::getUserExternalId(  ): null|string
```

Example use case would be to map user from an external store
to the entry defined in the token.





---

### getFullName

Get either the combined name of the user or the name of the token.

```php
SSOToken::getFullName(  ): null|string
```







---

### getFirstName

Get the first name of the user accessing.

```php
SSOToken::getFirstName(  ): null|string
```







---

### getLastName

Get the last name of the user accessing.

```php
SSOToken::getLastName(  ): null|string
```







---

### getRole

Get the role of the accessing user.

```php
SSOToken::getRole(  ): null|string
```

If this is set to “editor”, the requesting user may manage the contents
of the plugin instance, i.e. she has administration rights.
The type of the accessing entity can be either a “user” or a “editor”.





---

### getType

Get the type of the token.

```php
SSOToken::getType(  ): null|string
```

The type of the accessing entity can be either a “user” or a “token”.





---

### getThemeTextColor

Get text color used in the overall theme for this audience.

```php
SSOToken::getThemeTextColor(  ): null|string
```

The color is represented as a CSS-HEX code.





---

### getThemeBackgroundColor

Get background color used in the overall theme for this audience.

```php
SSOToken::getThemeBackgroundColor(  ): null|string
```

The color is represented as a CSS-HEX code.





---

### getLocale

Get the locale of the requesting user in the format of language tags.

```php
SSOToken::getLocale(  ): null|string
```







---

### isEditor

Check if the user is an editor.

```php
SSOToken::isEditor(  ): boolean
```

The user will always have a user role to prevent a bug class
on missing values. Only when the editor role is explicitly
provided the user will be marked as editor.





---

### getData

Get all data stored in the token.

```php
SSOToken::getData(  ): array
```







---



--------
> This document was automatically generated from source code comments on 2017-03-03 using [phpDocumentor](http://www.phpdoc.org/) and [cvuorinen/phpdoc-markdown-public](https://github.com/cvuorinen/phpdoc-markdown-public)
