<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require '../config/database.php';
require '../controllers/collection-meta.php';
require '../controllers/tiers.php';
require '../controllers/artist.php';
require '../controllers/tiers-meta.php';
require '../controllers/tier-balances.php';
require '../controllers/token-balances.php';

include_once '../include/common.inc.php';

$data = json_decode(file_get_contents("php://input"), true);

$database = new Database($LOG);
$db = $database->getConnection();

$collection_meta = new CollectionMeta($db);
$tier = new Tiers($db);
$tiers_meta = new TiersMeta($db);
$tier_balances = new TierBalances($db);
$token_balances = new TokenBalances($db);
$artist = new Artists($db);

$wallet_address = $data['walletAddress'];

// $wallet_address = '0xdD282C6c6EBBae45309Ca59964E6Ae42a8984B92';

$balanceData = $tier_balances->get_tier_balances_by_user_wallet($wallet_address);

$result = [];

if(isset($balanceData)) {
    foreach ($balanceData as $key => $value) {
        $temp = $value;
        $tokens_meta_data = $tiers_meta->getTierData($value['collection_id'], $value['tier']);
        $token_balance_data = $token_balances->get_token_balances_by_user_wallet($wallet_address,$value['collection_id'], $value['tier']);
        $temp['token_tier'] = $tokens_meta_data['tier'];
        $temp['token_quantity'] = $tokens_meta_data['tier_quantity'];
        $temp['token_image'] = $tokens_meta_data['tier_image'];
        $temp['ownership_offered'] = $tokens_meta_data['ownership_offered'];
        $collection_meta_data = $collection_meta->getEditionNameById($tokens_meta_data['collection_id']);
        $temp['collection_name'] = $collection_meta_data['collection_name'];
        $temp['blockchain'] = $collection_meta_data['blockchain'];
        $temp['artist_name'] = $artist->get_artist_name($collection_meta_data['artist_id']);
        $temp['drop_date'] = $collection_meta_data['drop_release_time'];
        array_push($result, $temp);
    }
}

echo json_encode(array (
    "message" => _("Tokens data collected successfully"),
    "success" => true,
    "tokensMeta" => $result
));