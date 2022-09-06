<?php
// required headers
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// files needed to connect to database
include_once '../include/common.inc.php';
include_once '../config/database.php';
 
// get database connection
$database = new Database($LOG);
$db = $database->getConnection();
 
// get posted data
$data = json_decode(file_get_contents("php://input"), true);
 
// set product property values for user
$sender_email = $data["email"];
$msg_subject = $data["subject"];
$msg_body = $data["description"];

// generate json web token
use \Firebase\JWT\JWT;

// get jwt
$jwt=isset($data["authToken"]) ? $data["authToken"] : "";

// if jwt is not empty
// if($jwt){
 
//     // if decode succeed,
//     try {
//         // decode jwt
//         $decoded = JWT::decode($jwt, $key, array('HS256'));

//         $subject = isset($msg_subject) ? $msg_subject : "";
//         $body = isset($msg_body) ? "<p>".$msg_body."</p><p>Regards,</p><p>".$sender_email."</p>" : "";

//         try {
//             //Recipients
//             $mail->setFrom(FROM_MAIL_ADDRESS, FROM_MAIL_NAME);
//             $mail->addAddress(ADMIN_EMAIL);     //Add a recipient

//             //Content
//             $mail->isHTML(true);//Set email format to HTML                                  
//             $mail->Subject = $subject;
//             $mail->Body    = $body;

//             $mail->send();

//             $LOG->info("Support form submitted to ADMIN");
            
//             // set response code
//             http_response_code(200);
        
//             echo json_encode(array(
//                 "message" => _("Request submitted successfully, our support team will get back to you as soon as possible."),
//                 "success" => true
//             ));

//         } catch (Exception $e) {
//             $LOG->error(''.$e->getMessage());

//             // set response code
//             http_response_code(200);
            
//             echo json_encode(array(
//                 "message" => _("Unable to submit a request, please try again later"),
//                 "success" => false
//             ));
//         }
 
//     } 
//     catch (Exception $e){
//         $LOG->error(''.$e->getMessage());

//         // set response code
//         http_response_code(401);
    
//         // tell the user access denied  & show error message
//         echo json_encode(array(
//             "message" => _("Access denied."),
//             "error" => $e->getMessage()
//         ));
//     }
// }

try {
    // decode jwt
    // $decoded = JWT::decode($jwt, $key, array('HS256'));

    $subject = isset($msg_subject) ? $msg_subject : "";
    $body = isset($msg_body) ? "<p>Hi,</p><p>".$msg_body."</p><p>Regards,</p><p>".$sender_email."</p>" : "";

    try {
        //Recipients
        $mail->setFrom(FROM_MAIL_ADDRESS, FROM_MAIL_NAME);
        $mail->addAddress(ADMIN_EMAIL);     //Add a recipient

        //Content
        $mail->isHTML(true);//Set email format to HTML                                  
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();

        $LOG->info("Support form submitted to ADMIN");
        
        // set response code
        http_response_code(200);
    
        echo json_encode(array(
            "message" => _("Request submitted successfully, our support team will get back to you as soon as possible."),
            "success" => true
        ));

    } catch (Exception $e) {
        $LOG->error(''.$e->getMessage());

        // set response code
        http_response_code(200);
        
        echo json_encode(array(
            "message" => _("Unable to submit a request, please try again later"),
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
?>