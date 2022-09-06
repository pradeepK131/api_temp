<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


include_once '../config/database.php';
include_once '../controllers/collection-meta.php';
include_once '../include/common.inc.php';

$data = json_decode(file_get_contents("php://input"), true);


$database = new Database($LOG);
$db = $database->getConnection();

$collection = new CollectionMeta($db);

$timestamp = date('Y-m-d h:i:s');

use \Firebase\JWT\JWT;

$jwt=isset($_POST["authToken"]) ? $_POST["authToken"] : "";

// if($jwt){
    
// } else {
//     http_response_code(400);
    
//     echo json_encode(array(
//         "message" => _("Access denied"),
//         "success" => false
//     ));
// }

try {
    $statusUpdated = $collection->updateMintStatus(
        $data['collectionID'],
        $data['txHash'],
        $timestamp
    );

    if($statusUpdated) {
        http_response_code(200);

        echo json_encode(array(
            "message" => _("Mint status updated successfully"),
            "success" => true
        ));
    } else {
        http_response_code(200);

        echo json_encode(array(
            "message" => _("Failed to update the mint status"),
            "success" => false
        ));
    }
} catch (Exception $e) {
    http_response_code(400);

    echo json_encode(array(
        "message" => _("Access denied"),
        "success" => false
    ));
}