<?php

//require __DIR__ . '../gmail.php';

namespace App;

function reduceArray($messageInfoArr, $key) : array {
	$tempArr = [];
	$index = 0;

	foreach ($messageInfoArr as $oneDim) {
		$keyIndex = 0;
		foreach ($oneDim as $twoDim) {
			foreach ($twoDim as $threeDim) {
				$tempArr[$index][$key[$keyIndex]] = $threeDim;
				$keyIndex++;
			}
		}
		$index++;
	} $messageInfoArr = $tempArr;

	return $messageInfoArr;
}



$payoutsArr = getPayouts(200);
$keyArray = ['timestamp', 'productName', 'qty', 'letterInfo', 'address'];


$payoutsArr = reduceArray($payoutsArr, $keyArray);

// Save to JSON
$fp = fopen('results.json', 'a');
fwrite($fp, json_encode($payoutsArr, JSON_UNESCAPED_UNICODE));
fclose($fp);


// Open JSON
$jsonFile = file_get_contents("results.json");
$jsonArr = json_decode($jsonFile, false, 512, JSON_UNESCAPED_UNICODE);







