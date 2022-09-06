<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require '../config/database.php';
require '../controllers/user.php';

// get posted data
$data = json_decode(file_get_contents("php://input"), true);

$user_lang = $data["locale"];
$user_time_zone = $data["timeZone"];

// setting up translation and time_zone
include_once '../include/common.inc.php';
include_once '../include/time-zone.inc.php';

// get database connection
$database = new Database($LOG);
$db = $database->getConnection();

// instantiate user object
$user = new User($db, $LOG);

$current_password = $data["currentPassword"];
$password = $data["password"];
$cpassword = $data["cPassword"];

$user->email = $data["email"];
$user->timestamp = date('Y-m-d h:i:s');
$user->emailExists();

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
        
        if(password_verify($current_password, $user->password)) {
            $user->password = $password;
            $update_password = $user->update_password();
            if($update_password) {
                $LOG->info("Password changed for ".$data["email"]);

                // set response code
                http_response_code(200);
    
                // display message: password changed
                echo json_encode(array(
                    "success" => true,
                    "message" => _("Your password has been successfully changed")
                ));
            } else {
                $LOG->error("Password change failed for ".$data["email"]);

                // set response code
                http_response_code(200);
    
                // display message: password change failed
                echo json_encode(array(
                    "success" => false,
                    "message" => _("Password change failed"),
                ));
            }
        } else {
            $LOG->error("Password change failed. ".$data["email"]." entered incorrect password");
            // set response code
            http_response_code(200);
    
            // display message: password change failed
            echo json_encode(array(
                "success" => false,
                "message" => _("Your password is incorrect"),
            ));
        }
    }
    catch (Exception $e){
        $LOG->error(''.$e->getMessage());
        http_response_code(401);

        echo json_encode(array (
            "message" => _("Access Denied"),
            "error" => $e,
            "success" => false
        ));
    }

}