<?php
namespace Store\User;
use Store\Config as Config;
use Store\Curl as Curl;
use Store\Campaign as Campaign;
use Store\Device as Device;
use Store\Logger as Logger;


use ScientiaMobile\WurflCloud as WurflCloud;
class User {
	private $config;
	public $response;
	private $msisdn;
	private $imsi;
	private $detectDevice;
	public $sessionId;
	public $clientIp;
	private $isAllowed;
	private $userId;
	public $userStatus;
	public $operator;
	public $authBy;
	public  $price_point;
	private $currentPageName;
 	public $userAgent;
	public $url;
	public $deviceDetails;
	private $campaignDetails;
	public $refferer;
	public $currentURL;
	public $logger;
	public $userSubscribeInfo;

	public $PromoBannerId;
	public $curlMethods;
	public $serverUri;
	public $urlPath;
	public $hostName;
	public $extractParamFromQueryParameters;
	public static $promoId;
	public static $tid;

	const NONE = 'none';
	const REQUESTSOURCE = 'WAP';
	const UNKNOWN = 'UNKNOWN';
	const UNSUBPENDING = 'UNSUBPENDING';
	const UNSUBSCRIBED = 'UNSUBSCRIBED';
	const NEWUSER = 'NEWUSER';
	const NOAUTH = 'NO AUTH';
	const ByDB = 1;
	const ByIP = 2;
	const ByMSISDN = 3;
	const ByIMSI = 4;

	//public function __construct() {
	public function __construct() {
		//get configured elements
		$this->config = new Config\Config();
		$this->curlMethods = new Curl\Curl();

		$this->STOREID 	= Config\Config::STOREID;
		$this->BGWAPPID = Config\Config::BGWAPPID;
		$this->UID 		= Config\Config::UID;
		$this->STORENAME = Config\Config::STORENAME;
		$this->CookieTag = Config\Config::CookieTag;
		$this->hostName = "http://".$_SERVER['HTTP_HOST'];

		$this->currentURL = $this->setCurrentURL();
		$this->serverUri = parse_url($_SERVER['REQUEST_URI']); // [path] => / & [query] => abc=1
		$this->urlPath = !empty($this->serverUri['path']) ? $this->serverUri['path'] : '';

		$this->queryParameters = isset($this->serverUri['query']) ? $this->serverUri['query'] : null;		// abc=1
		parse_str($this->queryParameters, $this->extractParamFromQueryParameters);

		$this->setPromoTransactionCookie();

		// Get MSISDN, IMSI, Ip and User Agent
		 $this->setMsidsn();
		 $this->setIMSI();
		 $this->setClientIpAddress();
		 $this->setUserAgent();

		// Initialize PromoId and TransactionId if any
		$this->setPromoId();
		$this->setTransactionId();

		$this->PromoBannerId = self::$promoId;
		$this->TransactionId = self::$tid;

		// Promo Ids for Interim Page
		//$this->PromoInterim = $this->config->promoInterim;

		// Get Current page filename
		$this->setCurrentPage();

		// Create a Mobile Detection object
		$this->detectMobile();

		//get allowed devices
		$this->isAllowed();

		if( $this->isAllowed == 'true' ){
			//get user details
			//		$this->setUserProfileDetails();

			$this->setUserDetails();
			$this->setUniqueSessionId();
			$this->setRefferer();

			$this->setDeviceDetails();
			$this->setLogData();
			$this->setCapaignDetails();
			//remove once campaign works
			$this->price_point = $this->config->operatorData[$this->operator]['DefaultPP'];
			$this->tokenId = $this->sessionId.'-'.$this->STOREID.'-0-0';
		}else{
			header("Location: index.html");
			exit();
		}

	}

	public function getConfigData(){
		return $this->config;
	}
	public function getQueryParams(){
		return $this->extractParamFromQueryParameters;
	}

	public function setLogData(){
		$logData = array('url' => $this->url, 'msisdn' => $this->msisdn, 'clientIp' => $this->clientIp,
			'imsi' => $this->imsi, 'operator' => $this->operator, 'userAgent' => $this->userAgent, 'sessionId' => $this->sessionId,
			'authBy' => $this->authBy, 'userStatus' => $this->userStatus, 'response' => $this->response, 'make' => $this->deviceDetails->make,
			'model' => $this->deviceDetails->model, 'currentURL' => $this->currentURL, 'refferer' => $this->refferer,
			'deviceId' => $this->deviceDetails->deviceId, 'deviceWidth' => $this->deviceDetails->deviceWidth,
			'deviceHeight' => $this->deviceDetails->deviceHeight, 'requestFrom' => $this->requestFrom, 'storeId' => $this->STOREID,
			'uid' => $this->UID, 'promoBannerId' => $this->PromoBannerId);

			$this->logger = new Logger\Logger($logData);

			$this->logger->logVisitors();
	}

	public function setDeviceDetails(){

		$data = array(
			'userAgent' => $this->userAgent,
			'msisdn' => $this->msisdn,
			'imsi' => $this->imsi,
 			'operator' => $this->operator,
			//'config' => $this->config
 		);

		$this->deviceDetails = new Device\Device($data);
 		if( $this->msisdn != self::UNKNOWN && $this->msisdn != '' && $this->msisdn != null && $this->operator != self::UNKNOWN ) {
			$this->deviceDetails->setIMSIContent();
		}
	}
	public function getMobileInfo(){
		return $this->deviceDetails->mobileInfo;
	}

	public function getDeviceSize(){
		return array(
			'Width' => $this->deviceDetails->getDeviceWidth(),
			'Height' => $this->deviceDetails->getDeviceHeight()
		);
	}

	public function getLanguage(){
		return $this->deviceDetails->lang;
	}

	public function setCapaignDetails(){
		$data = array(
			'promoBannerId' => self::$promoId,
			'transactionId' => self::$tid,
			'appId' => $this->STOREID,
			'sessionId' => $this->sessionId,
			'operator' => $this->operator,
			'promoParameters' => $this->extractParamFromQueryParameters,
		);

		$this->campaignDetails = new Campaign\Campaign($data);
	//	echo "<pre>"; print_r($this->campaignDetails);
	}

	private function setPromoTransactionCookie(){
		if(isset($_COOKIE[$this->CookieTag.'_promo']) and !empty($_COOKIE[$this->CookieTag.'_promo'])){

			self::$promoId = $_COOKIE[$this->CookieTag.'_promo'];
			self::$tid = $_COOKIE[$this->CookieTag.'_tid'];

			$checkForNonBannerId = explode('_',self::$promoId);

			if($checkForNonBannerId[0] != 'z'){
				self::$promoId = $checkForNonBannerId[0];
			}else{
				self::$promoId = 'z_'.uniqid();
				self::$tid = 0;
			}
			$this->setPromoId();
			$this->setTransactionId();
		}else{
			$this->setPromoId();
			$this->setTransactionId();
		}
		$this->config->setCookie($this->CookieTag.'_promo', self::$promoId);
		$this->config->setCookie($this->CookieTag.'_tid', self::$tid);
	}

	private function setPromoId(){
		if(isset($this->extractParamFromQueryParameters['promo']) && !empty($this->extractParamFromQueryParameters['promo'])) {
			self::$promoId = $this->extractParamFromQueryParameters['promo'];
		}else{
			self::$promoId = 'z_'.uniqid();
		}
	}

	private function setTransactionId(){
		if( isset($this->extractParamFromQueryParameters['transaction_id']) ){
			self::$tid = $this->extractParamFromQueryParameters['transaction_id'];
		}elseif( isset($this->extractParamFromQueryParameters['tid'])){
			self::$tid = $this->extractParamFromQueryParameters['tid'];
		}elseif(isset($this->extractParamFromQueryParameters['af_tid'])){
			self::$tid = $this->extractParamFromQueryParameters['af_tid'];
		}elseif( isset($this->extractParamFromQueryParameters['referrer']) ){
			self::$tid = $this->extractParamFromQueryParameters['referrer'];
			foreach($this->extractParamFromQueryParameters as $key => $value){
				if($key != 'c' and $key != 'promo' and $key != 'referrer'){
					self::$tid .= '&'.$key.'='.$value;
				}
			}
			self::$tid = rawurlencode(self::$tid);
		}elseif( isset($this->extractParamFromQueryParameters['click_id']) ){
			self::$tid = $this->extractParamFromQueryParameters['click_id'];
		}elseif( isset($this->extractParamFromQueryParameters['vserv']) ){
			self::$tid = $this->extractParamFromQueryParameters['vserv'];
		}elseif( isset($this->extractParamFromQueryParameters['track_no']) ){
			self::$tid = $this->extractParamFromQueryParameters['track_no'];
		}elseif( isset($this->extractParamFromQueryParameters['adv_sub']) ){
			self::$tid = $this->extractParamFromQueryParameters['adv_sub'];
		}elseif( isset($this->extractParamFromQueryParameters['subid']) ){
			self::$tid = $this->extractParamFromQueryParameters['subid'];
		}elseif( isset($this->extractParamFromQueryParameters['sub_id']) ){
			self::$tid = $this->extractParamFromQueryParameters['sub_id'];
		}elseif( isset($this->extractParamFromQueryParameters['kp']) ){
			self::$tid = $this->extractParamFromQueryParameters['kp'];
		}elseif( isset($this->extractParamFromQueryParameters['clickID']) ){
			self::$tid = $this->extractParamFromQueryParameters['clickID'];
		}elseif( isset($this->extractParamFromQueryParameters['rcid']) ){
			self::$tid = $this->extractParamFromQueryParameters['rcid'];
		}elseif( isset($this->extractParamFromQueryParameters['uid']) ){
			self::$tid = $this->extractParamFromQueryParameters['uid'];
		}elseif( isset($this->extractParamFromQueryParameters['aff_sub']) ){
			self::$tid = $this->extractParamFromQueryParameters['aff_sub'];
		}elseif( isset($this->extractParamFromQueryParameters['clickid']) ){
			self::$tid = $this->extractParamFromQueryParameters['clickid'];
		}elseif( isset($this->extractParamFromQueryParameters['click_ID']) ){
			self::$tid = $this->extractParamFromQueryParameters['click_ID'];
		}elseif( isset($this->extractParamFromQueryParameters['kc']) ){
			self::$tid = $this->extractParamFromQueryParameters['kc'];
		}else{
			self::$tid = 0;
		}
	}

	private function setAuthBy($authby){
		switch ($authby) {
			case 1: $this->authBy = self::ByDB; break;
			case 2: $this->authBy = self::ByIP; break;
			case 3: $this->authBy = self::ByMSISDN; break;
			case 4: $this->authBy = self::ByIMSI; break;
			default: $this->authBy = self::NOAUTH;
		}
	}
	private function setUserProfileDetails(){
		$data['user_id'] = $this->userId;
		$data['bgw_AppID'] = $this->BGWAPPID;
		$content = $this->curlMethods->executePostCurl(USERPROFILE, $data);

		$this->userSubscribeInfo = json_decode($content['Content'], true);
		//echo "<pre>"; print_r($this->userSubscribeInfo); exit;
	}

	private function setUserDetails(){
		$extractInfo = $this->getMsisdnDetails();
		// Set authService Response for log purpose
		$this->response = $extractInfo['Content'];

		if($extractInfo['Response']['user_status'] == 'BLOCKED'){
			header("Location: ".$this->hostName.'/block.html');
			exit();
		}
		if($extractInfo['Response']['user_id'] != 0){
			$this->userId = $extractInfo['Response']['user_id'];
			$this->userStatus = $extractInfo['Response']['user_status'];
			$this->operator = $extractInfo['Response']['operator'];

			if( isset($extractInfo['Response']['authby']) ){
				$this->setAuthBy($extractInfo['Response']['authby']);
			}else{
				$this->authBy = self::NOAUTH;
			}

			$this->requestFrom = isset($extractInfo['Response']['servReqSource']) ? $extractInfo['Response']['servReqSource'] : self::REQUESTSOURCE;

			if($this->userStatus != self::NEWUSER && $this->userStatus != self::UNKNOWN && $this->userStatus != self::UNSUBSCRIBED ){
				$_SESSION['downloadAllowed'] = 'true';
			}else{
				$_SESSION['downloadAllowed'] = 'false';
			}
			//get operator and last subscribed pricepoint details
			if( $this->userStatus != self::NEWUSER && $this->userStatus != self::UNSUBSCRIBED && $this->userStatus != self::UNKNOWN){
				// Logic to get current subscribed number price point
				$SubPackData['AppId'] = $this->STOREID;
				$SubPackData['user_id'] = $this->userId;

				$content = $this->curlMethods->executePostCurl(S3STATUS, $SubPackData);

				$outputSubPack = json_decode($content['Content'], true);
				//echo "<pre>"; print_r($outputSubPack);
				$this->price_point = (string)$outputSubPack['price_point'];
			}
		}else{
			$this->userId = self::UNKNOWN;
			$this->userStatus = self::UNKNOWN;
			$this->operator = self::UNKNOWN;
			$this->authBy = self::UNKNOWN;
			$this->requestFrom = self::REQUESTSOURCE;
		}
		$this->setUserCookie($extractInfo['Response']);
	}

	private function setUserCookie($userObj){
		if( isset($_COOKIE[$this->CookieTag.'_user_status']) && $_COOKIE[$this->CookieTag.'_user_status'] != $userObj['user_status'] ){
			$this->config->setcookie($this->CookieTag.'_user_status', $userObj['user_status']);
		}else{
			if(!(isset($_COOKIE[$this->CookieTag.'_user_id']) && isset($_COOKIE[$this->CookieTag.'_user_status'])
				&& ($_COOKIE[$this->CookieTag.'_user_id'] != 0 || $_COOKIE[$this->CookieTag.'_user_status'] != self::UNKNOWN
					|| $_COOKIE[$this->CookieTag.'_user_status'] != self::UNSUBSCRIBED || $_COOKIE[$this->CookieTag.'_user_status'] != self::UNSUBPENDING)) ){
				foreach($userObj as $key => $value){
					if($key == 'user_id'){
						$this->config->setPersistentCookie($this->CookieTag."_".$key, $value);
					}else{
						$this->config->setcookie($this->CookieTag."_".$key, $value);
					}
				}
			}
		}
	}
	private function setRefferer(){
		$this->refferer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : self::NONE;
	}

	private function getMsisdnDetails(){
		$extractInfo = array();
		$this->url =  AUTH_SERVICE.'/?AppId='.$this->STOREID.'&MSISDN='.$this->msisdn.'&NET_IP_ADDRESS='.$this->clientIp.'&IMSI='.$this->imsi;

		$content = $this->curlMethods->executeCurl($this->url);

		$temp = explode(',', $content['Content']);
		for($i=0;$i<count($temp);$i++){
			$t2 = explode('=',$temp[$i]);
			$extractInfo[$t2[0]] = $t2[1];
		}
		//echo "<pre>";	print_r($extractInfo);
		return array(
			'Response' => $extractInfo,
			'Content' => $content['Content']
		);
	}

	public function isAllowed(){
		if ( $this->detectDevice->isMobile() ) {
			$this->isAllowed = 'true';
		}elseif ( $this->detectDevice->isTablet() ) {
			$this->isAllowed = 'true';
		}else{
			$this->isAllowed = 'true';
		}
	}

	public function detectMobile(){
		$this->detectDevice  = new \Detection\Mobile_Detect;
	}

	public function setCurrentURL(){
		$this->currentURL = $_SERVER['REQUEST_URI'];
	}
	public function setCurrentPage(){
		$this->currentPageName = strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME)));
	}
	public function getCurrentPage(){
		return $this->currentPageName;
	}
	public function getUserStatus(){
		return $this->userStatus;
	}
	public function getSessionId(){
		return $this->sessionId;
	}

	public function getOperator(){
		return $this->operator;
	}

	public function getToken(){
		return $this->tokenId;
	}

	public function getMsisdn(){
		return $this->msisdn;
	}

	public function getUserId(){
		return $this->userId;
	}
	public function getClientIp(){
		return $this->clientIp;
	}

	public function getTransId(){
		$micro_date   = microtime();
		$date_array   = explode(" ", $micro_date);
		$milliseconds = substr($date_array[0], 2, 3);
		$appid   = 123;
		$date    = date('YmdHis');
		if($this->msisdn == 'UNKNOWN' or $this->msisdn == ''){
			$this->transid = '1111111111' . $appid . $date . $milliseconds;
		}else{
			$msisdn_length = strlen((string)$this->msisdn);

			if($msisdn_length == 12) {
				$mnumber = substr($this->msisdn,2);
			}
			$this->transid = $mnumber . $appid . $date . $milliseconds;
		}
		return $this->transid;
	}

	public function getCampaignDetails(){
		return $this->campaignDetails;
	}
	public function getLandingUrl(){
		return $this->campaignDetails->getLandingUrl();
	}

	public function setUserAgent(){
		$this->userAgent = $_SERVER['HTTP_USER_AGENT'];
	}

	public function setMsidsn(){

		if (isset($_SERVER['X-MSISDN'])){
			$this->msisdn =  $_SERVER['X-MSISDN'];
		}elseif (isset($_SERVER['X_MSISDN'])){
			$this->msisdn =  $_SERVER['X_MSISDN'];
		}elseif (isset($_SERVER['HTTP_X_MSISDN'])){
			$this->msisdn =  $_SERVER['HTTP_X_MSISDN'];
		}elseif (isset($_SERVER['X-UP-CALLING-LINE-ID'])){
			$this->msisdn =  $_SERVER['X-UP-CALLING-LINE-ID'];
		}elseif (isset($_SERVER['X_UP_CALLING_LINE_ID'])){
			$this->msisdn = $_SERVER['X_UP_CALLING_LINE_ID'];
		}elseif (isset($_SERVER['HTTP_X_UP_CALLING_LINE_ID'])){
			$this->msisdn = $_SERVER['HTTP_X_UP_CALLING_LINE_ID'];
		}elseif (isset($_SERVER['X_WAP_NETWORK_CLIENT_MSISDN'])){
			$this->msisdn =  $_SERVER['X_WAP_NETWORK_CLIENT_MSISDN'];
		}elseif (isset($_SERVER['HTTP_MSISDN'])){
			$this->msisdn = $_SERVER['HTTP_MSISDN'];
		}elseif (isset($_SERVER['HTTP-X-MSISDN'])){
			$this->msisdn =  $_SERVER['HTTP-X-MSISDN'];
		}elseif (isset($_SERVER['MSISDN'])){
			$this->msisdn =  $_SERVER['MSISDN'];
		}elseif (isset($_SERVER['HTTP_X_NOKIA_MSISDN'])){
			$this->msisdn =  $_SERVER['HTTP_X_NOKIA_MSISDN'];
		}else{
			$this->msisdn = self::UNKNOWN;
		}
 	}

	public function setClientIpAddress(){
		if (isset($_SERVER['HTTP_NET_IP_ADDRESS'])){
			$this->clientIp = $_SERVER['HTTP_NET_IP_ADDRESS'];
		}elseif (isset($_SERVER['HTTP_X_CLIENT'])){
			$this->clientIp = $_SERVER['HTTP_X_CLIENT'];
		}elseif (isset($_SERVER['NET_IP_ADDRESS'])){
			$this->clientIp = $_SERVER['NET_IP_ADDRESS'];
		}elseif (isset($_SERVER['HTTP_IP'])){
			$this->clientIp = $_SERVER['HTTP_IP'];
		}elseif (isset($_SERVER['HTTP_CLIENT_IP'])){
			$this->clientIp = $_SERVER['HTTP_CLIENT_IP'];
		}else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$this->clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}else if (isset($_SERVER['HTTP_X_FORWARDED'])){
			$this->clientIp = $_SERVER['HTTP_X_FORWARDED'];
		}else if (isset($_SERVER['HTTP_FORWARDED_FOR'])){
			$this->clientIp = $_SERVER['HTTP_FORWARDED_FOR'];
		}else if (isset($_SERVER['HTTP_FORWARDED'])){
			$this->clientIp = $_SERVER['HTTP_FORWARDED'];
		}elseif (isset($_SERVER['REMOTE_ADDR'])){
			$this->clientIp = $_SERVER['REMOTE_ADDR'];
		}else{
			$this->clientIp = self::UNKNOWN;
		}
	}

	private function setIMSI(){

		if (isset($_SERVER['HTTP_X_NOKIA_IMSI'])){
			$this->imsi = $_SERVER['HTTP_X_NOKIA_IMSI'];
		}elseif (isset($_SERVER['HTTP-X-NOKIA-IMSI'])){
			$this->imsi = $_SERVER['HTTP-X-NOKIA-IMSI'];
		}elseif (isset($_SERVER['HTTP_X_IMSI'])){
			$this->imsi = $_SERVER['HTTP_X_IMSI'];
		}elseif (isset($_SERVER['HTTP-X-IMSI'])){
			$this->imsi = $_SERVER['HTTP-X-IMSI'];
		}elseif (isset($_SERVER['HTTP_IMSI'])){
			$this->imsi = $_SERVER['HTTP_IMSI'];
		}elseif (isset($_SERVER['HTTP-IMSI'])){
			$this->imsi = $_SERVER['HTTP-IMSI'];
		}else{
			$this->imsi = 0;
		}
	}

	public function setMSISDNCookie(){
		if( $this->msisdn != '' or $this->msisdn != self::UNKNOWN ){
			$this->config->setCookie( $this->CookieTag.'_MSISDN',$this->msisdn);
		}
	}

	public function setUniqueSessionId(){
		$micro_date   	= microtime();
		$date_array   	= explode(" ", $micro_date);
		$milliseconds 	= substr($date_array[0], 2, 3);
		$date			= date('YmdHis');

 		if( isset($_COOKIE['Unq_Sid']) and $_COOKIE['Unq_Sid'] != '' and $_COOKIE['Unq_Sid'] != null) {
			$unq_sid = $_COOKIE['Unq_Sid'];

			if($this->msisdn !=  self::UNKNOWN){
				if( substr($unq_sid, 0, 12) == '911111111111' ){
					$unq_sid = $this->msisdn.$date.$milliseconds;
				}elseif(substr($unq_sid, 0, 12) != $this->msisdn ){
					$unq_sid = $this->msisdn.$date.$milliseconds;
				}
				$this->sessionId = $unq_sid;
			}else{
				$this->sessionId = $unq_sid;
			}
		}else{
			if($this->msisdn ==  self::UNKNOWN){
				$this->msisdn = '911111111111';
			}
			$unq_sid = $this->msisdn.$date.$milliseconds;
			$this->sessionId = $unq_sid;
		}
		$this->config->setcookie('Unq_Sid', $this->sessionId);
	}

	public function getOperatorSubscribeParam($opr){

		if( in_array($opr, $this->config->allowedOperators) ){
			$cpevent = $this->price_point;

			if($this->mobileInfo['Resolution_Width'] <= 240){
				$image_url = $this->hostName.'/cgImage/babes9/118965_176x264.jpg';
			}elseif($this->mobileInfo['Resolution_Width'] <= 320){
				$image_url = $this->hostName.'/cgImage/babes9/118965_240x360.jpg';
			}elseif($this->mobileInfo['Resolution_Width'] <= 480){
				$image_url = $this->hostName.'/cgImage/babes9/118965_320x480.jpg';
			}else{
				$image_url = $this->hostName.'/cgImage/babes9/118965_480x720.jpg';
			}

			return array(
				'CMODE' => $this->config->operatorData[$opr]['Cmode'],
				'CPEVENT' => $cpevent,
				'IMAGE' => $image_url
			);
		}else{
			$cpevent = $this->price_point;
			return array(
				'CMODE' => '',
				'CPEVENT' => $cpevent,
				'IMAGE' => ''
			);
		}
	}

}