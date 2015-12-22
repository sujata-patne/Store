<?php
include('controller\user.class.php');
include('lib\config.class.php');
include('lib\curl.class.php');
include('controller\device.class.php');
include('controller\campaign.class.php');
include('controller\logger.class.php');
include "controller/direct2CG.controller.php";

use Store\User as User;
use Store\Device as Device;


$user = new User\User();

$promo = $user->PromoBannerId;
$userStatus = $user->getUserStatus();
$userId = $user->getUserId();


$operator = $user->getOperator();
$clientIp = $user->getClientIp();
$msisdn = $user->getMsisdn();
$OprSubParam = $user->getOperatorSubscribeParam($operator);

$TransId = $user->getTransId();
$Token = $user->getToken();
$deviceInfo = $user->getDeviceSize();
$mobileInfo = $user->getMobileInfo();

$mobileDocTD = $user->getLanguage(); //doc type declaration xhtml/html5
$sessionId = $user->getSessionId();
$extractParams = $user->getQueryParams();
$config = $user->getConfigData();
//$campaignDetails = $user->getCampaignDetails();

$currentPage = $user->getCurrentPage();
$hostName = $user->hostName;
