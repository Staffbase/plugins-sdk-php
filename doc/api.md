# API Documentation

## Table of Contents

* [PluginSession](#pluginsession)
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
    * [getTags](#gettags)
    * [isEditor](#iseditor)
    * [isDeleteInstanceCall](#isdeleteinstancecall)
    * [getData](#getdata)
    * [__construct](#__construct)
    * [__destruct](#__destruct)
    * [base64ToPEMPublicKey](#base64topempublickey)
    * [getSessionVar](#getsessionvar)
    * [getSessionData](#getsessiondata)
    * [setSessionVar](#setsessionvar)
    * [isUserView](#isuserview)
* [SSOToken](#ssotoken)
    * [getAudience](#getaudience-1)
    * [getExpireAtTime](#getexpireattime-1)
    * [getNotBeforeTime](#getnotbeforetime-1)
    * [getIssuedAtTime](#getissuedattime-1)
    * [getIssuer](#getissuer-1)
    * [getInstanceId](#getinstanceid-1)
    * [getInstanceName](#getinstancename-1)
    * [getUserId](#getuserid-1)
    * [getUserExternalId](#getuserexternalid-1)
    * [getFullName](#getfullname-1)
    * [getFirstName](#getfirstname-1)
    * [getLastName](#getlastname-1)
    * [getRole](#getrole-1)
    * [getType](#gettype-1)
    * [getThemeTextColor](#getthemetextcolor-1)
    * [getThemeBackgroundColor](#getthemebackgroundcolor-1)
    * [getLocale](#getlocale-1)
    * [getTags](#gettags-1)
    * [isEditor](#iseditor-1)
    * [isDeleteInstanceCall](#isdeleteinstancecall-1)
    * [getData](#getdata-1)
    * [__construct](#__construct-1)
    * [base64ToPEMPublicKey](#base64topempublickey-1)

## PluginSession

A container which decrypts and stores the SSO data in a session for further requests.



* Full name: \Staffbase\plugins\sdk\PluginSession
* Parent class: \Staffbase\plugins\sdk\SSOData


### getAudience

Get targeted audience of the token.

```php
PluginSession::getAudience(  ): null|string
```







---

### getExpireAtTime

Get the time when the token expires.

```php
PluginSession::getExpireAtTime(  ): integer
```







---

### getNotBeforeTime

Get the time when the token starts to be valid.

```php
PluginSession::getNotBeforeTime(  ): integer
```







---

### getIssuedAtTime

Get the time when the token was issued.

```php
PluginSession::getIssuedAtTime(  ): integer
```







---

### getIssuer

Get issuer of the token.

```php
PluginSession::getIssuer(  ): null|string
```







---

### getInstanceId

Get the (plugin) instance id for which the token was issued.

```php
PluginSession::getInstanceId(  ): string
```

The id will always be present.





---

### getInstanceName

Get the (plugin) instance name for which the token was issued.

```php
PluginSession::getInstanceName(  ): null|string
```







---

### getUserId

Get the id of the authenticated user.

```php
PluginSession::getUserId(  ): null|string
```







---

### getUserExternalId

Get the id of the user in an external system.

```php
PluginSession::getUserExternalId(  ): null|string
```

Example use case would be to map user from an external store
to the entry defined in the token.





---

### getFullName

Get either the combined name of the user or the name of the token.

```php
PluginSession::getFullName(  ): null|string
```







---

### getFirstName

Get the first name of the user accessing.

```php
PluginSession::getFirstName(  ): null|string
```







---

### getLastName

Get the last name of the user accessing.

```php
PluginSession::getLastName(  ): null|string
```







---

### getRole

Get the role of the accessing user.

```php
PluginSession::getRole(  ): null|string
```

If this is set to “editor”, the requesting user may manage the contents
of the plugin instance, i.e. she has administration rights.
The type of the accessing entity can be either a “user” or a “editor”.





---

### getType

Get the type of the token.

```php
PluginSession::getType(  ): null|string
```

The type of the accessing entity can be either a “user” or a “token”.





---

### getThemeTextColor

Get text color used in the overall theme for this audience.

```php
PluginSession::getThemeTextColor(  ): null|string
```

The color is represented as a CSS-HEX code.





---

### getThemeBackgroundColor

Get background color used in the overall theme for this audience.

```php
PluginSession::getThemeBackgroundColor(  ): null|string
```

The color is represented as a CSS-HEX code.





---

### getLocale

Get the locale of the requesting user in the format of language tags.

```php
PluginSession::getLocale(  ): string
```







---

### getTags

Get the user tags.

```php
PluginSession::getTags(  ): array|null
```







---

### isEditor

Check if the user is an editor.

```php
PluginSession::isEditor(  ): boolean
```

Only when the editor role is explicitly
provided the user will be marked as editor.





---

### isDeleteInstanceCall

Check if the SSO call is an instance deletion call.

```php
PluginSession::isDeleteInstanceCall(  ): boolean
```

If an editor deletes a plugin instance in Staffbase
This will be true.





---

### getData

Get all stored data.

```php
PluginSession::getData(  ): array
```







---

### __construct

Constructor

```php
PluginSession::__construct( string $pluginId, string $appSecret,  $sessionHandler = null,  $leeway,  $remoteCallHandler = null )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pluginId` | **string** | the unique name of the plugin |
| `$appSecret` | **string** | application public key |
| `$sessionHandler` | **** | optional custom session handler |
| `$leeway` | **** | in seconds to compensate clock skew |
| `$remoteCallHandler` | **** | a class handling remote calls |




---

### __destruct

Destructor

```php
PluginSession::__destruct(  )
```







---

### base64ToPEMPublicKey

(DEPRECATED) Translate a base64 string to PEM encoded public key.

```php
PluginSession::base64ToPEMPublicKey( string $data ): string
```



* This method is **static**.
**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** | base64 encoded key |


**Return Value:**

PEM encoded key



---

### getSessionVar

Get a previously set session variable.

```php
PluginSession::getSessionVar( mixed $key ): mixed|null
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$key` | **mixed** |  |




---

### getSessionData

Get an array of all previously set session variables.

```php
PluginSession::getSessionData(  ): array
```







---

### setSessionVar

Set a session variable.

```php
PluginSession::setSessionVar( mixed $key, mixed $val )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$key` | **mixed** |  |
| `$val` | **mixed** |  |




---

### isUserView

Test if userView is enabled.

```php
PluginSession::isUserView(  ): array
```







---

## SSOToken

A container which is able to decrypt and store the data transmitted
from Staffbase app to a plugin using the Staffbase single-sign-on.



* Full name: \Staffbase\plugins\sdk\SSOToken
* Parent class: \Staffbase\plugins\sdk\SSOData


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
SSOToken::getLocale(  ): string
```







---

### getTags

Get the user tags.

```php
SSOToken::getTags(  ): array|null
```







---

### isEditor

Check if the user is an editor.

```php
SSOToken::isEditor(  ): boolean
```

Only when the editor role is explicitly
provided the user will be marked as editor.





---

### isDeleteInstanceCall

Check if the SSO call is an instance deletion call.

```php
SSOToken::isDeleteInstanceCall(  ): boolean
```

If an editor deletes a plugin instance in Staffbase
This will be true.





---

### getData

Get all stored data.

```php
SSOToken::getData(  ): array
```







---

### __construct

Constructor

```php
SSOToken::__construct( string $appSecret, string $tokenData, integer $leeway )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$appSecret` | **string** | Either a PEM key or a file:// URL. |
| `$tokenData` | **string** | The token text. |
| `$leeway` | **integer** | count of seconds added to current timestamp |




---

### base64ToPEMPublicKey

Translate a base64 string to PEM encoded public key.

```php
SSOToken::base64ToPEMPublicKey( string $data ): string
```



* This method is **static**.
**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** | base64 encoded key |


**Return Value:**

PEM encoded key



---



--------
> This document was automatically generated from source code comments on 2018-04-09 using [phpDocumentor](http://www.phpdoc.org/) and [cvuorinen/phpdoc-markdown-public](https://github.com/cvuorinen/phpdoc-markdown-public)
