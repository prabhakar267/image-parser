<?php

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
    
    
    $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (200==$retcode) {
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
        
    } else {
        curl_close($ch);
        return NULL;
    }
}
