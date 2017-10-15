<?php

/**
 * error reporting disabled, if you want to enable it
 * change it to "error_reporting(E_ALL)"
 * ref : http://php.net/manual/en/function.error-reporting.php
 */
error_reporting(0);

$final_response = get_extracted_images();

echo json_encode($final_response);


/**
 * function to extract images from URL in GET Parameter
 * @return response object
 */
function get_extracted_images() {
    $final_response = array();
    $images = array();

    if (isset($_GET['url'])) {

        $url = $_GET['url'];

        $parts = explode('/', trim($url));

        /**
         * this flag is to check whether user has entered the http or https in the beginning of URL or not
         * @var boolean
         */
        $flag = ($parts[0] == 'http:' || $parts[0] == 'https:') ? true : false;

        if (!$flag)
            $url = 'http://' . $url;

        /**
         * check whether URL entered by user is correct or not
         */
        if (!isValidURL($url)) {

            return array(
                'url_searched' => $url,
                'valid_url' => false,
                'success' => false
            );

        } else {

            $final_response['valid_url'] = true;

            /**
             * check if there is a trailing slash (/) or not, if there is one, remove it
             */
            if (substr($url, strlen($url) - 1) == '/')
                $url = rtrim($url, "/");

            $parts = explode('/', $url);

            /**
             * parent domain name called, if there is a subdomain, it would also be included here
             * @var string
             */
            $Root = $parts[0] . '//' . $parts[2];

            $html = curl_URL_call($url);
            if (empty($html)) {

                return array(
                    'url_searched' => $url,
                    'valid_url' => false,
                    'success' => false,
                    'message' => 'We are unable to access the given URL: ' . $url
                );
            }

            $dom = new DOMDocument;
            $dom->loadHTML($html);

            $final_response['url_searched'] = $url;
            $final_response['parent_url'] = $Root;

            /**
             * check if there is any image in HTML source code or not
             */
            if (preg_match_all('/<img[^>]+>/i', $html, $result)) {
                $final_response['success'] = true;

                foreach ($result[0] as $key) {
                    preg_match('/src="([^"]+)/i', $key, $src_key);

                    for ($i = 0; $i < count($src_key); $i += 2) {
                        $src = $src_key[1];

                        if (!preg_match("/http:/", $src) && !preg_match("/https:/", $src)) {
                            /**
                             * check whether the URL in the src is absolute or relative
                             * if it is relative, make it absolute
                             */
                            if ($src[0] == '/' && $src[1] == '/') {
                                $src = 'http:' . $src;
                            } else if ($src[0] == '/') {
                                $src = $Root . $src;
                            } else {
                                $src = $Root . '/' . $src;
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
                    if ($css_route[0] == '/' && $css_route[1] == '/') {
                        $css_route = 'http:' . $css_route;
                    } else if ($css_route[0] == '/') {
                        $css_route = $Root . $css_route;
                    } else if ($css_route[0] != 'h') {
                        $css_route = $Root . '/' . $css_route;
                    }
                    $parts = explode('/', $css_route);
                    $parts_length = sizeof($parts);
                    $css_root = $parts[0] . '//' . $parts[2];
                    $css_active_dir = $css_root;
                    $css_parent_dir = $css_root;
                    for ($i = 3; $i < $parts_length - 1; ++$i) {
                        if ($i < $parts_length - 2) {
                            $css_active_dir = $css_active_dir . '/' . $parts[$i];
                            $css_parent_dir = $css_parent_dir . '/' . $parts[$i];
                        } else {
                            $css_active_dir = $css_active_dir . '/' . $parts[$i];
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
                        if ($image_link[0] == '.' && $image_link[1] == '.') {
                            $image_link = $css_parent_dir . substr($image_link, 2);
                        } else if ($image_link[0] == '.') {
                            $image_link = $css_active_dir . substr($image_link, 1);
                        } else if ($image_link[0] == '/') {
                            $image_link = $css_active_dir . $image_link;
                        } else {
                            $image_link = $css_active_dir . '/' . $image_link;
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
        return $final_response;

    } else {
        $message = "Please enter a URL to extract information as a 'url' parameter in GET request";
        return array(
            'url_searched' => null,
            'valid_url' => false,
            'success' => false,
            'message' => $message,
        );
    }
}


/**
 * function to check if the URL entered by the user is correct or not
 * @param  string  $url URL to be passed which is to be checked
 * @return boolean      returns if URL passed is valid or not
 */
function isValidURL($url){
    return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}


/**
 * function to make a CURL call in order to fetch the complete HTML source code of URL entered
 * @param  string $url URL of the page
 * @return string      HTML source code of the URL entered
 */
function curl_URL_call($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}
