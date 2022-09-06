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

$wallet_list = $data['walletList'];

$final_data = array();

function get_default_user_name($wallet_address) {
    $wallet_array = str_split($wallet_address);
    $slice_position = sizeof($wallet_array) - 5;

    $user_suffix_array = array_slice($wallet_array, $slice_position);
    $user_suffix = implode($user_suffix_array);

    return 'User_' . $user_suffix;
}

if($wallet_list != null) {
    foreach ($wallet_list as $key => $value) {

        if(!isset($final_data[$value])) {
            $temp = array();
            $user_id = $user_meta->get_userid_for_wallet($value);

            if($user_id != null) {
                $collector_name = $user_meta->get_meta_data($user_id, 'full_name');
                $collector_image = $user_meta->get_meta_data($user_id, 'userimage');
                $final_data[$value] = array(
                    'collector_name' => $collector_name,
                    'collector_image' => $collector_image
                );
            } else {
                $collector_name = get_default_user_name($value);
                $collector_image = 'blank.png';
                $final_data[$value] = array(
                    'collector_name' => $collector_name,
                    'collector_image' => $collector_image
                );
            }
        }
    }
}

echo json_encode(array(
    "collectors_data" => $final_data
));