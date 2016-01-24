<?php
/**
 * @Author: Prabhakar Gupta
 * @Date:   2016-01-24 14:40:16
 * @Last Modified by:   Prabhakar Gupta
 * @Last Modified time: 2016-01-24 15:00:35
 */

require 'inc/function.inc.php';

$final_response = array();
$images = array();

if(isset($_GET['url'])){
    $url = $_GET['url'];
    
    $parts = explode('/', trim($url));

    /**
     * this flag is to check whether user has entered the http or https in the beginning of URL or not
     * @var boolean
     */
    $flag = ($parts[0] == 'http:' || $parts[0] == 'https:') ? true : false;

    if(!$flag)
        $url = 'http://'.$url;

    /**
     * check whether URL entered by user is correct or not
     */
    if(!isValidURL($url)){
        $final_response = array(
            'url_searched'  => $url,
            'valid_url'     => false,
            'success'       => false
        );
    } else {
        $final_response['valid_url'] = true;

        /**
         * check if there is a trailing slash (/) or not, if there is one, remove it
         */
        if(substr($url, strlen($url)-1) == '/')
            $url = rtrim($url, "/");

        $parts = explode('/', $url);

        /**
         * parent domain name called, if there is a subdomain, it would also be included here
         * @var string
         */
        $Root = $parts[0].'//'.$parts[2];
        
        $html = curl_URL_call($url);
        
        $final_response['url_searched'] = $url;
        $final_response['parent_url'] = $Root;

        /**
         * check if there is any image in HTML source code or not
         */
        if(preg_match_all('/<img[^>]+>/i',$html, $result)){
            $final_response['success'] = true;

            foreach ($result[0] as $key) {
                preg_match('/src="([^"]+)/i',$key, $src_key);

                for($i=0; $i<count($src_key); $i+=2){
                    $src = $src_key[1];

                    if(!preg_match("/http:/", $src) && !preg_match("/https:/", $src)){
                    	/**
                    	 * check whether the URL in the src is absolute or relative
                    	 * if it is relative, make it absolute
                    	 */
                        if($src[0] == '/' && $src[1] == '/'){
                            $src = 'http:'.$src;
                        } else if($src[0] == '/'){
                            $src = $Root.$src;
                        } else {
                            $src = $Root.'/'.$src;
                        }
                    }
                    array_push($images, $src);
                }
            }
        } else {
        	/**
        	 * No images were found in the HTML
        	 * source code, hence success if false
        	 */
            $final_response['success'] = false;
        }
    }
 	/**
 	 * All the images are added to the images array in 
 	 * final response
 	 */
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