<?php
/**
 * @Author: Prabhakar Gupta
 * @Date:   2016-01-24 14:40:16
 * @Last Modified by:   Prabhakar Gupta
 * @Last Modified time: 2016-04-24 15:25:14
 */
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once 'inc/function.inc.php';

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

        $dom = new DOMDocument;
        $dom->loadHTML($html);

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

      /**
       * Getting urls for stylesheets in the webpage
       */
        foreach ($dom->getElementsByTagName('link') as $node) {
            if ($node->getAttribute("rel") == "stylesheet") {
                $css_route = $node->getAttribute("href");
               /**
                * check whether the URL in the $css_route is absolute or relative
                * if it is relative, make it absolute
                */
                if($css_route[0] == '/' && $css_route[1] == '/'){
                    $css_route = 'http:'.$css_route;
                } else if($css_route[0] == '/'){
                    $css_route = $Root.$css_route;
                } else if($css_route[0] != 'h'){
                    $css_route = $Root.'/'.$css_route;
                }
                $parts = explode('/', $css_route);
                $parts_length = sizeof($parts);
                $css_root = $parts[0].'//'.$parts[2];
                $css_active_dir = $css_root;
                $css_parent_dir = $css_root;
                for ($i = 3; $i < $parts_length - 1; ++$i) {
                    if($i < $parts_length - 2) {
                        $css_active_dir = $css_active_dir.'/'.$parts[$i];
                        $css_parent_dir = $css_parent_dir.'/'.$parts[$i];
                    } else {
                        $css_active_dir = $css_active_dir.'/'.$parts[$i];
                    }
                }
                $css = curl_URL_call($css_route);
                $matches = array();
               /**
                * Getting image urls using image extension matches in stylesheet extracted
                */
                preg_match_all('/url\(\s*[\'"]?(\S*\.(?:jpe?g|gif|png))[\'"]?\s*\)[^;}]*?/i', $css, $matches);

                foreach ($matches[1] as $image_link) {
                   /**
                    * check whether the URL in the $image_link is absolute or relative
                    * if it is relative, make it absolute
                    */
                    if($image_link[0] == '.' && $image_link[1] == '.'){
                        $image_link = $css_parent_dir.substr($image_link, 2);
                    } else if($image_link[0] == '.'){
                        $image_link = $css_active_dir.substr($image_link, 1);
                    } else if($image_link[0] == '/'){
                        $image_link = $css_active_dir.$image_link;
                    } else {
                        $image_link = $css_active_dir.'/'.$image_link;
                    }
                    array_push($images, $image_link);
                }
            }
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
