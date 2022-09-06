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

$timestamp = date('Y-m-d h:i:s');

use \Firebase\JWT\JWT;

$jwt=isset($_POST["authToken"]) ? $_POST["authToken"] : "";

$collectionId = $_POST["collectionID"];
$oldCollectionImageName = $collection->getCollectionImageName($collectionId);
$oldCollectionCoverImageName = $collection->getCollectionCoverImageName($collectionId);
$isMInted = $collection->getMintStatus($collectionId);

if($isMInted == '0') {
    if($jwt){
        try {
            $collectionImageFileName = $oldCollectionImageName;
    
            if(isset($_FILES['image'])) {
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
                    unlink($collectionImageUploadPath.$oldCollectionImageName);
                    move_uploaded_file($collectionImageTempPath, $collectionImageUploadPath . $collectionImageFileName); // move file from system temporary path to our upload folder path 
                } else {
                    $errorMSG = json_encode(array("message" => "Collection image size exceeds 5MB", "success" => false));	
                    echo $errorMSG;
                }
            }

            $collectionCoverImageFileName = $oldCollectionCoverImageName;
    
            if(isset($_FILES['dropCoverImage'])) {
                $collectionCoverImageFileName  =  $_FILES['dropCoverImage']['name'];
                $collectionCoverImageTempPath  =  $_FILES['dropCoverImage']['tmp_name'];
                $collectionCoverImagefileSize  =  $_FILES['dropCoverImage']['size'];
    
                if(
                    empty($collectionCoverImageFileName)
                ) {
                    $errorMSG = json_encode(array("message" => "Collection cover image is missing. Please select an image", "success" => false));	
                    echo $errorMSG;
                }
        
                $fileExt1 = strtolower(pathinfo($collectionCoverImageFileName,PATHINFO_EXTENSION));
        
                if($collectionCoverImagefileSize < 5000000){
                    unlink($collectionCoverImageUploadPath.$oldCollectionCoverImageName);
                    move_uploaded_file($collectionCoverImageTempPath, $collectionCoverImageUploadPath . $collectionCoverImageFileName); // move file from system temporary path to our upload folder path 
                } else {
                    $errorMSG = json_encode(array("message" => "Collection cover image size exceeds 5MB", "success" => false));	
                    echo $errorMSG;
                }
            }
    
            $collectionUpdated = $collection->updateCollection(
                $collectionId,
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
                $goldTierTokenId = $collectionId . '0';
                $gtierImageName = $tier->getTierImageName($collectionId, '0');
    
                if(isset($_FILES['goldTokenImage'])) {
                    $goldTierImageFileName  =  $_FILES['goldTokenImage']['name'];
                    $goldTierImageTempPath  =  $_FILES['goldTokenImage']['tmp_name'];
                    $goldTierImagefileSize  =  $_FILES['goldTokenImage']['size'];
    
                    if(
                        empty($goldTierImageFileName)
                    ) {
                        $errorMSG = json_encode(array("message" => "Gold tier image is missing. Please select an image", "success" => false));	
                        echo $errorMSG;
                    }
                    $fileExt2 = strtolower(pathinfo($goldTierImageFileName,PATHINFO_EXTENSION));
                    if($goldTierImagefileSize < 5000000){
                        unlink($tierImageUploadPath . $collectionId."0.".$fileExt2);
                        $gtierImageName = $collectionId."0.".$fileExt2;
                        move_uploaded_file($goldTierImageTempPath, $tierImageUploadPath . $gtierImageName); // move file from system temporary path to our upload folder path 
                    } else {
                        $errorMSG = json_encode(array("message" => "Token image size exceeds 5MB", "success" => false));	
                        echo $errorMSG;
                    }
                }
    
                $addedGoldTier = $tier->updateTierData(
                    "0",
                    $collectionId,
                    $gtierImageName,
                    $_POST["ownershipOfferedForGoldTier"],
                    $_POST["goldTierPrice"],
                    $_POST["goldTierQuantity"],
                    $_POST["goldTierBenefits"]
                );
            }
    
            if($_POST["hasPlatinumTier"] == "true") {
    
                $platinumTierTokenId = $collectionId . '1';
                $ptierImageName = $tier->getTierImageName($collectionId, '1');
    
                if(isset($_FILES['platinumTokenImage'])) {
                    $platinumTierImageFileName  =  $_FILES['platinumTokenImage']['name'];
                    $platinumTierImageTempPath  =  $_FILES['platinumTokenImage']['tmp_name'];
                    $platinumTierImagefileSize  =  $_FILES['platinumTokenImage']['size'];
    
                    if(
                        empty($platinumTierImageFileName)
                    ) {
                        $errorMSG = json_encode(array("message" => "Platinum tier image is missing. Please select an image", "success" => false));	
                        echo $errorMSG;
                    }
    
                    $fileExt3 = strtolower(pathinfo($platinumTierImageFileName,PATHINFO_EXTENSION));
                    if($platinumTierImagefileSize < 5000000){
                        $ptierImageName = $collectionId."1.".$fileExt3;
                        unlink($tierImageUploadPath . $collectionId."1.".$fileExt3);
                        move_uploaded_file($platinumTierImageTempPath, $tierImageUploadPath . $ptierImageName); // move file from system temporary path to our upload folder path 
                    } else {
                        $errorMSG = json_encode(array("message" => "Token image size exceeds 5MB", "success" => false));	
                        echo $errorMSG;
                    }
                }
    
                $addedPlatinumTier = $tier->updateTierData(
                    "1",
                    $collectionId,
                    $ptierImageName,
                    $_POST["ownershipOfferedForPlatinumTier"],
                    $_POST["platinumTierPrice"],
                    $_POST["platinumTierQuantity"],
                    $_POST["platinumTierBenefits"]
                );
            }
    
            if($_POST["hasDiamondTier"] == "true") {
    
                $diamondTierTokenId = $collectionId . '2';
                $dtierImageName = $tier->getTierImageName($collectionId, '2');
    
                if(isset($_FILES['diamondTokenImage'])) {
                    $diamondTierImageFileName  =  $_FILES['diamondTokenImage']['name'];
                    $diamondTierImageTempPath  =  $_FILES['diamondTokenImage']['tmp_name'];
                    $diamondTierImagefileSize  =  $_FILES['diamondTokenImage']['size'];
    
                    if(
                        empty($diamondTierImageFileName)
                    ) {
                        $errorMSG = json_encode(array("message" => "Diamond tier image is missing. Please select an image", "success" => false));	
                        echo $errorMSG;
                    }
    
                    $fileExt4 = strtolower(pathinfo($diamondTierImageFileName,PATHINFO_EXTENSION));
    
                    if($diamondTierImagefileSize < 5000000){
                        $dtierImageName = $collectionId."2.".$fileExt4;
                        unlink($tierImageUploadPath . $collectionId."2.".$fileExt4);
                        move_uploaded_file($diamondTierImageTempPath, $tierImageUploadPath . $dtierImageName); // move file from system temporary path to our upload folder path 
                    } else {
                        $errorMSG = json_encode(array("message" => "Token image size exceeds 5MB", "success" => false));	
                        echo $errorMSG;
                    }
                }
    
                $addedDiamondTier = $tier->updateTierData(
                    "2",
                    $collectionId,
                    $dtierImageName,
                    $_POST["ownershipOfferedForDiamondTier"],
                    $_POST["diamondTierPrice"],
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
                    "message" => _("Collection updated successfully"),
                    "success" => true
                ));
            } else {
                http_response_code(200);
    
                echo json_encode(array (
                    "message" => _("Failed to update the collection"),
                    "success" => false
                ));
            }
        } 
        catch (Exception $e) {
            http_response_code(200);
    
            echo json_encode(array (
                "message" => _("Failed to update the collection"),
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
} else {
    http_response_code(401);
    
    echo json_encode(array (
        "message" => _("Cannot update minted collection"),
        "success" => false
    ));
}