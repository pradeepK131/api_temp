<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

// files needed to connect to database
include_once '../config/database.php';
include_once '../controllers/music-category.php';

$data = json_decode(file_get_contents("php://input"), true);

include_once '../include/common.inc.php';

$database = new Database($LOG);
$db = $database->getConnection();

$category = new PatentCategory($db);

$category_list = $category->get_patent_categories();
    
http_response_code(200);

echo json_encode(array (
    "category" => $category_list,
    "success" => true
));

