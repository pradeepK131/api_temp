<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");
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

// generate otp
$otp = mt_rand(100000,999999);

// set product property values for user and password reset record
$user->email = $data["email"];
$user->token = $otp;
$user->timestamp = date('Y-m-d h:i:s');

// check if the email exists in our table
$email_exist = $user->emailExists();


if($email_exist) {
    $verified = $user->update_token();

    $subject = _("Password Request Link");
    $body = "<p> As per your request we have sent a password reset link :&nbsp;
            <a href='http://localhost:3011/metronic8/react/demo1/auth/reset-password?token={$otp}'>Click Here</a></p>";

    try {
        //Recipients
        $mail->setFrom(FROM_MAIL_ADDRESS, FROM_MAIL_NAME);
        $mail->addAddress($user->email);     //Add a recipient

        //Content
        $mail->isHTML(true);//Set email format to HTML                                  
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();

        $LOG->info("Mail sent to ".$user->email);

        // set response code
        http_response_code(200);
    
        echo json_encode(array(
            "message" => _("Password reset link has been sent to your email."),
            "sent_email" => true
            ));

    } catch (Exception $e) {
        
        $LOG->error(''.$e->getMessage());

        // set response code
        http_response_code(200);
    
        // if unable to send email
        echo json_encode(array(
            "message" => _("Couldn't send an email. Please check your email address"),
            "sent_email" => false
            ));
    }
    
}
else {
    $LOG->info("Unable to send email to ".$data["email"].". Email not exists in the record");
    // set response code
    http_response_code(200);
 
    // email not registered in database
    echo json_encode(array(
        "message" => _("Your email address is not registered in our record."),
        "sent_email" => false
    ));
}

