<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../controllers/artist.php';
include_once '../controllers/collection-meta.php';
include_once '../controllers/tiers-meta.php';
include_once '../include/common.inc.php';

$data = json_decode(file_get_contents("php://input"), true);

$database = new Database($LOG);
$db = $database->getConnection();

$artist = new Artists($db);
$collection_meta = new CollectionMeta($db);
$tiers_meta = new TiersMeta($db);

$artist_id = $data['artistID'];

// $artist_id = '16';

$artist_data = $artist->get_artist_data_by_id($artist_id);
$collection_data = $collection_meta->getCollectionDataForArtist($artist_id);

$result = [];

function get_total_ownership_share($list) {
    $total_ownership = 0;
    foreach ($list as $key => $value) {
        $total_ownership += $value['ownership_offered'] * $value['tier_quantity'];
    }
    return $total_ownership;
}

function get_token_quantity($list) {
    $total_quantity = 0;
    foreach ($list as $key => $value) {
        $total_quantity += $value['tier_quantity'];
    }
    return $total_quantity;
}

if($collection_data != null) {
    foreach ($collection_data as $key => $value) {
        if($value['is_minted'] == '1') {
            $temp = array();
            $temp['collection_id'] = $value['id'];
            $temp['collection_name'] = $value['collection_name'];
            $temp['collection_type'] = $value['collection_type'];
            $temp['collection_cover_image'] = $value['collection_cover_image'];
            $tiers_meta_data = $tiers_meta->getTokensMetaByCollectionID($value['id']);
            $temp['total_ownership'] = get_total_ownership_share($tiers_meta_data);
            $temp['total_quantity'] = get_token_quantity($tiers_meta_data);

            array_push($result, $temp);
        }
    }
}

http_response_code(200);

echo json_encode(array (
    "message" => _("Artist data collected successfully"),
    "success" => true,
    "artist_data" => $artist_data,
    "collection_data" => $result
));