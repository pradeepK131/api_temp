<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require '../config/database.php';
require '../controllers/tokens.php';

$data = json_decode(file_get_contents("php://input"), true);

// setting up translation and time_zone
include_once '../include/common.inc.php';

$database = new Database($LOG);
$db = $database->getConnection();

$token = new Tokens($db);

$token_id = $data["tokenID"];
$tier = $data["tier"];
$ownership_offered = $data["ownershipOffered"];
$price = $data["price"];
$totalSupply = $data["totalSupply"];
$quantityAvailable = $data["quantityAvailable"];
$collection_id = $data["collectionID"];

$token_added = $tokens->add_token(
                    $token_id,
                    $tier,
                    $ownership_offered,
                    $price,
                    $totalSupply,
                    $quantityAvailable,
                    $collection_id
);

if($token_added) {
    http_response_code(200);
    
    echo json_encode(array(
        "success" => true,
        "message" => _('Collection Added Successfully'),
    ));
} else {
    http_response_code(200);
    
    echo json_encode(array(
        "success" => true,
        "message" => _('Failed to create collection'),
    ));
}