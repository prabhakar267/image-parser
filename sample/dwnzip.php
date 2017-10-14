<?php
// Load your HTML result into $response prior to here.
// Additionally, ensure that you have the root url for the
//     page loaded into $base_url.
$response=file_get_contents("")//url
$document = new DOMDocument();
$document->loadHTML($response);

$images = array();

// For all found img tags
foreach($document->getElementsByTagName('img') as $img) {
    // Extract what we want
    $image = array(
        // Here we take the img tag, get the src attribute
        //     we then run it through a function to ensure that it is not a
        //     relative url.
        // The make_absolute() function will not be covered in this snippet.
        'src' => make_absolute($img->getAttribute('src'), $base_url),
    );

    // Skip images without src
    if( ! $image['src'])
        continue;

    // Add to collection. Use src as key to prevent duplicates.
    $images[$image['src']] = $image;
}



foreach($images as $image) {
echo '<img src="'.$image.'" /><br />';

///Up to above will show you all images.

function downloadzip(){
$files = $image;

$tmpFile = tempnam('/tmp', '');

$zip = new ZipArchive;
$zip->open($tmpFile, ZipArchive::CREATE);
foreach ($files as $file) {
    // download file
    $fileContent = file_get_contents($file);

    $zip->addFromString(basename($file), $fileContent);
}
$zip->close();

header('Content-Type: application/zip');
header('Content-disposition: attachment; filename=file.zip');
header('Content-Length: ' . filesize($tmpFile));
readfile($tmpFile);

unlink($tmpFile);
}


if(isset($_POST['dnzip'])) {
	downloadzip();
}
	
?>
<html>
<body><button name="dnzip">Download All images in zip</button></body>
</html>

<?php
}
?>