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

// if($jwt){
    
// } else {
//     http_response_code(401);

//     echo json_encode(array (
//         "message" => "Access denied",
//         "success" => false
//     ));
// }

try {
    
    $prefix_id = $artist->create_artist(
        $_POST['artistName'], 
        $_POST['aboutArtist']
    );

    if($prefix_id != null) {
        $artistImageFillename  =  $_FILES['artistImage']['name'];
        $artistImageTempPath  =  $_FILES['artistImage']['tmp_name'];
        $artistImagefileSize  =  $_FILES['artistImage']['size'];

        $fileExt1 = strtolower(pathinfo($artistImageFillename,PATHINFO_EXTENSION));

        if($artistImagefileSize < 5000000){
            $newArtistImageFillename = $prefix_id . '_artist_image' .'.'. $fileExt1;
            move_uploaded_file($artistImageTempPath, $artistImageUploadPath . $newArtistImageFillename); // move file from system temporary path to our upload folder path 
        } else {
            $errorMSG = json_encode(array("message" => "Artist image size exceeds 5MB", "success" => false));	
            echo $errorMSG;
        }

        $artistCoverImageFillename  =  $_FILES['artistCoverImage']['name'];
        $artistCoverImageTempPath  =  $_FILES['artistCoverImage']['tmp_name'];
        $artistCoverImagefileSize  =  $_FILES['artistCoverImage']['size'];


        $fileExt2 = strtolower(pathinfo($artistCoverImageFillename,PATHINFO_EXTENSION));

        if($artistCoverImagefileSize < 5000000){
            $newArtistCoverImageFillename = $prefix_id . '_artist_cover_image' .'.'. $fileExt2;
            move_uploaded_file($artistCoverImageTempPath, $artistCoverImageUploadPath . $newArtistCoverImageFillename); // move file from system temporary path to our upload folder path 
        } else {
            $errorMSG = json_encode(array("message" => "Artist cover image size exceeds 5MB", "success" => false));	
            echo $errorMSG;
        }

        $artist->update_artist_images(
            $prefix_id,
            $prefix_id . '_artist_image' .'.'. $fileExt1,
            $prefix_id . '_artist_cover_image' .'.'. $fileExt2,
        );

        http_response_code(200);

        echo json_encode(array (
            "message" => _("Artist created successfully"),
            "success" => true
        ));
    } else {
        http_response_code(200);

        echo json_encode(array (
            "message" => _("Failed to create a artist"),
            "error" => $e->getMessage(),
            "success" => false
        ));
    }
} 
catch (Exception $e) {
    http_response_code(200);

    $LOG->error(''.$e->getMessage());

    echo json_encode(array (
        "message" => _("Failed to create a artist"),
        "error" => $e->getMessage(),
        "success" => false
    ));
}