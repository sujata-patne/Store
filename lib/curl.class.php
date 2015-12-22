<?php
namespace Store\Curl;
class Curl {
    public function executeCurl($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec ($ch);
        $getCurlInfo = curl_getinfo($ch);
        $curlError = curl_error($ch);
        curl_close ($ch); // close curl handle

        return array(
            'Content' => $content,
            'Info' => $getCurlInfo,
            'Error' => $curlError
        );
    }

    public function executePostCurl($url, $data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $content = curl_exec ($ch);
        $getCurlInfo = curl_getinfo($ch);
        $curlError = curl_error($ch);
        curl_close ($ch); // close curl handle

        return array(
            'Content' => $content,
            'Info' => $getCurlInfo,
            'Error' => $curlError
        );
    }
    public function executePostCurlHeader($url,$header,$data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $content = curl_exec ($ch);
        $getCurlInfo = curl_getinfo($ch);
        $curlError = curl_error($ch);
        curl_close ($ch); // close curl handle

        return array(
            'Content' => $content,
            'Info' => $getCurlInfo,
            'Error' => $curlError
        );
    }
}