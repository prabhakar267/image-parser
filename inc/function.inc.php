<?php
/**
 * @Author: Prabhakar Gupta
 * @Date:   2016-01-24 14:39:55
 * @Last Modified by:   Prabhakar Gupta
 * @Last Modified time: 2016-01-24 14:40:06
 */

function isValidURL($url){
    return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}

function curl_URL_call($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}