<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


include_once '../config/database.php';
include_once '../controllers/artist.php';
include_once '../include/common.inc.php';

$database = new Database($LOG);
$db = $database->getConnection();

$artist = new Artists($db);


$artistImageUploadPath = '../uploads/artistImages/';
$artistCoverImageUploadPath = '../uploads/artistCoverImages/';

$timestamp = date('Y-m-d h:i:s');

use \Firebase\JWT\JWT;

$jwt=isset($_POST["authToken"]) ? $_POST["authToken"] : "";

$artist_id = $_POST["artistID"];

$oldArtistImageName = $artist->get_artist_image_name($artist_id);
$oldArtistCoverImageName = $artist->get_artist_cover_image_name($artist_id);

// if($jwt){
    
// } else {
//     http_response_code(401);

//     echo json_encode(array (
//         "message" => "Access denied",
//         "success" => false
//     ));
// }

try {

    $_artistImageName = $oldArtistImageName;

    if(isset($_FILES['artistImage'])) {
        $artistImageFillename  =  $_FILES['artistImage']['name'];
        $artistImageTempPath  =  $_FILES['artistImage']['tmp_name'];
        $artistImagefileSize  =  $_FILES['artistImage']['size'];

        $fileExt1 = strtolower(pathinfo($artistImageFillename,PATHINFO_EXTENSION));

        if($artistImagefileSize < 5000000){
            unlink($artistImageUploadPath.$_artistImageName);
            move_uploaded_file($artistImageTempPath, $artistImageUploadPath . $_artistImageName); // move file from system temporary path to our upload folder path 
        } else {
            $errorMSG = json_encode(array("message" => "Artist image size exceeds 5MB", "success" => false));	
            echo $errorMSG;
        }
    }

    $_artistCoverImageName = $oldArtistCoverImageName;

    if(isset($_FILES['artistCoverImage'])) {
        $artistCoverImageFillename  =  $_FILES['artistCoverImage']['name'];
        $artistCoverImageTempPath  =  $_FILES['artistCoverImage']['tmp_name'];
        $artistCoverImagefileSize  =  $_FILES['artistCoverImage']['size'];

        $fileExt2 = strtolower(pathinfo($artistCoverImageFillename,PATHINFO_EXTENSION));

        if($artistCoverImagefileSize < 5000000){
            unlink($artistCoverImageUploadPath.$_artistCoverImageName);
            move_uploaded_file($artistCoverImageTempPath, $artistCoverImageUploadPath . $_artistCoverImageName); // move file from system temporary path to our upload folder path 
        } else {
            $errorMSG = json_encode(array("message" => "Artist image size exceeds 5MB", "success" => false));	
            echo $errorMSG;
        }
    }

    $artist_updated = $artist->update_artist(
        $artist_id,
        $_POST['artistName'], 
        $_artistImageName,
        $_artistCoverImageName,
        $_POST['aboutArtist']
    );

    if($artist_updated) {
        http_response_code(200);

        echo json_encode(array (
            "message" => _("Artist updated successfully"),
            "success" => true
        ));
    } else {
        http_response_code(200);

        echo json_encode(array (
            "message" => _("Failed to update the artist"),
            "success" => false
        ));
    }
} 
catch (Exception $e) {
    http_response_code(200);

    echo json_encode(array (
        "message" => _("Failed to update the artist"),
        "error" => $e->getMessage(),
        "success" => false
    ));
}