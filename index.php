<?php
require_once __DIR__ . '/vendor/autoload.php';
include  __DIR__ . '/model/Sort.php';


function getJson($fileName) : array {
	$jsonFile = file_get_contents($fileName);
	return json_decode($jsonFile, true, 512, JSON_UNESCAPED_UNICODE);
}

function refreshPayments() : array {
	system("cmd /c E:/xampp/htdocs/ShipmentManagement/gmail.bat");
	return getJson('payments.json');
}


$payments = getJson('payments.json');

// Sorting:
if(isset($_GET['sortTimestamp'])) {
	$payments = AP_Array_SortByKey::sort_by_key($payments,'timestamp');
}
if(isset($_GET['sortTimestampR'])) {
	$payments = AP_Array_SortByKey::sort_by_key_reversed($payments,'timestamp');
}
if(isset($_GET['sortProductName'])) {
	$payments = AP_Array_SortByKey::sort_by_key($payments,'productName');
}
if(isset($_GET['sortProductNameR'])) {
	$payments = AP_Array_SortByKey::sort_by_key_reversed($payments,'productName');
}
if(isset($_GET['sortQty'])) {
	$payments = AP_Array_SortByKey::sort_by_key($payments,'qty');
}
if(isset($_GET['sortQtyR'])) {
	$payments = AP_Array_SortByKey::sort_by_key_reversed($payments,'qty');
}

// Others:
if(isset($_GET['refresh']) ) {
	$payments = refreshPayments();
}


// Render TWIG
$loader = new Twig_Loader_Filesystem('views');
$twig = new Twig_Environment($loader);
echo $twig->render('index.html', [
	"payments" => $payments,
	"paymentLength" => count($payments)
]);


