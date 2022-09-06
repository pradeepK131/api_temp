<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../controllers/deposits.php';
include_once '../include/common.inc.php';

$data = json_decode(file_get_contents("php://input"), true);

$database = new Database($LOG);
$db = $database->getConnection();

$deposits = new Deposits($db);

$collection_id = $data['collectionID'];

// $collection_id = '25';

$deposit_data = $deposits->get_deposits($collection_id);

http_response_code(200);

echo json_encode(array (
    "message" => _("Deposit data collected successfully"),
    "success" => true,
    "deposit_data" => $deposit_data
));