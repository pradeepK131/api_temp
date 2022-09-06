<?php
// required headers
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// files needed to connect to database
include_once '../include/common.inc.php';
include_once '../config/database.php';
include_once '../controllers/user.php';
include_once '../controllers/user_meta.php';

 
// get database connection
$database = new Database($LOG);
$db = $database->getConnection();
 
// instantiate user object
$user = new User($db, $LOG);

// instantiate user meta object
$user_meta = new UserMeta($db, $LOG);
 
// get posted data
$data = json_decode(file_get_contents("php://input"), true);
 
// generate otp
$otp = mt_rand(100000,999999);

// set product property values for user
$user->full_name = $data["fullname"];
$user->username = $data["username"];
$user->email = $data["email"];
$user->password = $data["password"];
$user->token = $otp;
$user->timestamp = date('Y-m-d h:i:s');

$default_notification_settings = '{"itemSold": false, "bidActivity": false, "priceChange": false,
    "auctionExpiration": true, "outBid": false, "referralSuccessful": true, "ownedAssetUpdates": false,
    "successfulPurchase": false}';

// generate json web token
use \Firebase\JWT\JWT;


// create the user
if(
    !empty($user->full_name) &&
    !empty($user->username) &&
    !empty($user->email) &&
    !empty($user->password) &&
    !$user->emailExists()&& // to check if email already exists(before user creation)
    $user->create()
){

    // calling emailExists again to get userId(after user creation)
    $user->emailExists();

    $LOG->info("Account created successfully for ". $user->email);

    $user_id = (int) $user->id;

    $user_meta->user_id = $user_id;
    $user_meta->created = date('Y-m-d h:i:s');
    $user_meta->updated = date('Y-m-d h:i:s');

    $user_meta->create_default_meta('full_name', $user->full_name);
    $user_meta->create_default_meta('username', $user->username);
    $user_meta->create_default_meta('email', $user->email);
    $user_meta->create_default_meta('description', '');
    $user_meta->create_default_meta('user_cover_image', 'default');
    $user_meta->create_default_meta('userimage', 'blank.png');
    $user_meta->create_default_meta('metamask_wallet', '');
    $user_meta->create_default_meta('kaikas_wallet', '');
    $user_meta->create_default_meta('is_admin', '0'); 

    $token = array( 
        "iat" => $issued_at,
        "exp" => $expiration_time,
        "iss" => $issuer,
        "data" => array(
            "id" => $user->id,
            "full_name" => $user->full_name,
            "username" => $user->username,
            "email" => $user->email
        )
     );
    
     $_user = array(
         "id" => $user->id,
         "avatar" => "blank.png",
         "cover_image" => "default",
         "full_name" => $user->full_name,
         "username" => $user->username,
         "email" => $user->email,
         "description" => '',
         "is_admin" => '0'
     );


    $subject = _("Account created successfully");

    $body = "<p><strong>"._("Your email verification failed")."</strong>. 
            "._("Verification link stays valid for")." <em>"._("24 hours")."</em>. 
            "._("Use the follwoing link to verify and login")." :&nbsp;
            <a href='http://localhost/musician/verify-user?token={$otp}'>"._("Verify Here")."</a></p>";

    try {
        //Recipients
        $mail->setFrom(FROM_MAIL_ADDRESS, FROM_MAIL_NAME);
        $mail->addAddress($user->email);     //Add a recipient

        //Content
        $mail->isHTML(true);//Set email format to HTML                                  
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        
        // generate jwt
        $jwt = JWT::encode($token, $key);

        $LOG->info("Mail sent to ".$user->email);
        // set response code
        http_response_code(200);
    
        echo json_encode(array(
            "message" => _("Account created successfully and verification email has been sent to your email"),
            "token" => $jwt,
            "user" => $_user,
            "is_verified" => false,
            "created" => true
        ));
    } catch (Exception $e) {

        $LOG->error(''.$e->getMessage());

        // generate jwt
        $jwt = JWT::encode($token, $key);

        // set response code
        http_response_code(200);
    
        echo json_encode(array(
            "message" => _("Account created successfully but we are unable to send verification email."),
            "token" => $jwt,
            "user" => $_user,
            "is_verified" => false,
            "created" => true
        ));
    }
}
// message if unable to create user or if email already exists
else{
    // check email already exists in our database
    if($user->emailExists()) {

        $LOG->error("Account creation failed for ".$data["email"].". Email already exists.");
        // set response code
        http_response_code(200);
    
        // display message: email already exists
        echo json_encode(array(
            "message" => _("Email already exists and linked with another account"),
            "created" => false
        ));
    } else {
        $LOG->error("Account creation failed for ".$data["email"].".");
        // set response code
        http_response_code(400);

        echo json_encode(array(
            "message" => _("Registration Failed"),
            "created" => false
        ));
    }
}
?>

