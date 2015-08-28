<?php
    require 'inc/header.inc.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Prabhakar Gupta">

    <title>Image Extractor</title>

    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <header class="header-image">
        <div class="container">
            <h1><strong>Image Extractor</strong></h1>
            <h3>Enter any URL and get all the images on the page</h3>
        </div>
    </header>
    <hr>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <form class="form-horizontal form-main" method="GET">
                      <!-- <label class="control-label" for="inputSuccess1">URL : </label> -->
                      <input type="text" class="form-control" name="url" placeholder="Enter the URL from where images are to be extracted" required>
                      <br>
                      <button type="submit" class="btn btn-lg btn-success">Extract</button>
                </form>
            </div>
        </div>
    </div>
<?php
function isValidURL($url){
    return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}

if(isset($_GET['url'])){
    $url = $_GET['url'];
    
    $parts = explode('/', $url);

    $flag = ($parts[0] == 'http:' || $parts[0] == 'https:') ? true : false;

    if($flag == false)
        $url = 'http://'.$url;

    if(!isValidURL($url)){
        echo '<div class="other-text">Invalid URL!</div>';
		die;
	}

    if(substr($url, strlen($url)-1) == '/')
        $url = rtrim($url, "/");

    $parts = explode('/', $url);
    $Root = $parts[0].'//'.$parts[2];
    $html = @file_get_contents($url);
    
    if(preg_match_all('/<img[^>]+>/i',$html, $result)){
        echo '<div class="other-text"><strong>URL Searched</strong> : <a href="'.$url.'" target="_blank">'.$url.'</a><br><strong>Parent Domain</strong> : <a href="'.$Root.'" target="_blank">'.$Root.'</a><br></div>';
        foreach ($result[0] as $key) {
            preg_match('/src="([^"]+)/i',$key, $src_key);
            for($i=0;$i<count($src_key);$i+=2){
                $src = $src_key[1];
                if(!preg_match("/http:/", $src) && !preg_match("/https:/", $src)){
                    if($src[0]=='/' && $src[1]=='/')
                        $src = 'http:'.$src;
                    elseif($src[0]=='/')
                        $src = $Root.$src;
                    else
                        $src = $Root.'/'.$src;
                }
                echo '<a href="'.$src.'"><img src="'.$src.'" width="250" style="margin:20px"></a>'."\n";
            }
        }
    } else {
		echo '<div class="other-text"><b>No Image Found at your Given Location</b></div>';
    }
} else {
    echo '<div class="other-text">Welcome :)</div>';
}
?>

</body>
</html>