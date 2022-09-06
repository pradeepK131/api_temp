<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../controllers/royalty-claim-history.php';
require '../controllers/collection-meta.php';
require '../controllers/tiers-meta.php';
include_once '../include/common.inc.php';

$data = json_decode(file_get_contents("php://input"), true);

$database = new Database($LOG);
$db = $database->getConnection();

$royalty_claim_history = new KlaytnRoyaltyClaimHistory($db);
$collection_meta = new CollectionMeta($db);
$tiers_meta = new TiersMeta($db);

$wallet_address = $data['walletAddress'];

// $wallet_address = '0xdD282C6c6EBBae45309Ca59964E6Ae42a8984B92';

$royalty_data = $royalty_claim_history->get_royalty_claim_history_by_user_wallet($wallet_address);

$result = [];

if(isset($royalty_data)) {
    foreach ($royalty_data as $key => $value) {
        $temp = $value;
        $tiers_meta_data = $tiers_meta->getTierData($value['collection_id'], $value['tier']);
        $temp['token_tier'] = $tiers_meta_data['tier'];
        $temp['token_quantity'] = $tiers_meta_data['tier_quantity'];
        $temp['token_image'] = $tiers_meta_data['tier_image'];
        $collection_meta_data = $collection_meta->getEditionNameById($tiers_meta_data['collection_id']);
        $temp['collection_name'] = $collection_meta_data['collection_name'];
        $temp['blockchain'] = $collection_meta_data['blockchain'];
        
        array_push($result, $temp);
    }
}

echo json_encode(array (
    "message" => _("Royalty claim history data fetched successfully"),
    "success" => true,
    "claimData" => $result
));