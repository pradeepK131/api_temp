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
include_once '../controllers/collection-meta.php';
 
// get database connection
$database = new Database($LOG);
$db = $database->getConnection();
 
// instantiate database object
$user = new User($db, $LOG);
$collection = new CollectionMeta($db); 

// get posted data
$data = json_decode(file_get_contents("php://input"), true);
 
// set product property values for user
$email = $data["email"];
$collection_id = $data["collectionID"];
$tier = $data["tier"];
$quantity = $data["quantity"];
$royalty_amount = $data["royaltyAmount"];
$blockchain = $data["blockchain"];
$tx_hash = $data["txHash"];
$claimer_name = $data["claimer"];
$claimer_id = $data["claimerID"];

// $email = "joypradeep7@gmail.com";
// $collection_name = "The Hooddie";
// $tier = "1";
// $quantity = "3";
// $royalty_amount = "0.2";
// $blockchain = "ethereum";
// $tx_hash = "0xkkdk";
// $claimer_name = "User123";
// $claimer_id = "1";

$tier_map = array(
    "0" => "Gold",
    "1" => "Platinum",
    "2" => "Diamond"
);

$explorer_link = array(
    "ethereum" => "https://rinkeby.etherscan.io/tx/",
    "klaytn" => "https://baobab.scope.klaytn.com/tx/"
);

$price_type = array(
    "ethereum" => "ETH",
    "klaytn" => "KLAY"
);

$collectionData = $collection->getCollectionDataById($collection_id);
$collection_name = $collectionData["collection_name"];

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
 
//         // set product property values for verify_user

//         // check user exists
//         $subject = "Royalty Claim Successfull";
//         $body = "<p>You have successfully claimed the royalty 
//                     amount of <b>".$royalty_amount." ".$price_type[$blockchain]."</b> for a quantity of 
//                     <b>".$quantity." ".$tier_map[$tier]."</b> tier tokens that belongs to 
//                     <b>".$collection_name."</b> collection.
//                     Check your transaction
//                     <a href='".$explorer_link[$blockchain]."/".$tx_hash."'>
//                     here
//                     </a></p>";

//         try {
//             //Recipients
//             $mail->setFrom(FROM_MAIL_ADDRESS, FROM_MAIL_NAME);
//             $mail->addAddress($email);     //Add a recipient

//             //Content
//             $mail->isHTML(true);//Set email format to HTML                                  
//             $mail->Subject = $subject;
//             $mail->Body    = $body;

//             $mail->send();

//             $LOG->info("Royalty claim mail sent to ".$user->email);

//             // check user exists
//             $subject = "Royalty Claimed";
//             $body = "<p><b>".$claimer_name."</b> with user ID - ".$claimer_id." has claimed the royalty 
//                     amount of <b>".$royalty_amount." ".$price_type[$blockchain]."</b> for a quantity of 
//                     <b>".$quantity." ".$tier_map[$tier]."</b> tier tokens that belongs to 
//                     <b>".$collection_name."</b> collection.
//                     Check your transaction
//                     <a href='".$explorer_link[$blockchain]."/".$tx_hash."'>
//                     here
//                     </a></p>";

//             $mail->setFrom(FROM_MAIL_ADDRESS, FROM_MAIL_NAME);
//             $mail->addAddress(ADMIN_EMAIL);     //Add a recipient

//             //Content
//             $mail->isHTML(true);//Set email format to HTML                                  
//             $mail->Subject = $subject;
//             $mail->Body    = $body;

//             $mail->send();

//             $LOG->info("Royalty claim mail sent to ADMIN");
            
//             // set response code
//             http_response_code(200);
        
//             echo json_encode(array(
//                 "message" => _("Royalty Claim email has been successfully sent"),
//                 "success" => true
//             ));

//         } catch (Exception $e) {
//             $LOG->error(''.$e->getMessage());

//             // set response code
//             http_response_code(200);
            
//             echo json_encode(array(
//                 "message" => _("Unable to send royalty claim email"),
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

    // set product property values for verify_user

    // check user exists
    $subject = _("Royalty Claim Successfull");
    $body = "<p>"._("Hi")." ".$claimer_name.",</p><p>"._("You have successfully claimed the royalty amount of")." 
                <b>".$royalty_amount." ".$price_type[$blockchain]."</b> "._("for a quantity of")." 
                <b>".$quantity." ".$tier_map[$tier]."</b> "._("tier tokens that belongs to")." 
                <b>".$collection_name."</b> collection.
                "._("Check your transaction")."
                <a href='".$explorer_link[$blockchain]."/".$tx_hash."'>
                "._("here.")."
                </a></p><p>"._("Regards,")."</p><p>"._("Musician Team")."</p>";

    try {
        //Recipients
        $mail->setFrom(FROM_MAIL_ADDRESS, FROM_MAIL_NAME);
        $mail->addAddress($email);     //Add a recipient

        //Content
        $mail->isHTML(true);//Set email format to HTML                                  
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();

        $LOG->info("Royalty claim mail sent to ".$user->email);

        // check user exists
        $subject = _("Royalty Claimed");
        $body = "<p>"._("Hi,")."</p><p><b>".$claimer_name."</b> "._("with user ID")." - ".$claimer_id." "._("has claimed the royalty amount of")." 
                <b>".$royalty_amount." ".$price_type[$blockchain]."</b> "._("for a quantity of")." 
                <b>".$quantity." ".$tier_map[$tier]."</b> "._("tier tokens that belongs to")." 
                <b>".$collection_name."</b> "._("collection").".
                "._("Check the transaction")."
                <a href='".$explorer_link[$blockchain]."/".$tx_hash."'>
                "._("here.")."
                </a></p><p>"._("By,")."</p><p>"._("Musician Mail Server")."</p>";

        $mail->setFrom(FROM_MAIL_ADDRESS, FROM_MAIL_NAME);
        $mail->addAddress(ADMIN_EMAIL);     //Add a recipient

        //Content
        $mail->isHTML(true);//Set email format to HTML                                  
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();

        $LOG->info("Royalty claim mail sent to ADMIN");
        
        // set response code
        http_response_code(200);
    
        echo json_encode(array(
            "message" => _("Royalty Claim email has been successfully sent"),
            "success" => true
        ));

    } catch (Exception $e) {
        $LOG->error(''.$e->getMessage());

        // set response code
        http_response_code(200);
        
        echo json_encode(array(
            "message" => _("Unable to send royalty claim email"),
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