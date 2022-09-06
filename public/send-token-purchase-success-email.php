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
$email = $data["email"];
$collection_name = $data["collectionName"];
$tier = $data["tier"];
$price = $data["price"];
$blockchain = $data["blockchain"];
$tx_hash = $data["txHash"];
$buyer_name = $data["buyerName"];
$buyer_id = $data["buyerID"];

// $email = "joypradeep7@gmail.com";
// $collection_name = "The Hooddie";
// $tier = "1";
// $price = "0.2";
// $blockchain = "ethereum";
// $tx_hash = "0xkkdk";
// $buyer_name = "User123";
// $buyer_id = "1";

$tier_map = array(
    "0" => "Gold",
    "1" => "Platinum",
    "2" => "Diamond"
);

$explorer_link = array(
    "ethereum" => "https://rinkeby.etherscan.io/tx",
    "klaytn" => "https://baobab.scope.klaytn.com/tx"
);

$price_type = array(
    "ethereum" => "ETH",
    "klaytn" => "KLAY"
);


// generate json web token
use \Firebase\JWT\JWT;

// get jwt
$jwt=isset($data["authToken"]) ? $data["authToken"] : "";

// // if jwt is not empty
// if($jwt){
 
//     // if decode succeed,
//     try {
//         // decode jwt
//         $decoded = JWT::decode($jwt, $key, array('HS256'));
 
//         // set product property values for verify_user

//         // check user exists
//         $subject = "Purchase Successfull";
//         $body = "<p>You have successfully purchased 1 quantity of 
//                 ".$tier_map[$tier]." tier token belongs to ".$collection_name." collection for the 
//                 price of ".$price." ".$blockchain == 'ethereum' ? 'ETH' : 'KLAY'."
//                 Check your transaction
//                 <a href=".$explorer_link[$blockchain]."/".$tx_hash.">
//                 here
//                 </a></p>";

//         try {
//             //Recipients
//             $mail->setFrom(FROM_MAIL_ADDRESS, FROM_MAIL_NAME);
//             $mail->addAddress($email);     //Add a recipient

//             //Content
//             $mail->isHTML(true);//Set email format to HTML                                  
//             $mail->Subject = $subject;
//             $mail->Body    = $body;

//             $mail->send();

//             $LOG->info("Token purchase mail sent to ".$user->email);

//             $subject = "Purchase Notification";
//             $body = "<p>".$buyer_name." with user ID - ".$buyer_id." has successfully purchased 
//                         1 qunatity of ".$tier_map[$tier]." tier token belongs to 
//                         ".$collection_name." collection for the price of ".$price." 
//                         ".$blockchain == 'ethereum' ? 'ETH' : 'KLAY'."
//                     <a href=".$explorer_link[$blockchain]."/".$tx_hash.">
//                     here
//                     </a></p>";

//             $mail->setFrom(FROM_MAIL_ADDRESS, FROM_MAIL_NAME);
//             $mail->addAddress(ADMIN_EMAIL);     //Add a recipient

//             //Content
//             $mail->isHTML(true);//Set email format to HTML                                  
//             $mail->Subject = $subject;
//             $mail->Body    = $body;

//             $mail->send();

//             $LOG->info("Token purchase mail sent to ADMIN");
            
//             // set response code
//             http_response_code(200);
        
//             echo json_encode(array(
//                 "message" => _("Token purchase email has been successfully sent"),
//                 "success" => true
//             ));

//         } catch (Exception $e) {
//             $LOG->error(''.$e->getMessage());

//             // set response code
//             http_response_code(200);
            
//             echo json_encode(array(
//                 "message" => _("Unable to send email"),
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
    $subject = _("Purchase Successfull");
    $body = "<p>"._("Hi")." ".$buyer_name.",</p><p>"._("You have successfully purchased 1 quantity of")." <b>".$tier_map[$tier]."</b> "._("tier token belongs to")." 
                <b>".$collection_name."</b> "._("collection for the price of")." <b>".$price." ".$price_type[$blockchain]."</b>. 
                "._("Check your transaction")."
            <a href=".$explorer_link[$blockchain]."/".$tx_hash.">
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

        $LOG->info("Token purchase mail sent to ".$user->email);

        if(ADMIN_TOKEN_SALE_NOTIFICATION_SETTING == "All") {
            $subject = _("Purchase Notification");
            $body = "<p>"._("Hi,")."</p><p><b>".$buyer_name."</b> "._("with user ID")." - ".$buyer_id." "._("has successfully purchased")." 
                        "._("1 quantity of")." <b>".$tier_map[$tier]."</b> "._("tier token belongs to")." 
                        <b>".$collection_name."</b> "._("collection for the price of")." <b>".$price." 
                        ".$price_type[$blockchain]."</b>. "._("Check the transaction")."
                    <a href=".$explorer_link[$blockchain]."/".$tx_hash.">
                    "._("here").".
                    </a></p><p>"._("By,")."</p><p>"._("Musician Mail Server")."</p>";

            $mail->setFrom(FROM_MAIL_ADDRESS, FROM_MAIL_NAME);
            $mail->addAddress(ADMIN_EMAIL);     //Add a recipient

            //Content
            $mail->isHTML(true);//Set email format to HTML                                  
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();

            $LOG->info("Token purchase mail sent to ADMIN");
        }
        
        // set response code
        http_response_code(200);
    
        echo json_encode(array(
            "message" => _("Token purchase email has been successfully sent"),
            "success" => true
        ));

    } catch (Exception $e) {
        $LOG->error(''.$e->getMessage());

        // set response code
        http_response_code(200);
        
        echo json_encode(array(
            "message" => _("Unable to send email"),
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