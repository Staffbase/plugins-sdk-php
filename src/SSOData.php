<?php

namespace Staffbase\plugins\sdk;

use Staffbase\plugins\sdk\SSOData\SharedDataTrait;
use Staffbase\plugins\sdk\SSOData\SSODataTrait;

/**
 * @deprecated Please use \Staffbase\plugins\sdk\SSOData\SSOData
 */
abstract class SSOData
{
    use SSODataTrait, SharedDataTrait;
}
