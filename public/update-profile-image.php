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

// get posted data
$data = json_decode(file_get_contents("php://input"), true);

if(isset($_FILES['userImage'])) {
    $fileName  =  $_FILES['userImage']['name'];
    $tempPath  =  $_FILES['userImage']['tmp_name'];
    $fileSize  =  $_FILES['userImage']['size'];
}

$database = new Database($LOG);
$db = $database->getConnection();

$user = new User($db, $LOG);
$user_meta = new UserMeta($db, $LOG);

$user_id = $_POST["userID"];
$user_meta->user_id = $user_id;
$user_meta->updated = date('Y-m-d h:i:s');

$old_user_image = $user_meta->get_account_userimage();
$old_cover_image = $user_meta->get_user_cover_image($user_id);

$operation_success = false;

if(isset($_FILES['userImage'])) {
    $upload_path = '../uploads/userImages/';
    $fileExt = strtolower(pathinfo($fileName,PATHINFO_EXTENSION));

    if($fileSize < 5000000){
        if($old_user_image !== "blank.png") {
            unlink($upload_path . $old_user_image);
        }
        $rand = mt_rand(100000,999999);
        $new_file_name = "".$user_id."_".$rand."";
        move_uploaded_file($tempPath, $upload_path . $new_file_name .".".$fileExt); // move file from system temporary path to our upload folder path 
        $operation_success = $user_meta->update_meta("userImage", $new_file_name.".".$fileExt);
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
        "message" => _("User image successfully updated"),
        "success" => true,
        "userImages" => $user_images
    ));
} else {
    http_response_code(200);

    echo json_encode(array (
        "message" => _("Failed to update user image"),
        "success" => false
    ));
}