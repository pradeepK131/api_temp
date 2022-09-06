<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require '../config/database.php';
require '../controllers/tier-balances.php';
require '../controllers/user_meta.php';
include_once '../include/common.inc.php';

$data = json_decode(file_get_contents("php://input"), true);

$database = new Database($LOG);
$db = $database->getConnection();

$tier_balances = new TierBalances($db);
$user_meta = new UserMeta($db, $LOG);

$collection_id = $data["collectionID"];
$skip = $data["skipNumber"];
$no_of_records = 1;

function get_default_user_name($wallet_address) {
    $wallet_array = str_split($wallet_address);
    $slice_position = sizeof($wallet_array) - 5;

    $user_suffix_array = array_slice($wallet_array, $slice_position);
    $user_suffix = implode($user_suffix_array);

    return 'User_' . $user_suffix;
}

$data_tier_balance = $tier_balances->get_pag_tier_balances($collection_id, $skip, $no_of_records);

$final_data = [];

if($data_tier_balance != null) {
    foreach ($data_tier_balance as $key => $value) {
        $temp = $value;
        $user_id = $user_meta->get_userid_for_wallet($value['wallet_address']);

        if($user_id != null) {
            $temp['collector_name'] = $user_meta->get_meta_data($user_id, 'full_name');
            $temp['collector_image'] = $user_meta->get_meta_data($user_id, 'userimage');
        } else {
            $temp['collector_name'] = get_default_user_name($value['wallet_address']);
            $temp['collector_image'] = 'blank.png';
        }
        array_push($final_data, $temp);
    }
}

http_response_code(200);

echo json_encode(array(
    "collectors_data" => $final_data,
    "success" => true
));