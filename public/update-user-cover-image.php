<?php

// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../controllers/user.php';
include_once '../controllers/user_meta.php';
include_once '../include/common.inc.php';

if(isset($_FILES['coverImage'])) {
    $fileName  =  $_FILES['coverImage']['name'];
    $tempPath  =  $_FILES['coverImage']['tmp_name'];
    $fileSize  =  $_FILES['coverImage']['size'];
}

$database = new Database($LOG);
$db = $database->getConnection();

$user = new User($db, $LOG);
$user_meta = new UserMeta($db, $LOG);

$user_id = $_POST["userID"];
$user_meta->user_id = $user_id;
$user_meta->updated = date('Y-m-d h:i:s');

$user_image = $user_meta->get_account_userimage();
$old_cover_image = $user_meta->get_user_cover_image($user_id);

$operation_success = false;

if(isset($_FILES['coverImage'])) {
    $upload_path = '../uploads/userCoverImages/';
    $fileExt = strtolower(pathinfo($fileName,PATHINFO_EXTENSION));

    if($fileSize < 5000000){
        if($old_cover_image !== "default") {
            unlink($upload_path . $old_cover_image);
        }
        $rand = mt_rand(100000,999999);
        $new_file_name = "".$user_id."_".$rand."";
        move_uploaded_file($tempPath, $upload_path . $new_file_name .".".$fileExt); // move file from system temporary path to our upload folder path 
        $operation_success = $user_meta->update_meta("user_cover_image", $new_file_name.".".$fileExt);
    }
}

if($operation_success) {
    $user_image = $user_meta->get_account_userimage();
    $cover_image = $user_meta->get_user_cover_image($user_id);

    $user_images = array(
        "coverImage" => $cover_image,
        "userImage" => $user_image
    );

    http_response_code(200);

    echo json_encode(array (
        "message" => _("Cover image successfully updated"),
        "success" => true,
        "userImages" => $user_images
    ));
} else {
    http_response_code(200);

    echo json_encode(array (
        "message" => _("Failed to update cover image"),
        "success" => false
    ));
}