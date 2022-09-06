<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../controllers/collection-meta.php';
include_once '../controllers/tiers-meta.php';
include_once '../controllers/artist.php';
include_once '../include/common.inc.php';

$database = new Database($LOG);
$db = $database->getConnection();

$collection = new CollectionMeta($db);
$tiers_meta = new TiersMeta($db);
$artist = new Artists($db);

$collectionData = $collection->getAllCollectionData();

$result = [];

if(isset($collectionData) && sizeof($collectionData) > 0) {
    foreach ($collectionData as $key => $value) {
        $temp = $value;
        $tierData = $tiers_meta->getTokensMetaByCollectionID($value["id"]);
        $artist_data = $artist->get_artist_data_by_id($value["artist_id"]);
        $temp["artistName"] = $artist_data["artist_name"];
        $temp["artistImage"] = $artist_data["artist_image"];
        $temp["tiersData"] = $tierData;
        array_push($result, $temp);
    }
}

echo json_encode(array (
    "message" => _("Collection created successfully"),
    "success" => true,
    "collectionsMeta" => $result
));