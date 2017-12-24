<?php
// File Validation
if($_FILES["upload_file"]["type"]!='text/csv'){
	echo "Please select valid file format.";
	exit;
}
// API Information
$token = "token";
$account_id = 1234567890;
$client_id = 123456;
$currency              = "USD";
$description           = "Service Description";


// Array for save data
$saveData = array();
$saveData[] = [
			'Card Number','Expiration Month','Expiration Month','CVV','Country','Postal Code','User Name', 'Email', 'Checkout Id', 'Status', 'Amount'
		];
//Open csv file
$handle = fopen($_FILES["upload_file"]["tmp_name"], "r");
//print_r($handle); exit;
//$data = fgetcsv($handle);

// Loop Start
while(($item = fgetcsv($handle)) !== FALSE){
// print_r($item);
// Create Credit Card Id
$curl = curl_init();

$email = $item[8];
$user_name = $item[6]." ".$item[7];
$cc_number = $item[0];
$cvv = $item[3];
$expiration_month = $item[1];
$expiration_year = $item[2];
$country = $item[4];
$postal_code = $item[5];
$amountPayable         = $item[9];


$jdata = [
	'client_id' => $client_id,
	'user_name' => $user_name,
	'email' => $email,
	'cc_number' => $cc_number,
	'cvv' => $cvv,
	'expiration_month' => $expiration_month,
	'expiration_year' => $expiration_year,
	'address' => [
		'country' => $country,
		'postal_code' => $postal_code,
	],
];
$jsonData = json_encode($jdata);
// print_r($jsonData); exit;

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://wepayapi.com/v2/credit_card/create",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $jsonData,
  CURLOPT_HTTPHEADER => array(
    "authorization: PRODUCTION_12d9167f273a60d9c2a4210acf467116f604474f8654fc1006d8cbc29402a6a7",
    "cache-control: no-cache",
    "content-type: application/json",
    "user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
	$data = json_decode($response, true);
	$cid = $data[credit_card_id];
	// echo "Credit Card Id: ";
 //    echo $cid = $data[credit_card_id];
 //    echo "<br/>";
}

// Process Payment
$pcurl = curl_init();

curl_setopt_array($pcurl, array(
  CURLOPT_URL => "https://wepayapi.com/v2/checkout/create",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "{\n\n  \"account_id\": $account_id,\n  \"amount\": $amountPayable,\n  \"type\": \"service\",\n  \"currency\": \"$currency\",\n  \"short_description\": \"test payment\",\n  \"long_description\": \"$description\",\n  \"payment_method\": {\n      \"type\": \"credit_card\",\n      \"credit_card\": {\n          \"id\": \"$cid\"\n       }\n   }\n}",
  CURLOPT_HTTPHEADER => array(
    "authorization: Bearer $token",
    "cache-control: no-cache",
    "content-type: application/json",
    "user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36"
  ),
));

$presponse = curl_exec($pcurl);
$perr = curl_error($pcurl);

curl_close($pcurl);

if ($perr) {
  echo "cURL Error #:" . $perr;
} else {
  $pdata = json_decode($presponse, true);
}

	if(!empty($pdata[error])){
		$saveData[] = [
			$cc_number, $expiration_month, $expiration_year, $cvv, $country, $postal_code, $user_name, $email, '', $pdata['error_description'], $amountPayable
		];
	 	// echo "Process Completed.";
	}else{
		$saveData[] = [
			$cc_number, $expiration_month, $expiration_year, $cvv, $country, $postal_code, $user_name, $email, $pdata['checkout_id'], $pdata['state'], $amountPayable
		];
		// echo "Process Completed.";
	}
}
fclose($handle);
$file = fopen('payment_status.csv', 'w');
foreach ($saveData as $row)
{
    fputcsv($file, $row);
}
fclose($file);

echo "Process Completed.";
?>
