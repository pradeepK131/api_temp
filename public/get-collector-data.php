<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require '../config/database.php';
require '../controllers/user_meta.php';
include_once '../include/common.inc.php';

$data = json_decode(file_get_contents("php://input"), true);

$database = new Database($LOG);
$db = $database->getConnection();

$user_meta = new UserMeta($db, $LOG);

$wallet_address = $data['walletAddress'];

// $wallet_address = '0x629b6eB5489ea2082d5De40728Dc499A5335Ae5e';

$user_id = $user_meta->get_userid_for_wallet($wallet_address);


$user_meta_data = array();

if($user_id != null) {
    $user_meta_data['collector_name'] = $user_meta->get_meta_data($user_id, 'full_name');
    $user_meta_data['collector_image'] = $user_meta->get_meta_data($user_id, 'userimage');
    $user_meta_data['collector_cover_image'] = $user_meta->get_meta_data($user_id, 'user_cover_image');
}

http_response_code(200);

echo json_encode(array(
    "collector_data" => $user_meta_data,
    "success" => true
));