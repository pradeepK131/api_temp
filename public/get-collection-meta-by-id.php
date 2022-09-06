<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../controllers/collection-meta.php';
include_once '../controllers/tiers-meta.php';
include_once '../controllers/artist.php';
include_once '../include/common.inc.php';

$data = json_decode(file_get_contents("php://input"), true);

$database = new Database($LOG);
$db = $database->getConnection();

$collection = new CollectionMeta($db);
$tier = new TiersMeta($db);
$artist = new Artists($db);

// $collectionId = '25';

$collectionData = $collection->getCollectionDataById($data["id"]);
$artist_data = $artist->get_artist_data_by_id($collectionData['artist_id']);

$result = $collectionData;
$tierData = $tier->getTokensMetaByCollectionID($collectionData["id"]);
$result["tiersData"] = $tierData;


echo json_encode(array (
    "message" => _("Collection data fetched successfully"),
    "success" => true,
    "collectionMeta" => $result,
    "artistName" => $artist_data['artist_name']
));