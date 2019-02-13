<!DOCTYPE html>
<html>
	<body>
		<iframe src="https://calendar.google.com/calendar/embed?src=hrvt7kjiabpc162vunc3gkkfq4%40group.calendar.google.com&ctz=America%2FChicago" style="border: 0" width="800" height="600" frameborder="0" scrolling="no"></iframe>
		<?php
		  
		  // Google calendar
		  require __DIR__ . '/vendor/autoload.php';

			// if (php_sapi_name() != 'cli') {
				// throw new Exception('This application must be run on the command line.');
			// }

			/**
			 * Returns an authorized API client.
			 * @return Google_Client the authorized client object
			 */
			function getClient()
			{
				$client = new Google_Client();
				$client->setApplicationName('Google Calendar API PHP Quickstart');
				$client->setScopes(Google_Service_Calendar::CALENDAR_READONLY);
				$client->setAuthConfig('credentials.json');
				$client->setAccessType('offline');
				$client->setPrompt('select_account consent');

				// Load previously authorized token from a file, if it exists.
				// The file token.json stores the user's access and refresh tokens, and is
				// created automatically when the authorization flow completes for the first
				// time.
				$tokenPath = 'token.json';
				if (file_exists($tokenPath)) {
					$accessToken = json_decode(file_get_contents($tokenPath), true);
					$client->setAccessToken($accessToken);
				}

				// If there is no previous token or it's expired.
				if ($client->isAccessTokenExpired()) {
					// Refresh the token if possible, else fetch a new one.
					if ($client->getRefreshToken()) {
						$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
					} else {
						// Request authorization from the user.
						$authUrl = $client->createAuthUrl();
						printf("Open the following link in your browser:\n%s\n", $authUrl);
						print 'Enter verification code: ';
						$authCode = trim(fgets(STDIN));

						// Exchange authorization code for an access token.
						$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
						$client->setAccessToken($accessToken);

						// Check to see if there was an error.
						if (array_key_exists('error', $accessToken)) {
							throw new Exception(join(', ', $accessToken));
						}
					}
					// Save the token to a file.
					if (!file_exists(dirname($tokenPath))) {
						mkdir(dirname($tokenPath), 0700, true);
					}
					file_put_contents($tokenPath, json_encode($client->getAccessToken()));
				}
				return $client;
			}


			// Get the API client and construct the service object.
			$client = getClient();
			$service = new Google_Service_Calendar($client);

			// Print the next 10 events on the user's calendar.
			$calendarId = 'hrvt7kjiabpc162vunc3gkkfq4@group.calendar.google.com';
			// init date var to remove notice
			$date = date('c');
			$optParams = array(
			  'maxResults' => 10,
			  'orderBy' => 'startTime',
			  'singleEvents' => true,
			  'timeMin' => date('c', strtotime($date .' -1 day')),
			);
			$results = $service->events->listEvents($calendarId, $optParams);
			$events = $results->getItems();

			if (empty($events)) {
				$ddList = "No DDs today";
			} else {
				foreach ($events as $event) {
					$start = $event->start->dateTime;
					date_default_timezone_set('America/Chicago');
					$currentDate = date("y-m-d");
					if (empty($start)) {
						$start = $event->start->date;
					}
					if ((int)date("Hi") < 200) {
						$currentDate = date("y-m-");
						if (((int)date("d") - 1) < 10) {
							$currentDate .= "0";
							$currentDate .= (string)((int)date("d") - 1);
						}
						else {
							$currentDate .= (string)((int)date("d") - 1);
						}
					}
					if (strpos($start, $currentDate) !== false) {
						$ddList = $event->getDescription();
						$webText = "\nToday:\n" . $ddList;
						echo nl2br($webText);
					}
				}
			}
		  // End of Google calendar
		  
		  // SET THIS FILE'S URL AS YOUR BOT'S CALLBACK
		  
		  
		  require 'groupMeApi.php'; // functions for the bot to use
		  
		  /* **************************************************
			SETTINGS
			************************************************** */
		  $br = "\r\n"; // Useful shorthand when you need to have a line break in a bot message
		  $bMakeGeneralResponses = true; // Determine whether to respond at any mention of the bot, or just when specific requests are found
		  
		  /* **************************************************
			USER VARIABLES
			************************************************** */
		  $botId   = "44f1cfdcd4982d583f6a7554a0";
		  $groupMe = new groupMeApi();
		  
		  
		  /* **************************************************
			Get post data from the callback
			************************************************** */
		  $postdata = file_get_contents("php://input"); // Payload for bot callback will be viewable in php://input
		  $p = json_decode($postdata,true); // using TRUE makes it an associative array
		  
		  if (!($p)) {
			// No callback data, so we shouldn't do anything (either API callback error, or the page was loaded in a browser rather than used as callback URL
			exit();
			}
		  
		  /* **************************************************
			
			Format of callback data:
			
			//  {
			//    "id": "123456789012345678",
			//    "source_guid": "1234abcd5678efab9012cdef3456abcd",
			//    "created_at": 1420070400,
			//    "user_id": "12345678"
			//    "group_id": "1234567",
			//    "name": "NameString Here",
			//    "avatar_url": "https://i.groupme.com/1280x1280.jpeg.12345",
			//    "text": "hi",
			//    "system": false,
			//    "attachments": 
			//    [{
			//      "type":"image",
			//      "url":"https://i.groupme.com/460x574.jpeg.12345",
			//   }]
			// }
			
			************************************************** */
		  
		  // If it's blank (or only an attachment) no need to keep working to see if the bot needs to respond
		  if (strlen($p['text']) < 1) {
			exit();
			}
		  
		  // Store some of the variables for quick use
		  $userIdNumber = $p['user_id'];
		  $groupId = $p['group_id'];
		  $text = $p['text'];
		  
		  /* **************************************************
			
			Checks for string(s) in a bit of text
			
			$hay       [string]     text to search
			$arrNeedles [array]      array of values to look for
			
			returns     [true/false]   true if any of the needles match
			
			************************************************** */
		  function iStr($hay = false,$arrNeedles = false) {
			if ($hay && $arrNeedles) {
			  if (!is_array($arrNeedles)) { $arrNeedles = array($arrNeedles); }
			  foreach ($arrNeedles as $n) {  
				if (preg_match("/(".preg_quote($n,'/').")/i", $hay) === 1) {
				  return true;
						}
					}
				}
			return false;
			}
		  
		  
		  if (iStr($p['name'],array('DD PHP')) === false) { // This post wasn't made by the bot, so we'll continue (prevents infinite loop)
			
			if (iStr($text,array("!dd"))) {
			  // DD was asked for
			  $strResponse = $ddList;
				}
			
			if (iStr($text,array("!list"))) {
			  // List of dds was asked for
			  $groupMe->botPost($groupId,$botId,"https://calendar.google.com/calendar/embed?src=hrvt7kjiabpc162vunc3gkkfq4%40group.calendar.google.com&ctz=America%2FChicago");
				}
			  
			if (!empty($strResponse)) {
			  // Post the message
			  $groupMe->botPost($groupId,$botId,$strResponse); 
				}
				
			}
		  
		?> 
	</body>
</html> 