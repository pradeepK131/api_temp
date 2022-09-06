<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


include_once '../config/database.php';
include_once '../controllers/collection-meta.php';
include_once '../controllers/tokens-meta.php';
include_once '../include/common.inc.php';

// $data = json_decode(file_get_contents("php://input"), true);

$database = new Database($LOG);
$db = $database->getConnection();

$collection = new CollectionMeta($db);
$tokens = new TokensMeta($db);

$collectionImageUploadPath = '../uploads/collectionImages/';
$tokenImageUploadPath = '../uploads/tokenImages/';

// $collectionId = $data["id"];
$collectionId = '8';

$isMInted = $collection->getMintStatus($collectionId);

$collectionImageName = $collection->getCollectionImageName($collectionId);
$tokenImageData = $tokens->getTokenImageNameByCollectionID($collectionId);

if($isMInted == '0') {
    $collectionDeleted = $collection->deleteCollection($collectionId);

    if($collectionDeleted) {

        unlink($collectionImageUploadPath.''.$collectionImageName);

        foreach ($tokenImageData as $key => $value) {
            unlink($tokenImageUploadPath .''. $value);
        }

        http_response_code(200);

        // sending login success
        echo json_encode(array(
                "message" => _("Deleted collection successfully"),
                "success" => true
            )
        );
    } else {
        http_response_code(200);

        // sending login success
        echo json_encode(array(
                "message" => _("Collection deletion failed"),
                "success" => false
            )
        );
    }
} else {
    http_response_code(200);

    // sending login success
    echo json_encode(array(
            "message" => _("Cannot delete minted collection"),
            "success" => false
        )
    );
}
