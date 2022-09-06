<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require '../config/database.php';
require '../controllers/collections.php';
require '../controllers/tiers.php';
require '../controllers/tier-balances.php';
require '../controllers/user_meta.php';

$data = json_decode(file_get_contents("php://input"), true);

// setting up translation and time_zone
include_once '../include/common.inc.php';

$database = new Database($LOG);
$db = $database->getConnection();

$collection = new Collections($db);
$tiers = new Tiers($db);
$tier_balances = new TierBalances($db);
$user_meta = new UserMeta($db, $LOG);

$collection_id = $data["collectionID"];

// $collection_id = '25';

$collection_data = $collection->get_collection_data($collection_id);

$tiers_list = json_decode($collection_data["tiers"]);

$tier_data = [];
$tier_balance_data = [];
$final_tier_balance = [];

function get_default_user_name($wallet_address) {
    $wallet_array = str_split($wallet_address);
    $slice_position = sizeof($wallet_array) - 5;

    $user_suffix_array = array_slice($wallet_array, $slice_position);
    $user_suffix = implode($user_suffix_array);

    return 'User_' . $user_suffix;
}

if(isset($tiers_list)) {
    for ($i=0; $i < sizeof($tiers_list); $i++) { 
        $data_tier = $tiers->get_tiers_data($collection_id, $tiers_list[$i]);
        $data_tier_balance = $tier_balances->get_tier_balances($collection_id, $tiers_list[$i]);

        array_push($tier_data, $data_tier);
    
        if(isset($data_tier_balance)) {
            for ($j=0; $j < sizeof($data_tier_balance); $j++) { 
                array_push($tier_balance_data, $data_tier_balance[$j]);
            }
        }
    }

    $limited_tier_balance_data = array_slice($tier_balance_data, 0, 1);

    foreach ($limited_tier_balance_data as $key => $value) {
        $temp = $value;
        $user_id = $user_meta->get_userid_for_wallet($value['wallet_address']);

        if($user_id != null) {
            $temp['collector_name'] = $user_meta->get_meta_data($user_id, 'full_name');
            $temp['collector_image'] = $user_meta->get_meta_data($user_id, 'userimage');
        } else {
            $temp['collector_name'] = get_default_user_name($value['wallet_address']);
            $temp['collector_image'] = 'blank.png';
        }
        array_push($final_tier_balance, $temp);
    }

    http_response_code(200);

    echo json_encode(array (
        "collection" => $collection_data,
        "tokens" => $tier_data,
        "token_balances" => $final_tier_balance,
        "success" => true,
    ));
} else {
    http_response_code(200);

    echo json_encode(array (
        "message" => _("Failed to fetch data"),
        "success" => false
    ));
}




