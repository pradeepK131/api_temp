<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require '../config/database.php';
require '../controllers/collection-meta.php';
require '../controllers/tiers-meta.php';
require '../controllers/artist.php';

include_once '../include/common.inc.php';

$data = json_decode(file_get_contents("php://input"), true);

$database = new Database($LOG);
$db = $database->getConnection();

$collection_meta = new CollectionMeta($db);
$tiers_meta = new TiersMeta($db);
$artist = new Artists($db);

$tiersList = json_decode($data['tiersList'], true);

$result = array();

foreach ($tiersList as $key => $data) {
    $temp = array();
    $tiers_meta_data = $tiers_meta->getTierData($data['collectionID'], $data['tier']);
    $temp['token_tier'] = $tiers_meta_data['tier'];
    $temp['token_quantity'] = $tiers_meta_data['tier_quantity'];
    $temp['token_image'] = $tiers_meta_data['tier_image'];
    $temp['ownership_offered'] = $tiers_meta_data['ownership_offered'];
    $collection_meta_data = $collection_meta->getEditionNameById($tiers_meta_data['collection_id']);
    $temp['collection_name'] = $collection_meta_data['collection_name'];
    $temp['collection_id'] = $data['collectionID'];
    $temp['blockchain'] = $collection_meta_data['blockchain'];
    $temp['artist_name'] = $artist->get_artist_name($collection_meta_data['artist_id']);
    $temp['drop_date'] = $collection_meta_data['drop_release_time'];
    $result[$data['collectionID'] . $data['tier']] = $temp;
}

echo json_encode(array (
    "message" => _("Tokens data collected successfully"),
    "success" => true,
    "tokensMeta" => $result
));