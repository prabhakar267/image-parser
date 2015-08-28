<?php require 'inc/header.inc.php';?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Prabhakar Gupta">
	<link rel="shortcut icon" href="../../img/icon.png">
    <title>Image Extractor</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
    .header-image{
        background-color: rgb(239, 250, 141);
        background-image: url('bg.png');
        padding: 20px;
        text-align: center;
        font-family: Futura, Century Gothic, AppleGothic, sans-serif;
    }
	.other-text{
		font-family: Futura, Century Gothic, AppleGothic, sans-serif;
		font-size : 16px;
		margin: 10px;
		text-align: center;
	}
    </style>
</head>

<body style="background-color:rgb(249, 249, 249);">
    <header class="header-image">
        <div class="container">
            <h1><strong>Image Extractor</strong></h1>
            <h2><strong>Enter any URL and get all the images on the page</strong></h2>
        </div>
    </header>
    <hr>
    <form class="form-inline text-center" action="<?php echo $script_name;?>" method="POST">
        
          <label class="control-label" for="inputSuccess1">URL : </label>
          <input type="text" class="form-control" name="url" required>
          <button type="submit" class="btn btn-success">Extract</button>
    </form>
<?php
    function isValidURL($url){
        return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
    }

    if(isset($_POST['url'])){
        $url = $_POST['url'];
        

        $parts = explode('/', $url);
        $flag = ($parts[0]=='http:' || $parts[0]=='https:')?1:0;

        if(!@$flag)
            $url = 'http://'.$url;

        if(!isValidURL($url)){
            echo '<div class="other-text">Invalid URL!</div>';
			die();
		}

        if(substr($url, strlen($url)-1) == '/')
            $url = rtrim($url,"/");

        $filename = 'websites.txt';
        $handle = fopen($filename, 'a');
        fwrite($handle, $url."\n");
        fclose($handle);
            
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