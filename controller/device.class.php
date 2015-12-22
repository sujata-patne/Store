<?php
namespace Store\Device;
use Store\Config as Config;
use Store\Curl as Curl;
use Store\Logger as Logger;

use ScientiaMobile\WurflCloud as WurflCloud;

class Device {
    public $agentId;
    private $deviceDetails;
    private $wurflInfo;
    private $config ;
    public $curlMethods;

    public $lang;
    public $make;
    public $model;
    public $browser;
    public $deviceId;
    public $deviceWidth;
    public $deviceHeight;
    public $mobileInfo;
    private $streamingPreferredHTTPProtocol;
    private $wurflConfig;
    private $isFeature = 'true';
    private $wurflClient;
    public $userAgent;

    private $featurePhoneUA = 'Micromax X2814/Q03C MAUI-Browser Profile/MIDP-2.0 Configuration/CLDC-1.1';

    function __construct($userData = array()) {
        $this->config = new Config\Config();
        $this->curlMethods = new Curl\Curl();

        $this->CookieTag = Config\Config::CookieTag;
        $this->WURFL_API_KEY 	= Config\Config::WURFL_API_KEY;

        $this->config = new Config\Config();

        //echo "<pre>"; var_dump($userData); exit;
        if (isset($userData['userAgent'])) {
            $this->userAgent = $userData['userAgent'];
        }
        $this->agentId = gmp_strval($this->gmphexdec(md5($this->userAgent)));

        if (isset($userData['imsi'])) {
            $this->imsi = $userData['imsi'];
        }
        if (isset($userData['msisdn'])) {
            $this->msisdn = $userData['msisdn'];
        }
        if (isset($userData['operator'])) {
            $this->operator = $userData['operator'];
        }

        // Get Browser details
        $this->setBrowser();

        //Get Device inforamtion
        $this->getWurlInfoFromDB();
        if( is_array($this->deviceDetails) and !empty($this->deviceDetails) ){
            $this->setDeviceInfo();

        }else {
            if (SITE_MODE == 1) { //2
                // Create a WURFL Configuration object
                $this->wurflConfig = new WurflCloud\Config();
                // Set your WURFL Cloud API Key
                $this->wurflConfig->api_key = $this->WURFL_API_KEY;

                $this->getWurlInfoFromServer();
                $this->setWURFLInfo();
            }
        }
       // echo "<pre>"; print_r($userData); exit;
        $this->Logger = new Logger\Logger((array)$this);
    }

    private function getWurlInfoFromDB(){
        $deviceInfo = array(
            'agent_id' => $this->agentId
        );
        $deviceInfoResponse =  (object)$this->curlMethods->executePostCurl(READ_WURFL_DATA, $deviceInfo);

        $this->deviceDetails = json_decode($deviceInfoResponse->Content, true);
        // echo "deviceInfo <pre>"; print_r($deviceInfoResponse); exit;

    }
    private function updateWurlfInfoToDB($deviceInfo){
        $content = $this->curlMethods->executePostCurl(INSERT_WURFL_DATA,$deviceInfo);
       // echo "content <pre>"; print_r($content);
        return $content;
    }
    private function getWurlInfoFromServer(){
       // Create the WURFL Cloud Client
        $this->wurflClient = new WurflCloud\Client($this->wurflConfig, true);
        // Detect your device
        if($this->isFeature == 'true'){
            $uA['HTTP_USER_AGENT'] = $this->featurePhoneUA;
            $this->wurflClient->detectDevice($uA);
        }else{
            $this->wurflClient->detectDevice($_SERVER);
        }
        $this->wurflInfo =  $this->wurflClient->getAllCapabilities();
     //   echo "FROM WURFL <pre>"; print_r($this->wurflInfo);

    }

    private function setWURFLInfo(){
        $this->lang = "HTML";

        if( strtolower($this->wurflInfo['html_preferred_dtd']) == "html4" or strtolower($this->wurflInfo['html_preferred_dtd']) == "html5" ){
            $this->lang = "HTML";
        }else{
            $this->lang = "XHTML";
        }
        $this->make = $this->wurflInfo['brand_name'];
        $this->model = $this->wurflInfo['model_name'];
        $this->deviceId = $this->wurflInfo['id'];
        $this->streamingPreferredHTTPProtocol = $this->wurflInfo['streaming_preferred_protocol'];

        $this->deviceWidth = $this->wurflInfo['max_image_width'];
        $this->deviceHeight = $this->wurflInfo['max_image_height'];

        $this->mobileInfo = array(
            'Resolution_Width' => $this->wurflInfo['resolution_width'],
            'Resolution_Height' => $this->wurflInfo['resolution_height'],
            'Image_Width' => $this->wurflInfo['max_image_width'],
            'Image_Height' => $this->wurflInfo['max_image_height'],
            'Wallpaper_Width' => $this->wurflInfo['wallpaper_preferred_width'],
            'Wallpaper_Height' => $this->wurflInfo['wallpaper_preferred_height']
        );

        $this->deviceDetails = array(
            'agent_id' => gmp_strval($this->gmphexdec(md5($this->userAgent))),
            'user_agent' => rawurlencode($this->userAgent),
            'device_id' => $this->wurflInfo['id'],
            'html_preferred_dtd' => $this->lang,
            'screen_width' => $this->wurflInfo['resolution_width'],
            'wallpaper_preferred_width' => $this->wurflInfo['wallpaper_preferred_width'],
            'wallpaper_preferred_height' => $this->wurflInfo['wallpaper_preferred_height'],
            'max_image_width' => $this->wurflInfo['max_image_width'],
            'max_image_height' => $this->wurflInfo['max_image_height'],
            'mp3' => empty($this->wurflInfo['mp3']) ? 0 : $this->wurflInfo['mp3'],
            'device_brand' => $this->wurflInfo['brand_name'],
            'device_model' => $this->wurflInfo['model_name'],
            'streaming_video' => ($this->wurflInfo['streaming_video'] == true) ? 1 : 0,
            'streaming_3gpp' => empty($this->wurflInfo['streaming_3gpp']) ? 0 : $this->wurflInfo['streaming_3gpp'],
            'streaming_mp4' => empty($this->wurflInfo['streaming_mp4']) ? 0 : $this->wurflInfo['streaming_mp4'],
            'streaming_flv' => empty($this->wurflInfo['streaming_flv']) ? 0 : $this->wurflInfo['streaming_flv'],
            'streaming_video_size_limit' => $this->wurflInfo['streaming_video_size_limit'],
            'pref_protocal' => $this->wurflInfo['streaming_preferred_protocol'],
            'streaming_pref_http_protocal' => $this->wurflInfo['streaming_preferred_http_protocol'],
            'wallpaper_gif' => empty($this->wurflInfo['wallpaper_gif']) ? 0 : $this->wurflInfo['wallpaper_gif'],
            'wallpaper_jpg' => empty($this->wurflInfo['wallpaper_jpg']) ? 0 : $this->wurflInfo['wallpaper_jpg'],
            'wallpaper_png' => empty($this->wurflInfo['wallpaper_png']) ? 0 : $this->wurflInfo['wallpaper_png'],
            'device_res_height' => $this->wurflInfo['resolution_height'],
            'device_res_width' => $this->wurflInfo['resolution_width'],
            'playback_3gpp' => empty($this->wurflInfo['playback_3gpp']) ? 0 : $this->wurflInfo['playback_3gpp'],
            'playback_mp4' => empty($this->wurflInfo['playback_mp4']) ? 0 : $this->wurflInfo['playback_mp4'],
            'browser' => $this->browser
        );
        $this->config->setCookie($this->CookieTag.'_screen_width', $this->wurflInfo['max_image_width']);
        //$wurflUpdateResponse = $this->updateWurlfInfoToDB($this->deviceDetails);
        //echo "<pre>"; print_r($wurflUpdateResponse);
    }
    public function setBrowser(){
        $browserDetails = get_browser($this->userAgent, true);
        $this->browser = $browserDetails['browser'];
    }
    private function setDeviceInfo(){
        $DeviceInfoResponse = $this->deviceDetails[0];

        $this->lang = $DeviceInfoResponse['html_preferred_dtd'];
        $this->make = $DeviceInfoResponse['device_brand'];
        $this->model = $DeviceInfoResponse['device_model'];
        $this->deviceId = $DeviceInfoResponse['device_id'];
        $this->streamingPreferredHTTPProtocol = $DeviceInfoResponse['streaming_pref_http_protocal'];

        $this->deviceWidth = $DeviceInfoResponse['max_image_width'];
        $this->deviceHeight = $DeviceInfoResponse['max_image_height'];

        $this->mobileInfo = array(
            'Resolution_Width' => $DeviceInfoResponse['device_res_width'],
            'Resolution_Height' => $DeviceInfoResponse['device_res_height'],
            'Image_Width' => $DeviceInfoResponse['max_image_width'],
            'Image_Height' => $DeviceInfoResponse['max_image_height'],
            'Wallpaper_Width' => $DeviceInfoResponse['wallpaper_preferred_width'],
            'Wallpaper_Height' => $DeviceInfoResponse['wallpaper_preferred_height']
        );

        $this->config->setCookie( $this->CookieTag.'_screen_width', $DeviceInfoResponse['max_image_width']);

    }

    private function gmphexdec($n) {
        $gmp = gmp_init(0);
        $mult = gmp_init(1);
        for ($i=strlen($n)-1;$i>=0;$i--,$mult=gmp_mul($mult, 16)) {
            $gmp = gmp_add($gmp, gmp_mul($mult, hexdec($n[$i])));
        }
        return $gmp;
    }

    public function setIMSIContent(){
        if( !isset($_COOKIE[$this->CookieTag.'_IMSI']) ){

            $imsiData['IMSI'] = $this->imsi;
            $imsiData['MSISDN'] = $this->msisdn;
            $imsiData['operator'] = $this->operator;
            $imsiData['make'] = $this->make;
            $imsiData['model'] = $this->model;
            $imsiData['browser'] = $this->browser;
            $imsiData['agent_id'] = $this->agentId;

            $Imsicontent = $this->curlMethods->executePostCurl(IMSI_CIR_DATA, $imsiData);

            $this->Logger->logIMSICircle($Imsicontent);

            if( stripos($Imsicontent['Content'], 'IMSI-Circle record added') !== false or stripos($Imsicontent['Content'], 'IMSI-Circle updated') !== false ){
                setcookie($this->config['CookieTag'].'_IMSI', $this->imsi, strtotime('today 23:59'), '/');
            }
        }
    }
    public function getDeviceWidth(){
        return $this->deviceWidth;
    }

    public function getDeviceHeight(){
        return $this->deviceHeight;
    }


}