<?php

namespace Staffbase\plugins\sdk;

use Staffbase\plugins\sdk\SSOData\SharedData;

/**
 * @deprecated Please use \Staffbase\plugins\sdk\SSOData\SSOData
 */
abstract class SSOData
{
    use SSOData\SSOData, SharedData;
}
