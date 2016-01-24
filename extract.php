<?php
/**
 * @Author: Prabhakar Gupta
 * @Date:   2016-01-24 14:40:16
 * @Last Modified by:   Prabhakar Gupta
 * @Last Modified time: 2016-01-24 14:41:38
 */

require 'inc/function.inc.php';

$final_response = array();
$images = array();

if(isset($_GET['url'])){
    $url = $_GET['url'];
    
    $parts = explode('/', trim($url));

    $flag = ($parts[0] == 'http:' || $parts[0] == 'https:') ? true : false;

    if($flag == false)
        $url = 'http://'.$url;

    if(!isValidURL($url)){
        $final_response = array(
            'url_searched'  => $url,
            'valid_url'     => false,
            'success'       => false
        );
    } else {
        $final_response['valid_url'] = true;

        if(substr($url, strlen($url)-1) == '/')
            $url = rtrim($url, "/");

        $parts = explode('/', $url);
        $Root = $parts[0].'//'.$parts[2];
        $html = curl_URL_call($url);
        
        $final_response['url_searched'] = $url;
        $final_response['parent_url'] = $Root;

        if(preg_match_all('/<img[^>]+>/i',$html, $result)){
            $final_response['success'] = true;

            foreach ($result[0] as $key) {
                preg_match('/src="([^"]+)/i',$key, $src_key);
                for($i = 0; $i<count($src_key); $i += 2){
                    $src = $src_key[1];
                    if(!preg_match("/http:/", $src) && !preg_match("/https:/", $src)){
                        if($src[0] == '/' && $src[1] == '/')
                            $src = 'http:'.$src;
                        elseif($src[0]=='/')
                            $src = $Root.$src;
                        else
                            $src = $Root.'/'.$src;
                    }
                    array_push($images, $src);
                }
            }
        } else {
            $final_response['success'] = false;
        }
    }
 
    $final_response['images'] = $images;

} else {
    $message = "Please enter a URL to extract information as a 'url' parameter in GET request";
    $final_response = array(
        'url_searched'  => null,
        'valid_url'     => false,
        'success'       => false,
        'message'       => $message,
    );
}

echo json_encode($final_response);