<?php

// required headers
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

include_once '../include/common.inc.php';
include_once '../config/database.php';
include_once '../controllers/user.php';
include_once '../controllers/user_meta.php';

// get posted data
$data = json_decode(file_get_contents("php://input"), true);

// get database connection
$database = new Database($LOG);
$db = $database->getConnection();

// instantiate object
$user = new User($db, $LOG);

$user_meta = new UserMeta($db, $LOG);

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
        
        if(isset($data["token"])) {
            $user->token = $data["token"];
        
            if(!empty($user->token)) {
                $verified = $user->verify_tokn();
                $user->emailExists(); // to get user details
                if($verified){
                    $user->timestamp = date('Y-m-d h:i:s');
                    $status_updated = $user->update_status();

                    $user_meta->user_id = (int)$user->id;
                    $description = $user_meta->get_meta_data($user->id, 'description');
                    $is_admin = $user_meta->get_meta_data($user->id, 'is_admin');
                    $user_image = $user_meta->get_account_userimage();
                    $user_cover_image = $user_meta->get_user_cover_image($user->id);

                    if($status_updated) {
                        $_user = array(
                            "id" => $user->id,
                            "avatar" => "blank.png",
                            "cover_image" => $user_cover_image,
                            "full_name" => $user->full_name,
                            "username" => $user->username,
                            "email" => $user->email,
                            "description" => $description,
                            "is_admin" => $is_admin
                        );
                    
                        $LOG->info("Verification completed for ".$user->email);
                        // set response code
                        http_response_code(200);
    
                        echo json_encode(
                                array(
                                    "message" => _("Your email verification is complete"),
                                    "user" => $_user,
                                    "is_verified" => true,
                                    "success" => true,
                                )
                        );
                    } else {
                        $LOG->warn("Verifiaction failed for ".$user->email);
                        // set response code
                        http_response_code(200);
                    
                        // tell the user verification failed
                        echo json_encode(array(
                            "message" => _("Your email verification failed"),
                            "success" => false
                        ));
                    }
                }
            }
        }
 
    } 
    catch (Exception $e){
        $LOG->error(''.$e);
        // set response code
        http_response_code(401);    
    
        // tell the user access denied  & show error message
        echo json_encode(array(
            "message" => _("Access denied."),
            "error" => $e->getMessage(),
            "success" => false
        ));
    }
}