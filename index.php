<?php

include('controller\user.class.php');
include('lib\config.class.php');
include('lib\curl.class.php');
include('controller\device.class.php');
include('controller\campaign.class.php');
include('controller\logger.class.php');

use Store\User as User;
use Store\Device as Device;

$obj1 = new User\User();
