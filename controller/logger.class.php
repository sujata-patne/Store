<?php
/**
 * Created by PhpStorm.
 * User: sujata.patne
 * Date: 17-12-2015
 * Time: 10:34
 */

namespace Store\Logger;


class Logger {
    public function __construct($userData) {
        $this->logDate = $this->udate('Y-m-d H:i:s.u');

        if (isset($userData['imsi'])) {
           $this->imsi = $userData['imsi'];
        }
        if (isset($userData['msisdn'])) {
            $this->msisdn = $userData['msisdn'];
        }
        if (isset($userData['operator'])) {
            $this->operator = $userData['operator'];
        }
        if (isset($userData['make'])) {
            $this->make = $userData['make'];
        }
        if (isset($userData['model'])) {
            $this->model = $userData['model'];
        }
        if (isset($userData['browser'])) {
            $this->browser = $userData['deviceDetails']['browser'];
        }
        if (isset($userData['agentId'])) {
            $this->agentId = $userData['agentId'];
        }
        if (isset($userData['url'])) {
            $this->url = $userData['url'];
        }
        if (isset($userData['clientIp'])) {
            $this->clientIp = $userData['clientIp'];
        }
        if (isset($userData['operator'])) {
            $this->operator = $userData['operator'];
        }
        if (isset($userData['authBy'])) {
            $this->authBy = $userData['authBy'];
        }
        if (isset($userData['userStatus'])) {
            $this->userStatus = $userData['userStatus'];
        }
        if (isset($userData['response'])) {
            $this->response = $userData['response'];
        }
        if (isset($userData['currentURL'])) {
            $this->currentURL = $userData['currentURL'];
        }
        if (isset($userData['refferer'])) {
            $this->refferer = $userData['refferer'];
        }
        if (isset($userData['deviceId'])) {
            $this->deviceId = $userData['deviceId'];
        }
        if (isset($userData['deviceWidth'])) {
            $this->deviceWidth = $userData['deviceWidth'];
        }
        if (isset($userData['deviceHeight'])) {
            $this->deviceHeight = $userData['deviceHeight'];
        }
        if (isset($userData['userAgent'])) {
            $this->userAgent = $userData['userAgent'];
        }
        if (isset($userData['requestFrom'])) {
            $this->requestFrom = $userData['requestFrom'];
        }
        if (isset($userData['storeId'])) {
            $this->storeId = $userData['storeId'];
        }
        if (isset($userData['uid'])) {
            $this->uid = $userData['uid'];
        }
        if (isset($userData['sessionId'])) {
            $this->sessionId = $userData['sessionId'];
        }
        if (isset($userData['campaignDetails'])) {
            $this->campaignDetails = $userData['campaignDetails'];
        }
        if (isset($userData['promoBannerId'])) {
            $this->promoBannerId = $userData['promoBannerId'];
        }
        if (isset($userData['transactionId'])) {
            $this->transactionId = $userData['transactionId'];
        }
        if (isset($userData['token'])) {
            $this->token = $userData['token'];
        }
        if (isset($userData['cgImage'])) {
            $this->cgImage = $userData['cgImage'];
        }
        if (isset($userData['cmode'])) {
            $this->cmode = $userData['cmode'];
        }
        if (isset($userData['cpevent'])) {
            $this->cpevent = $userData['cpevent'];
        }
        if (isset($userData['errorUrl'])) {
            $this->errorUrl = $userData['errorUrl'];
        }
        if (isset($userData['retUrl'])) {
            $this->retUrl = $userData['retUrl'];
        }
        if (isset($userData['bannerId'])) {
            $this->bannerId = $userData['bannerId'];
        }
    }

    public function logIMSICircle($Imsicontent){
        $logFile = LOGS.'IMSI_LOG'.date('Y-m-d').'.log';
        $fh = fopen($logFile, 'a') or die("can't open file");
        fwrite($fh, "\n");
        fwrite($fh, date('Y-m-d H:i:s').', ');
        fwrite($fh, $this->msisdn.', ');
        fwrite($fh, $this->imsi.', ');
        fwrite($fh, $this->operator.', ');
        fwrite($fh, $this->make.', ');
        fwrite($fh, $this->model.', ');
        fwrite($fh, $this->browser.', ');
        fwrite($fh, $this->agentId.', ');
        fwrite($fh, $Imsicontent['Content'].', ');
        fwrite($fh, "\n");
        fclose($fh);
    }

    //public function logVisitors( $api, $msisdn, $clientIp, $operator, $authBy, $userStatus, $response, $make, $model, $currentPage, $referrerPage, $dId, $deviceWidth, $deviceHeight, $requestFrom, $Store, $uid, $promobannerId){
    public function logVisitors(){
        $logFile = LOGS.'VisitorLog_'.date('Y-m-d-H').'.log';
        $fs = fopen($logFile, 'a') or die('Cannot open file');
        fwrite($fs, "\n");

        fwrite($fs, $this->logDate.',');
        fwrite($fs, trim($this->url).',');
        fwrite($fs, trim($this->msisdn).',');
        fwrite($fs, trim($this->clientIp).',');
        fwrite($fs, trim($this->operator).',');
        fwrite($fs, trim($this->authBy).',');
        fwrite($fs, trim($this->userStatus).',');
        fwrite($fs, rawurlencode(trim($this->response)).',');

        fwrite($fs, trim($this->make).',');
        fwrite($fs, trim($this->model).',');

        fwrite($fs, trim(str_replace(',', '', $this->currentURL)).',');
        fwrite($fs, trim(str_replace(',', '', $this->refferer)).',');

        fwrite($fs, trim($this->deviceId).',');
        fwrite($fs, trim($this->deviceWidth.'x'.$this->deviceHeight).',');
        fwrite($fs, $this->requestFrom.',');
        $this->userAgent = str_replace(',', ';', $this->userAgent);
        fwrite($fs, rawurlencode($this->userAgent).',');
        fwrite($fs, $this->storeId.',');
        fwrite($fs, $this->uid.',');

        fwrite($fs, $this->sessionId.','.$this->promoBannerId.',');

        //fwrite($fs, implode(',',$this->campaignDetails));
        fwrite($fs, $this->imsi.',');

        $this->video_id = 0;
        fwrite($fs, $this->video_id.',');
        fwrite($fs, $this->browser);
        fwrite($fs, "\n");
        fclose($fs);
    }

    private function logBGWBanner(){
        $logFile = LOGS.'BGWBanner_'.date('Y-m-d-H').'.log';
        $fp = fopen($logFile, 'a') or die('Cannot open file');
        fwrite($fp, "\n");
        fwrite($fp, $this->logDate.',');
        fwrite($fp, "\n");
        fwrite($fp, "Promo Id: ".$this->promoBannerId."\n");
        fwrite($fp, "Banner Id ".$this->bannerId."\n");
        fwrite($fp, "Session Id ".$this->sessionId."\n");
        fwrite($fp, "BGW Id: ".$this->transactionId."\n");
        fwrite($fp, "Response: ".$this->campaignDetails."\n");
        fclose($fp);
    }

    private function logSubscribePack(){
        $logFile = LOGS.'SubscribePacks_'.date('d-m-Y-H').'.log';

        $fs = fopen($logFile, 'a') or die("can't open file");

        fwrite($fs, "\r\n");
        fwrite($fs, 'DATE AND TIME:');
        fwrite($fs, $this->logDate);
        fwrite($fs, "\r\n");
        fwrite($fs, "\r\n");
        fwrite($fs, 'MSISDN:');
        fwrite($fs, $this->msisdn);
        fwrite($fs, "\r\n");
        fwrite($fs, 'Operator:');
        fwrite($fs, $this->operator);
        fwrite($fs, "\r\n");
        fwrite($fs, 'Client IP:');
        fwrite($fs, $this->clientIp);
        fwrite($fs, "\r\n");
        fwrite($fs, 'TransId:');
        fwrite($fs, $this->transactionId);
        fwrite($fs, "\r\n");
        fwrite($fs, 'Success Return url:');
        fwrite($fs, $this->retUrl);
        fwrite($fs, "\r\n");

        fwrite($fs, 'Fail Return url:');
        fwrite($fs, $this->errorUrl);
        fwrite($fs, "\r\n");

        fwrite($fs, 'CPEVENT:');
        fwrite($fs, $this->cpevent);
        fwrite($fs, "\r\n");

        fwrite($fs, 'CMODE:');
        fwrite($fs, $this->cmode);
        fwrite($fs, "\r\n");

        fwrite($fs, 'CG IMAGE:');
        fwrite($fs, $this->cgImage);
        fwrite($fs, "\r\n");

        fwrite($fs, 'Token:');
        fwrite($fs, $this->token);
        fwrite($fs, "\r\n");
        fwrite($fs, 'Subscription Url:');
        fwrite($fs, $this->url);
        fwrite($fs, "\r\n");
        fclose($fs);

    }

    public function logSubscription($extractParams){
        $logFile = LOGS.'Subscribe_'.date('d-m-Y-H').'.log';

        $fs = fopen($logFile, 'a') or die("can't open file");

        fwrite($fs, "\r\n");
        fwrite($fs, 'DATE AND TIME:');
        fwrite($fs, $this->logDate);
        fwrite($fs, "\r\n");
        foreach($extractParams as $key => $value){
            fwrite($fs, $key.':');
            fwrite($fs, $value);
            fwrite($fs, "\r\n");
        }
        fwrite($fs, "\r\n");
        fwrite($fs, 'MSISDN:');
        fwrite($fs, $this->msisdn);
        fwrite($fs, "\r\n");
        fwrite($fs, 'Operator:');
        fwrite($fs, $this->operator);
        fwrite($fs, "\r\n");
        fwrite($fs, 'Client IP:');
        fwrite($fs, $this->clientIp);
        fwrite($fs, "\r\n");
        fwrite($fs, 'TransId:');
        fwrite($fs, $this->transactionId);
        fwrite($fs, "\r\n");
        fwrite($fs, "Session Id ".$this->sessionId."\n");

        fwrite($fs, "\r\n");
        fwrite($fs, 'Success Return url:');
        fwrite($fs, $this->retUrl);
        fwrite($fs, "\r\n");

        fwrite($fs, 'Fail Return url:');
        fwrite($fs, $this->errorUrl);
        fwrite($fs, "\r\n");

        fwrite($fs, 'CPEVENT:');
        fwrite($fs, $this->cpevent);
        fwrite($fs, "\r\n");

        fwrite($fs, 'CMODE:');
        fwrite($fs, $this->cmode);
        fwrite($fs, "\r\n");

        fwrite($fs, 'CG IMAGE:');
        fwrite($fs, $this->cgImage);
        fwrite($fs, "\r\n");

        fwrite($fs, 'Token:');
        fwrite($fs, $this->token);
        fwrite($fs, "\r\n");
        fwrite($fs, 'Subscription Url:');
        fwrite($fs, $this->url);
        fwrite($fs, "\r\n");
        fclose($fs);

    }
    private function udate($format = 'u', $utimestamp = null) {
        if (is_null($utimestamp)){
            $utimestamp = microtime(true);
        }

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }
}