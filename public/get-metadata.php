<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require '../config/database.php';
require '../controllers/collection-meta.php';
require '../controllers/token-balances.php';
require '../controllers/tiers-meta.php';
require '../controllers/artist.php';
include_once '../include/common.inc.php';
require (MAIN_PATH.'vendor/autoload.php');

$database = new Database($LOG);
$db = $database->getConnection();

$collection_meta = new CollectionMeta($db);
$token_balances = new TokenBalances($db);
$tiers_meta = new TiersMeta($db);
$artist = new Artists($db);

use GraphQL\Query;

$token_id = $_GET['token_id'];

$gql = (new Query('tokenBalance'))
    ->setArguments(['id' => $token_id])
    ->setSelectionSet(
        [
            'tokenId',
            (new Query('collection'))
                ->setSelectionSet(
                    [
                        'id'
                    ]

                ),
            (new Query('tier'))
                ->setSelectionSet(
                    [
                        'tier'
                    ]
                )
        ]
    );

// Run query to get results
try {
    $results = $client->runRawQuery($gql);
}
catch (QueryError $exception) {

    // Catch query error and desplay error details
    print_r($exception->getErrorDetails());
    exit;
}

$tiers_abb = array(
  '0' => 'GOLD',
  '1' => 'PLATINUM',
  '2' => 'DIAMOND'
);

$collection_type_abbr = array(
  'single' => 'Single',
  'single-remix' => 'Single & Remix',
  'ep' => 'EP',
  'album' => 'Album'
);

$results->reformatResults(true);
$eth_token_data = $results->getData()['tokenBalance'];
$klay_token_data = $token_balances->get_collection_id_and_tier($token_id);

print_r($results);

if(isset($eth_token_data)) {
  $collection_id = $eth_token_data['collection']['id'];
  $tier = $eth_token_data['tier']['tier'];

  $collection_tier_length = strlen($collection_id . $tier);
  $split_token_id = str_split($token_id);

  $new_split = array_slice($split_token_id, $collection_tier_length);
  $backward_token_num = implode($new_split);

  $tier_data = $tiers_meta->getTierData($collection_id, $tier);

  $tier_quantity = $tier_data['tier_quantity'];
  $royalty_share = $tier_data['ownership_offered'] / 10 ** 5;

  $token_count = ($tier_quantity - $backward_token_num) + 1;

  $collection_data = $collection_meta->getCollectionDataById($collection_id);

  $collection_name = $collection_data['collection_name'];
  $artist_name = $artist->get_artist_name($collection_data['artist_id']);

  $name = "#".$token_count." ".$collection_name."";
  $image = 'http://localhost/musician-api/uploads/tierImages/' . $tier_data['tier_image'];

  echo json_encode(array(
    'name' => $name,
    'image' => $image,
    'description' => "[".$token_count."/".$tier_quantity."]" . " of " . $collection_name,
    'attributes' => array(
      array(
        'trait_type' => 'Tier',
        'value' => $tiers_abb[$tier_data['tier']]
      ),
      array(
        'trait_type' => 'Edition',
        'value' => $collection_name
      ),
      array(
        'trait_type' => 'AssetType',
        'value' => $collection_type_abbr[$collection_data['collection_type']]
      ),
      array(
        'trait_type' => $collection_type_abbr[$collection_data['collection_type']],
        'value' => $collection_name
      ),
      array(
        'display_type'=> 'number',
        'trait_type' => 'Token Number',
        'value' => $token_count
      ),
      array(
        'trait_type' => 'Artist',
        'value' => $artist_name
      ),
      array(
        'trait_type' => 'Royalty Share',
        'value' => $royalty_share . '%'
      )
    )
  ));
  
} else if(isset($klay_token_data)) {
  
  $collection_id = $klay_token_data['collection_id'];
  $tier = $klay_token_data['tier'];

  $collection_tier_length = strlen($collection_id . $tier);
  $split_token_id = str_split($token_id);

  $new_split = array_slice($split_token_id, $collection_tier_length);
  $backward_token_num = implode($new_split);

  $tier_data = $tiers_meta->getTierData($collection_id, $tier);

  $tier_quantity = $tier_data['tier_quantity'];
  $royalty_share = $tier_data['ownership_offered'] / 10 ** 5;

  $token_count = ($tier_quantity - $backward_token_num) + 1;

  $collection_data = $collection_meta->getCollectionDataById($collection_id);

  $collection_name = $collection_data['collection_name'];
  $artist_name = $artist->get_artist_name($collection_data['artist_id']);

  $name = "#".$token_count." ".$collection_name."";
  $image = 'http://localhost/musician-api/uploads/tierImages/' . $tier_data['tier_image'];

  echo json_encode(array(
    'name' => $name,
    'image' => $image,
    'description' => "[".$token_count."/".$tier_quantity."]" . " of " . $collection_name,
    'attributes' => array(
      array(
        'trait_type' => 'Tier',
        'value' => $tiers_abb[$tier_data['tier']]
      ),
      array(
        'trait_type' => 'Edition',
        'value' => $collection_name
      ),
      array(
        'trait_type' => 'AssetType',
        'value' => $collection_type_abbr[$collection_data['collection_type']]
      ),
      array(
        'trait_type' => $collection_type_abbr[$collection_data['collection_type']],
        'value' => $collection_name
      ),
      array(
        'display_type'=> 'number',
        'trait_type' => 'Token Number',
        'value' => $token_count
      ),
      array(
        'trait_type' => 'Artist',
        'value' => $artist_name
      ),
      array(
        'trait_type' => 'Royalty Share',
        'value' => $royalty_share . '%'
      )
    )
  ));
} else {
  echo json_encode(array(
    'data' => 'query for non-existent token id'
  ));
}
