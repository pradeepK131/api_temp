<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../controllers/deposits.php';
include_once '../controllers/royalty-claims.php';
include_once '../controllers/token-balances.php';
include_once '../include/common.inc.php';

$data = json_decode(file_get_contents("php://input"), true);

$database = new Database($LOG);
$db = $database->getConnection();

$deposits = new Deposits($db);
$royalty_claims = new RoyaltyClaims($db);
$token_balances = new TokenBalances($db);

$wallet_address = $data['walletAddress'];
$collection_id = $data['collectionID'];
$tier = $data['tier'];
$ownership_offered = $data['ownershipOffered'];

// $wallet_address = '0xdD282C6c6EBBae45309Ca59964E6Ae42a8984B92';
// $collection_id = '25';
// $tier = '1';
// $ownership_offered = '10000';

$deposits_data = $deposits->get_deposits($collection_id);


function get_correct_percent($total_ownership_offered, $collectors_cut) {
    $coeff = ((10000 * 10**32) / ($total_ownership_offered));
    return ((($coeff * $collectors_cut * 10 **33) / 10**5) / 10**32);
}

function get_collectors_cut($deposited_amount, $total_ownership_offered, $collectors_cut) {
    $percent = get_correct_percent($total_ownership_offered, $collectors_cut);

    return (($percent * $deposited_amount) / 10**32);
}

function get_token_balance_ids($collection_id, $tier, $wallet_address, $token_balances) {
    $token_data = $token_balances->get_token_balances_by_user_wallet($collection_id, $tier, $wallet_address);
    $token_ids = [];

    if(isset($token_data)) {
        foreach ($token_data as $key => $value) {
            array_push($token_ids, $value['token_id']);
        }
    }

    return $token_ids;
}

function calculate_royalty_amount(
    $deposited_amount, 
    $deposit_number, 
    $tokenIds, 
    $ownership_offered, 
    $total_ownership_offered,
    $royalty_claims
) {
    $withdraw_amount = 0;
    $unclaimedIds = [];

    foreach ($tokenIds as $key => $token_id) {
        $claim_data = $royalty_claims->get_royalty_claims($token_id, $deposit_number);

        if(isset($claim_data) && isset($claim_data['claimed_amount']) && $claim_data['claimed_amount'] > 0) {
            continue;
        } else {
            $withdraw_amount += get_collectors_cut($deposited_amount, $total_ownership_offered, $ownership_offered);
            array_push($unclaimedIds, $token_id);
        }
    }
    return array(
        'withdraw_amount' => round($withdraw_amount),
        'unclaimedIds' => $unclaimedIds
    );
}


$accountBalanceData = [];


if(isset($deposits_data)) {
    foreach ($deposits_data as $key => $deposit_data) {
        $temp = array();
        $temp['depositNumber'] = $deposit_data['deposit_number'];
    
        $royalty_claim_data = calculate_royalty_amount(
            $deposit_data['deposit_amount'],
            $deposit_data['deposit_number'],
            get_token_balance_ids($collection_id, $tier, $wallet_address, $token_balances),
            $ownership_offered,
            $deposit_data['total_ownership_offered'],
            $royalty_claims
        );
        $temp['withdrawAmount'] = $royalty_claim_data['withdraw_amount'];
        $temp['unclaimedIds'] = $royalty_claim_data['unclaimedIds'];
    
        array_push($accountBalanceData, $temp);
    }
}

echo json_encode(array (
    "message" => _("Account Balance fetched successfully"),
    "success" => true,
    "accountBalanceData" => $accountBalanceData,
    "tokenIds" => get_token_balance_ids($collection_id, $tier, $wallet_address, $token_balances),
));

