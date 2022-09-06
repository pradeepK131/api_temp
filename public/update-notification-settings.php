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
$user_time_zone = $data["timezone"];

// setting up translation and time_zone
include_once '../include/common.inc.php';
include_once '../include/time-zone.inc.php'; 

// get database connection
$database = new Database($LOG);
$db = $database->getConnection();

// instantiate user meta object
$user_meta = new UserMeta($db, $LOG);

$user_meta->user_id = (int)$data["userID"];
$user_meta->updated = $timestamp;

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

        try {
            $user_meta->update_meta('Notification_settings', $data['dataString']);
            $user_meta->update_meta('minimum_bid_threshold', $data['minBidThreshold']);

            $LOG->info("Notification settings updated for user_id ".$user_meta->user_id);

            $notification_settings = array(
                "notificationSettings" => $data["dataString"],
                "minBidThreshold" => $data["minBidThreshold"]
            );
            http_response_code(200);

            echo json_encode(array (
                "message" => _gettext("Notification settings updated successfully."),
                "notification_settings" => $notification_settings,
                "success" => true
            ));
        } catch (Exception $e) {
            http_response_code(200);

            echo json_encode(array (
                "message" => _gettext("Notification settings update failed."),
                "error" => $e,
                "success" => false
            ));
        }
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