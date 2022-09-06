<?php
// required headers
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// files needed to connect to database
include_once '../include/common.inc.php';
include_once '../config/database.php';
include_once '../controllers/user.php';
 
// get database connection
$database = new Database($LOG);
$db = $database->getConnection();
 
// instantiate database object
$user = new User($db, $LOG);
 
// get posted data
$data = json_decode(file_get_contents("php://input"), true);
 
// set product property values for user
$user->email = $data["email"];

// generate json web token
use \Firebase\JWT\JWT;

// get jwt
$jwt=isset($data["authToken"]) ? $data["authToken"] : "";

// if jwt is not empty
if($jwt){
 
    // if decode succeed,
    try {
        // decode jwt
        $decoded = JWT::decode($jwt, $key, array('HS256'));
 
        // set product property values for verify_user
        $otp = mt_rand(100000,999999);
        $user->token = $otp;
        $user->timestamp = date('Y-m-d h:i:s');
        
        $token_updated = $user->update_token();

        // check user exists
        if($user->emailExists() && $token_updated){

            $subject = _("Email Verification");
            $body = _("<p>Here is your verification link. Verification link stays valid for <em>24 hours</em>. 
                    <a href='http://localhost:3011/music/verify-user?token={$otp}'>
                    Click Here
                    </a></p>");

            try {
                //Recipients
                $mail->setFrom(FROM_MAIL_ADDRESS, FROM_MAIL_NAME);
                $mail->addAddress($user->email);     //Add a recipient

                //Content
                $mail->isHTML(true);//Set email format to HTML                                  
                $mail->Subject = $subject;
                $mail->Body    = $body;

                $mail->send();

                $LOG->info("Verification mail sent to ".$user->email);
                
                // set response code
                http_response_code(200);
            
                echo json_encode(array(
                    "message" => _("Verification email has been successfully sent to your email."),
                    "success" => true
                ));

            } catch (Exception $e) {
                $LOG->error(''.$e->getMessage());

                // set response code
                http_response_code(200);
                
                echo json_encode(array(
                    "message" => _("Unable to send email, please check your email address"),
                    "success" => false
                ));
            }
        }
        // message if unable to find email
        else {
            
            $LOG->error("Verification resend request failed for ".$data["email"].". Email doest not exists.");
            // set response code
            http_response_code(200);
        
            echo json_encode(array(
                "message" => _("Email does not exist in our record"),
                "success" => false
            ));
        }
 
    } 
    catch (Exception $e){
        $LOG->error(''.$e->getMessage());

        // set response code
        http_response_code(401);
    
        // tell the user access denied  & show error message
        echo json_encode(array(
            "message" => _("Access denied."),
            "error" => $e->getMessage()
        ));
    }
}
?>