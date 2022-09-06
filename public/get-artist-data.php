<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../controllers/artist.php';
include_once '../include/common.inc.php';


$database = new Database($LOG);
$db = $database->getConnection();

$artist = new Artists($db);

$artist_data = $artist->get_artist_data();

if($artist_data == null) {
    $artist_data = [];
}

http_response_code(200);

echo json_encode(array (
    "message" => _("Artist data collected successfully"),
    "success" => true,
    "artist_data" => $artist_data
));