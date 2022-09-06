<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


include_once '../config/database.php';
include_once '../controllers/artist.php';
include_once '../include/common.inc.php';

$data = json_decode(file_get_contents("php://input"), true);

$database = new Database($LOG);
$db = $database->getConnection();

$artist = new Artists($db);

$artistImageUploadPath = '../uploads/artistImages/';
$artistCoverImageUploadPath = '../uploads/artistCoverImages/';

$artist_id = $data["id"];

$artistImageName = $artist->get_artist_image_name($artist_id);
$artistCoverImageName = $artist->get_artist_cover_image_name($artist_id);

$artistDeleted = $artist->delete_artist($artist_id);

if($artistDeleted) {

    unlink($artistImageUploadPath.''.$artistImageName);
    unlink($artistCoverImageUploadPath.''.$artistCoverImageName);

    http_response_code(200);

    // sending login success
    echo json_encode(array(
            "message" => _("Artist Deleted successfully"),
            "success" => true
        )
    );
} else {
    http_response_code(200);

    // sending login success
    echo json_encode(array(
            "message" => _("Artist deletion failed"),
            "success" => false
        )
    );
}
