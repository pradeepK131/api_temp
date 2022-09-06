<?php

// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../controllers/user_meta.php';
include_once '../include/common.inc.php';

$data = json_decode(file_get_contents("php://input"), true);

$database = new Database($LOG);
$db = $database->getConnection();

$user_meta = new UserMeta($db, $LOG);

$user_id = $data['userID'];
$wallet_address = $data['walletAddress'];
$wallet_name = $data['walletName'];

$user_meta->user_id = $user_id;
$user_meta->updated = date('Y-m-d h:i:s');

$operation_success = false;

if($wallet_name == 'metamask') {
    $operation_success = $user_meta->update_meta("metamask_wallet", $wallet_address);
} else if($wallet_name == 'kaikas') {
    $operation_success = $user_meta->update_meta("kaikas_wallet", $wallet_address);
}

if($operation_success) {
    http_response_code(200);

    echo json_encode(array (
        "message" => _("Wallet successfully updated"),
        "success" => true,
    ));
} else {
    http_response_code(200);

    echo json_encode(array (
        "message" => _("Failed to update wallet"),
        "success" => false
    ));
}