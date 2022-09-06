<?php 
// required headers
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

include_once '../include/common.inc.php';
require '../config/database.php';
require '../controllers/user.php';

// get posted data
$data = json_decode(file_get_contents("php://input"), true);

// get database connection
$database = new Database($LOG);
$db = $database->getConnection();

$user = new User($db, $LOG);
$user->token = $data["token"];

$password = $data["password"];
$cpassword = $data["cpassword"];

$user->password = $password;
$record_exists = $user->verify_tokn();
if($record_exists){
	$time = $user->timestamp;
	$time_stamp = strtotime($time);
	$current_time = date('Y-m-d h:i:s');
	$current_time = strtotime($current_time);
	if($current_time - $time_stamp < 180){
		$user->timestamp = date('Y-m-d h:i:s');
		$update_password = $user->update_password();
		if($update_password){
			$LOG->info("Password reset made for ".$user->email);

			// set response code
			http_response_code(200);
 
			// display message: password changed
			echo json_encode(array(
				"success" => true,
				"message" => _("Your password has been successfully changed")
			));
		} else {
			$LOG->error("Password reset failed for ".$user->email);

			// set response code
			http_response_code(200);
 
			// display message: password change failed
			echo json_encode(array(
				"success" => false,
				"message" => _("Your password reset request failed"),
			));
		}
	} else {
		$LOG->error("Password reset failed for ".$user->email.". Link expired.");
		// set response code
		http_response_code(200);
 
		// display message: password reset link expired
		echo json_encode(array(
			"success" => false,
			"message" => _("Your password reset link was expired"),
		));
	}
} else {
	$LOG->error("Password reset failed. Invalid token.");
	// set response code
	http_response_code(200);
 
	// display message: if email not exists in database
	echo json_encode(array(
		"success" => false,
		"message" => _("Invalid credentials"),
	));
}

?>
