<?php
/**
 * SSO Session implementation, based on this doc:
 * https://developers.staffbase.com/api/plugin-sso/
 *
 * PHP version 5.5.9
 *
 * @category  Authentication
 * @copyright 2017-2019 Staffbase, GmbH.
 * @author    Vitaliy Ivanov
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/staffbase/plugins-sdk-php
 */
 
namespace Staffbase\plugins\sdk\Exceptions;

/**
 * Class SSOAuthenticationException
 * 
 * An SSO Exception type which indicates
 * a failure during the authentication process
 * caused by invalid input.
 *
 * Can be used to identify cases which can
 * be handled with a soft http error eg.: 401.
 */
class SSOAuthenticationException extends SSOException {}
