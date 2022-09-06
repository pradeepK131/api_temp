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

// $user_lang = $_POST["language"];
// $user_time_zone = $_POST["timeZone"];

// setting up translation and time_zone
include_once '../include/common.inc.php';
// include_once '../include/time-zone.inc.php';

$database = new Database($LOG);
$db = $database->getConnection();

$user = new User($db, $LOG);
$user_meta = new UserMeta($db, $LOG);

$user_meta->user_id = (int)$_POST["userID"];
$old_fullname = $user_meta->get_account_fullname();
$old_username = $user_meta->get_account_username();
$old_email = $user_meta->get_account_email();
$old_description = $user_meta->get_meta_data($_POST["userID"], 'description');


use \Firebase\JWT\JWT;

$timestamp = date('Y-m-d h:i:s');

$jwt=isset($_POST["authToken"]) ? $_POST["authToken"] : "";
$user->full_name = $_POST["name"];
$user->username = $_POST["handle"];
$user->email = $_POST["email"];
$user->id = $_POST["userID"];
$user->timestamp = $timestamp;

// if jwt is not empty
if($jwt){
 
    // if decode succeed
    try {
        // decode jwt
        // $decoded = JWT::decode($jwt, $key, array('HS256'));
 
        $profile_updated = $user->update_profile();

        if($profile_updated) {
            // comparing old data vs updated data to check the changes
            if($old_fullname !== $_POST["name"]) {
                $user_meta->user_id = (int)$_POST["userID"];
                $user_meta->updated = $timestamp;
                $user_meta->update_meta("full_name", $_POST["fullName"]); // saving to meta table if data changes
            } 
            if($old_username !== $_POST["handle"]) {
                $user_meta->user_id = (int)$_POST["userID"];
                $user_meta->updated = $timestamp;
                $user_meta->update_meta("username", $_POST["userName"]); // saving to meta table if data changes
            } 
            if($old_email !== $_POST["email"]) {
                $user_meta->user_id = (int)$_POST["userID"];
                $user_meta->updated = $timestamp;
                $user_meta->update_meta("email", $_POST["email"]); // saving to meta table if data changes
            }
            if($old_description !== $_POST["description"]) {
                $user_meta->user_id = (int)$_POST["userID"];
                $user_meta->updated = $timestamp;
                $user_meta->update_meta("description", $_POST["description"]); // saving to meta table if data changes
            }
            $verified = false;

            $user->emailExists();
            if($user->status === '1') {
                $verified = true;
            }
            $description = $user_meta->get_meta_data($_POST["userID"], 'description');

            $_user = array(
                "id" => $user->id,
                "full_name" => $user->full_name,
                "username" => $user->username,
                "email" => $user->email,
                "description" => $description,
                "is_verified" => $verified
            );

            $LOG->info("Profile updated for ".$user->email);
            
            http_response_code(200);

            echo json_encode(array (
                "message" => _("Profile successfully updated"),
                "user" => $_user,
                "success" => true
            ));
        } else {
            http_response_code(200);

            $LOG->error("Profile updates failed for ".$_POST["email"].".");

            echo json_encode(array (
                "message" => _("Profile update failed"),
                "success" => false
            ));
        }
    }
    catch (Exception $e){
        $LOG->error($e->getMessage());

        http_response_code(401);

        echo json_encode(array (
            "message" => "Access Denied",
            "error" => $e,
            "success" => false
        ));
    }

}
