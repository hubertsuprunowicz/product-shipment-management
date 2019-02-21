<?php
require_once __DIR__ . '/vendor/autoload.php';


define('APPLICATION_NAME', 'Gmail API PHP Quickstart');
define('CREDENTIALS_PATH', '~/.credentials/gmail-php-quickstart.json'); //TODO: add to gitignore
define('CLIENT_SECRET_PATH', __DIR__ . '/Credentials/credentials.json'); //TODO: add to gitignore
define('SCOPES', implode(' ', array(
		Google_Service_Gmail::GMAIL_READONLY)
));


if (php_sapi_name() != 'cli') {
	throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
	$client = new Google_Client();
	$client->setApplicationName(APPLICATION_NAME);
	$client->setScopes(SCOPES);
	$client->setAuthConfig(CLIENT_SECRET_PATH);
	$client->setAccessType('offline');

	// Load previously authorized credentials from a file.
	$credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
	if (file_exists($credentialsPath)) {
		$accessToken = json_decode(file_get_contents($credentialsPath), true);
	} else {
		// Request authorization from the user.
		$authUrl = $client->createAuthUrl();
		printf("Open the following link in your browser:\n%s\n", $authUrl);
		print 'Enter verification code: ';
		$authCode = trim(fgets(STDIN));

		// Exchange authorization code for an access token.
		$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

		// Store the credentials to disk.
		if(!file_exists(dirname($credentialsPath))) {
			mkdir(dirname($credentialsPath), 0700, true);
		}
		file_put_contents($credentialsPath, json_encode($accessToken));
		printf("Credentials saved to %s\n", $credentialsPath);
	}
	$client->setAccessToken($accessToken);

	// Refresh the token if it's expired.
	if ($client->isAccessTokenExpired()) {
		$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
		file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
	}
	return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
	$homeDirectory = getenv('HOME');
	if (empty($homeDirectory)) {
		$homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
	}
	return str_replace('~', realpath($homeDirectory), $path);
}

/**
 * Get list of Messages in user's mailbox.
 *
 * @param  Google_Service_Gmail $service Authorized Gmail API instance.
 * @param  string $userId User's email address. The special value 'me'
 * can be used to indicate the authenticated user.
 * @return array Array of Messages.
 */
function listMessages($service, $userId, $optArr = []) {
	$pageToken = NULL;
	$messages = array();
		try {
			if ($pageToken) {
				$optArr['pageToken'] = $pageToken;
			}
			$messagesResponse = $service->users_messages->listUsersMessages($userId, $optArr);
			if ($messagesResponse->getMessages()) {
				$messages = array_merge($messages, $messagesResponse->getMessages());
				$pageToken = $messagesResponse->getNextPageToken();
			}
		} catch (Exception $e) {
			print 'An error occurred: ' . $e->getMessage();
		}

	return $messages;
}

function getHeaderArr($dataArr) {
	$outArr = [];
	foreach ($dataArr as $key => $val) {
		$outArr[$val->name] = $val->value;
	}
	return $outArr;
}

function getBody($dataArr) {
	$outArr = [];
	foreach ($dataArr as $key => $val) {
		$outArr[] = base64url_decode($val->getBody()->getData());
		break;
	}

	return $outArr;
}

function base64url_decode($data) {
	return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

function getMessage($service, $userId, $messageId) {
	try {
		$message = $service->users_messages->get($userId, $messageId);
//		print 'Message with ID: ' . $message->getId() . ' retrieved.' . "\n";
		return $message;
	} catch (Exception $e) {
		print 'An error occurred: ' . $e->getMessage();
	}
}

function listLabels($service, $userId, $optArr = []) {

	$results = $service->users_labels->listUsersLabels($userId, $optArr);

	if (count($results->getLabels()) == 0) {
		print "No labels found.\n";
	} else {
		print "Labels:\n";
		foreach ($results->getLabels() as $label) {
			printf("- %s\n", $label->getName());
		}
	}
}

function getPayments($numberOfResults) {


	// Get the API client and construct the service object.
	$client = getClient();
	$service = new Google_Service_Gmail($client);
	$user = 'me';


	// Get the messages in the user's account.
	$messages = listMessages($service, $user, [
		'maxResults' => $numberOfResults,
		// labelIds is not the Label Name.
		'labelIds' => 'Label_7874112263175737202'
	]);

	// Filter returned records using RegEx
	$searchedMessages = [];
	foreach ($messages as $message) {
		$msgObj = getMessage($service, $user, $message->getId());
		if(preg_match('/nowa wp/',$msgObj->getSnippet())) {
			$searchedMessages[] = $message;
		}
	}

	$resultArr = [];
	foreach ($searchedMessages as $message) {
		$msgObj = getMessage($service, $user, $message->getId());
		$str = strip_tags(base64url_decode($msgObj->getPayload()->getBody()->getData()));
		$toChange = preg_replace('/\s{2,}/', '', $str);
		preg_match_all ('/przedmioty(.\w+.*)\(.*\)(\d.+sztuk[a-z]).*dostawy(.*)Dane.*przesyłki(.*)Numer/', (string)$toChange, $result);

		//UPDATE: needed date without changing array
		preg_match_all ('/przekazania wpłaty(.*)Razem/', (string)$toChange, $date);
		$m_en = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
		$m_pol = array("stycznia", "lutego", "marca", "kwietnia", "maja", "czerwca", "lipca", "września", "sierpnia", "października", "listopada", "grudnia");
		$date[1][0] = str_replace($m_pol, $m_en, $date[1][0]);
		$result[0][0] =  $date[1][0];

		// Add result to an array
		$resultArr[] = $result;
	}

	return $resultArr;
}

function cleanData($messageInfoArr, $key) : array {
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

	foreach ($messageInfoArr as $item) { array_unique($item); }

	return $messageInfoArr;
}

function saveJson($arg) {
	$fp = fopen('payments.json', 'w');
	fwrite($fp, json_encode($arg, JSON_UNESCAPED_UNICODE));
	fclose($fp);
}

$payments = getPayments(100);
$key = ['timestamp', 'productName', 'qty', 'letterInfo', 'address'];
$payments = cleanData($payments, $key);
saveJson($payments);












