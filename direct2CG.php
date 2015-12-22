<?php
include 'config.php';
use Store\Direct2CG as Direct2CG;

//get config parameters;
$t = $_GET['t'];
$n = $_GET['n'];
$d = $_GET['d'];
$m = $_GET['m'];
$i = isset($_GET['i']) ? $_GET['i'] : null;

$f = (isset($extractParams['f']))?$extractParams['f']:$currentPage;
$promo = (isset($extractParams['promo']))? $extractParams['promo']:$promo;
$price_point = (isset($extractParams['EventId']) and $extractParams['EventId'] != '' and $extractParams['EventId'] != null)? base64_decode($extractParams['EventId']): $OprSubParam['CPEVENT'];

if($userStatus == 'NEWUSER' or $userStatus == 'UNSUBSCRIBED' ){
	if( !in_array($operator, $config->allowedOperators) ){
		header("Location: error.php?responseId=999999&resDesc=Invalid Operator Info");
		exit();
	}else{
		$direct2cg = new Direct2CG\direct2cg($promo, $f);

		$image_url = $direct2cg->getCGimages();

		$retUrl = $direct2cg->getUrlFromParams();

		/*$fUrl = $campaignDetails->getNOKUrl();
		$retUrl = $campaignDetails->getLandingUrl();
		$price_point = $campaignDetails->getPromoPricePoint();
		$bannerId = $campaignDetails->getPromoBannerId();*/
        if( isset($t) and isset($n) and isset($d) and isset($m) ){
			$n1 = base64_decode($n);
			if($i == null){
				$retUrl .= '?t='.$t.'_n='.$n1.'_d='.$d.'_m='.$m;
			}else{
				$retUrl .= '?t='.$t.'_n='.$n1.'_d='.$d.'_m='.$m.'_i='.$i;
			}
		}

		//if(!empty($extractParams) and isset($extractParams['promo']) and $extractParams['promo'] != '' and $extractParams['promo'] != null and ctype_digit($extractParams['promo'])){
		if(!(!empty($promo) and isset($promo) and $promo != '' and $promo != null and ctype_digit($promo))){
			$checkPromoId = explode("_",$promo);
			//echo "<pre>"; print_r($checkPromoId);
			if($checkPromoId[0] === 'z'){
				$direct2cg->logBGWBanner($msisdn,$operator, $TransId,$campaignDetails,$fUrl,$retUrl,$price_point,$bannerId);
			}
		}else{
			if( empty($OprSubParam) ){
				header("Location: ".$fUrl);
				exit();
			}else{
				$logCmode = $OprSubParam['CMODE'];
			}
		}

		$subscribeData = array(
			'transactionId' => $TransId,
			'msisdn' => $msisdn,
			'clientIp' => $clientIp,
			'retUrl' => $retUrl,
			'extractParams' => $extractParams,
			'promoBannerId' => $promo,
		);

		// $direct2cg->logSubscription($subscribeData);

		// fwrite($fs, 'Success Return url:');
		// fwrite($fs, $retUrl);
		// fwrite($fs, "\r\n");

		// fwrite($fs, 'Fail Return-. url:');
		// fwrite($fs, $fUrl);
		// fwrite($fs, "\r\n");

		// fwrite($fs, 'CPEVENT:');
		// fwrite($fs, $price_point);
		// fwrite($fs, "\r\n");

		// fwrite($fs, 'CMODE:');
		// fwrite($fs, $logCmode);
		// fwrite($fs, "\r\n");

		// fwrite($fs, 'CP IMAGE:');
		// fwrite($fs, $image_url);
		// fwrite($fs, "\r\n");

		// fwrite($fs, 'Token:');
		// fwrite($fs, $Token);
		// fwrite($fs, "\r\n");


		// fwrite($fs, 'Sub Url:');
		// fwrite($fs, $billing_gateway);
		// fwrite($fs, "\r\n");
		// fclose($fs);

		setcookie('D2C_promo', "", time()-3600, '/');
		setcookie('D2C_tid', "", time()-3600, '/');

		unset($_COOKIE['D2C_promo']);
		unset($_COOKIE['D2C_tid']);

		header("Location: ".$billing_gateway);
		exit();
	}
}else{
	$checkPromoId = explode("_",$extractParams['promo']);
	/*
	if($checkPromoId[0] != 'z'){
		header("Location: http://wakau.in/Wakau/celebritySubscribe/");
		exit();
	}else{*/
	if( $USERSTATUS == 'UNKNOWN' ){
		header("Location: error.php?responseId=999999&resDesc=Invalid Operator Info");
		exit();
	}else{
		header("Location: index.php");
		exit();
	}
	//}
}


?>