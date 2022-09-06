<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// files needed to connect to database
include_once '../config/database.php';
include_once '../controllers/user_meta.php';

// get posted data
$data = json_decode(file_get_contents("php://input"), true);

$user_lang = $data["language"];
$user_time_zone = $data["timeZone"];

// setting up translation and time_zone
include_once '../include/common.inc.php';
include_once '../include/time-zone.inc.php'; 

// get database connection
$database = new Database($LOG);
$db = $database->getConnection();

// instantiate user meta object
$user_meta = new UserMeta($db, $LOG);

$user_meta->user_id = (int)$data["userID"];


// getting old data before update
$old_lang = $user_meta->get_account_language();
$old_time_zone = $user_meta->get_account_time_zone();
$old_currency = $user_meta->get_account_currency();

$new_lang = $data["language"];
$new_time_zone = $data["timeZone"];
$new_currency = $data["currency"];


// generate json web token
use \Firebase\JWT\JWT;

// get jwt
$jwt=isset($data["authToken"]) ? $data["authToken"] : "";

// if jwt is not empty
if($jwt){
 
    // if decode succeed
    try {
        // decode jwt
        $decoded = JWT::decode($jwt, $key, array('HS256'));
        
        // comparing old data vs updated data to check the changes
        if($old_lang !== $new_lang) {
            $user_meta->user_id = (int)$data["userID"];
            $user_meta->updated = $timestamp;
            $user_meta->update_meta("language", $new_lang); // saving to meta table if data changes
        } 
        if($old_time_zone !== $new_time_zone) {
            $user_meta->user_id = (int)$data["userID"];
            $user_meta->updated = $timestamp;
            $user_meta->update_meta("time_zone", $new_time_zone); // saving to meta table if data changes
        } 
        if($old_currency !== $new_currency) {
            $user_meta->user_id = (int)$data["userID"];
            $user_meta->updated = $timestamp;
            $user_meta->update_meta("currency", $new_currency); // saving to meta table if data changes
        }

        $LOG->info("Account preference updated for user_id ".$user_meta->user_id);
        
        http_response_code(200);

        echo json_encode(array (
            "message" => _("Account Preference updated successfully."),
            "locale" => $new_lang,
            "time_zone" => $new_time_zone,
            "currency" => $new_currency,
            "success" => true
        ));
    }
    catch (Exception $e){
        $LOG->error(''.$e->getMessage());

        http_response_code(401);

        echo json_encode(array (
            "message" => "Access Denied",
            "error" => $e,
            "success" => false
        ));
    }

}