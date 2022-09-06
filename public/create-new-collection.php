<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


include_once '../config/database.php';
include_once '../controllers/collection-meta.php';
include_once '../controllers/tiers-meta.php';
include_once '../include/common.inc.php';

$database = new Database($LOG);
$db = $database->getConnection();

$collection = new CollectionMeta($db);
$tier = new TiersMeta($db);

$collectionImageUploadPath = '../uploads/collectionImages/';
$tierImageUploadPath = '../uploads/tierImages/';
$collectionCoverImageUploadPath = '../uploads/collectionCoverImages/';

$timestamp = date('Y-m-d h:i:s');

use \Firebase\JWT\JWT;

$jwt=isset($_POST["authToken"]) ? $_POST["authToken"] : "";

if($jwt){
    try {
        $collectionImageFileName  =  $_FILES['image']['name'];
        $collectionImageTempPath  =  $_FILES['image']['tmp_name'];
        $collectionImagefileSize  =  $_FILES['image']['size'];

        if(
            empty($collectionImageFileName)
        ) {
            $errorMSG = json_encode(array("message" => "Collection image is missing. Please select an image", "success" => false));	
            echo $errorMSG;
        }

        $fileExt1 = strtolower(pathinfo($collectionImageFileName,PATHINFO_EXTENSION));

        if($collectionImagefileSize < 5000000){
            move_uploaded_file($collectionImageTempPath, $collectionImageUploadPath . $collectionImageFileName); // move file from system temporary path to our upload folder path 
        } else {
            $errorMSG = json_encode(array("message" => "Collection image size exceeds 5MB", "success" => false));	
            echo $errorMSG;
        }

        $collectionCoverImageFileName  =  $_FILES['dropCoverImage']['name'];
        $collectionCoverImageTempPath  =  $_FILES['dropCoverImage']['tmp_name'];
        $collectionCoverImagefileSize  =  $_FILES['dropCoverImage']['size'];

        if(
            empty($collectionCoverImageFileName)
        ) {
            $errorMSG = json_encode(array("message" => "Collection cover image is missing. Please select an image", "success" => false));	
            echo $errorMSG;
        }

        $fileExtc = strtolower(pathinfo($collectionCoverImageFileName,PATHINFO_EXTENSION));

        if($collectionCoverImagefileSize < 5000000){
            move_uploaded_file($collectionCoverImageTempPath, $collectionCoverImageUploadPath . $collectionCoverImageFileName); // move file from system temporary path to our upload folder path 
        } else {
            $errorMSG = json_encode(array("message" => "Collection cover image size exceeds 5MB", "success" => false));	
            echo $errorMSG;
        }

        $collectionId = $collection->createCollection(
            $_POST["artistID"],
            $_POST["collectionName"],
            $_POST["collectionType"],
            $_POST["collectionOwner"],
            $collectionImageFileName,
            $collectionCoverImageFileName,
            $_POST["blockchain"],
            $_POST["spotifyURL"],
            $_POST["dropReleaseDateTime"],
            $_POST["collectionDescription"],
            $timestamp
        );

        if($_POST["hasGoldTier"] == "true") {
            $goldTierImageFileName  =  $_FILES['goldTierImage']['name'];
            $goldTierImageTempPath  =  $_FILES['goldTierImage']['tmp_name'];
            $goldTierImagefileSize  =  $_FILES['goldTierImage']['size'];

            if(
                empty($goldTierImageFileName)
            ) {
                $errorMSG = json_encode(array("message" => "Gold tier image is missing. Please select an image", "success" => false));	
                echo $errorMSG;
            }
            $fileExt2 = strtolower(pathinfo($goldTierImageFileName,PATHINFO_EXTENSION));
            if($goldTierImagefileSize < 5000000){
                move_uploaded_file($goldTierImageTempPath, $tierImageUploadPath . $collectionId."0.".$fileExt2); // move file from system temporary path to our upload folder path 
            } else {
                $errorMSG = json_encode(array("message" => "Tier image size exceeds 5MB", "success" => false));	
                echo $errorMSG;
            }

            $addedGoldTier = $tier->addTierData(
                $collectionId,
                $collectionId."0.".$fileExt2,
                $_POST["ownershipOfferedForGoldTier"],
                $_POST["goldTierPrice"],
                '0',
                $_POST["goldTierQuantity"],
                $_POST["goldTierBenefits"]
            );
        }

        if($_POST["hasPlatinumTier"] == "true") {
            $platinumTierImageFileName  =  $_FILES['platinumTierImage']['name'];
            $platinumTierImageTempPath  =  $_FILES['platinumTierImage']['tmp_name'];
            $platinumTierImagefileSize  =  $_FILES['platinumTierImage']['size'];

            if(
                empty($platinumTierImageFileName)
            ) {
                $errorMSG = json_encode(array("message" => "Platinum tier image is missing. Please select an image", "success" => false));	
                echo $errorMSG;
            }

            $fileExt3 = strtolower(pathinfo($platinumTierImageFileName,PATHINFO_EXTENSION));
            if($platinumTierImagefileSize < 5000000){
                move_uploaded_file($platinumTierImageTempPath, $tierImageUploadPath . $collectionId."1.".$fileExt3); // move file from system temporary path to our upload folder path 
            } else {
                $errorMSG = json_encode(array("message" => "Tier image size exceeds 5MB", "success" => false));	
                echo $errorMSG;
            }

            $addedPlatinumTier = $tier->addTierData(
                $collectionId,
                $collectionId."1.".$fileExt3,
                $_POST["ownershipOfferedForPlatinumTier"],
                $_POST["platinumTierPrice"],
                '1',
                $_POST["platinumTierQuantity"],
                $_POST["platinumTierBenefits"]
            );
        }

        if($_POST["hasDiamondTier"] == "true") {
            $diamondTierImageFileName  =  $_FILES['diamondTierImage']['name'];
            $diamondTierImageTempPath  =  $_FILES['diamondTierImage']['tmp_name'];
            $diamondTierImagefileSize  =  $_FILES['diamondTierImage']['size'];

            if(
                empty($diamondTierImageFileName)
            ) {
                $errorMSG = json_encode(array("message" => "Diamond tier image is missing. Please select an image", "success" => false));	
                echo $errorMSG;
            }

            $fileExt4 = strtolower(pathinfo($diamondTierImageFileName,PATHINFO_EXTENSION));

            if($diamondTierImagefileSize < 5000000){
                move_uploaded_file($diamondTierImageTempPath, $tierImageUploadPath . $collectionId."2.".$fileExt4); // move file from system temporary path to our upload folder path 
            } else {
                $errorMSG = json_encode(array("message" => "Tier image size exceeds 5MB", "success" => false));	
                echo $errorMSG;
            }

            $addedDiamondTier = $tier->addTierData(
                $collectionId,
                $collectionId."2.".$fileExt4,
                $_POST["ownershipOfferedForDiamondTier"],
                $_POST["diamondTierPrice"],
                '2',
                $_POST["diamondTierQuantity"],
                $_POST["diamondTierBenefits"]
            );
        }

        $success = false;

        if($_POST["hasGoldTier"] == "true") {
            if($addedGoldTier) {
                $success = true;
            } else {
                $success = false;
            }
        }

        if($_POST["hasPlatinumTier"] == "true") {
            if($addedPlatinumTier) {
                $success = true;
            } else {
                $success = false;
            }
        }

        if($_POST["hasDiamondTier"] == "true") {
            if($addedDiamondTier) {
                $success = true;
            } else {
                $success = false;
            }
        }
        if($success) {
            http_response_code(200);

            echo json_encode(array (
                "message" => _("Collection created successfully"),
                "success" => true
            ));
        } else {
            http_response_code(200);

            echo json_encode(array (
                "message" => _("Failed to create the collection"),
                "success" => false
            ));
        }
    } 
    catch (Exception $e) {
        http_response_code(200);
        
        $LOG->error(''.$e->getMessage());

        echo json_encode(array (
            "message" => _("Failed to create the collection"),
            "error" => $e->getMessage(),
            "success" => false
        ));
    }
} else {
    http_response_code(401);

    echo json_encode(array (
        "message" => _("Access denied"),
        "success" => false
    ));
}