<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../controllers/tiers-meta.php';
include_once '../include/common.inc.php';

$data = json_decode(file_get_contents("php://input"), true);

$database = new Database($LOG);
$db = $database->getConnection();

$tiers_meta = new TiersMeta($db);

$collection_id = $data['collectionID'];
$tier = $data['tier'];

// $collection_id = '24';
// $tier = '0';

$tier_data = $tiers_meta->getTierData($collection_id, $tier);

$tier_benefits = $tier_data['tier_extra_benefits'];

echo json_encode(array (
    "message" => _("Collection created successfully"),
    "success" => true,
    "tier_benefits" => $tier_benefits
));