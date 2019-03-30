#!/usr/bin/php
<?php
ini_set('log_errors',1);
ini_set('error_log', dirname(__FILE__) . '/490rabbitmq.log');
error_reporting(E_ALL);

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function loginAuth($usrName, $usrPassword) {
	//echo "loginAuth method run";
	$db = mysqli_connect("127.0.0.1", "admin", "password", "490db");

	if  (!$db) {
	     die("MySQL Connection Failed: " . mysqli_connect_error() );
	} else {
	     $authUser = "SELECT * FROM login WHERE username='$usrName' AND password='$usrPassword'";
             $confirmAuth = mysqli_query($db, $authUser);
             if (mysqli_num_rows($confirmAuth) >= 1) {
                 echo "\nusername and password found in table\n";
                 return true;
             } else {
                 echo "\nusername and password NOT found in table\n";
                 return false;
             }
	}
}


function fetchData($usrName, $sqlStatement) {
        $db = mysqli_connect("127.0.0.1", "admin", "password", "490db");
        $runQuery = mysqli_query($db, $sqlStatement);

        if  (!$db) {
             die("MySQL Connection Failed: " . mysqli_connect_error() );
        } else {
             $row = mysqli_fetch_assoc($runQuery);
	     print_r($row);
             return $row;
	     //print_r($new_array);
             //print_r($arrayToString);
	     //$secondArray = $new_array['0'];
	     //print_r($secondArray);	     
	     //$arrayToString = implode(',',$new_array);             	     
	     //print_r($arrayToString);
             //return $arrayToString;
             //return $secondArray;
	     mysqli_close();
  	}
}

function registerAuth($usrName,$usrPassword,$usrEmail) {
	$db = mysqli_connect("127.0.0.1", "admin", "password", "490db");
        if  (!$db) {
             die("MySQL Connection Failed: " . mysqli_connect_error() );
        } else {
             $searchUser = "SELECT * FROM login WHERE username='$usrName'";
	     $searchEmail = "SELECT * FROM login WHERE email='$usrEmail'";
             $checkUserExist = mysqli_query($db, $searchUser);
             $checkEmailExist = mysqli_query($db, $searchEmail);
	     if (mysqli_num_rows($checkUserExist) >= 1) {
	          echo "\nusername already exists\n";
                  return false;
             } elseif (mysqli_num_rows($checkEmailExist) >= 1 ) {
                  echo "\nemail already exists\n";
		  return false;
             } else {
                mysqli_query($db, "INSERT INTO login(username, password, email) VALUES('$usrName', '$usrPassword', '$usrEmail')");
		$getUserID = mysqli_query($db, "SELECT id FROM login WHERE username='$usrName'");
		//print_r($getUserID);
		$userID = mysqli_fetch_assoc($getUserID);
		//print_r($userID);
		$resultUserID = (int)$userID['id'];
		//print_r(gettype($resultUserID));
		mysqli_query($db, "INSERT INTO wallet(id, balance) VALUES('$resultUserID', 100)");
		echo "\naccount created\n";
                return true;
	     }
       }
}


function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "login":
      //return doLogin($request['username'],$request['password']);
      return loginAuth($request['usrName'],$request['usrPassword']);
    case "register":
      return registerAuth($request['usrName'],$request['usrPassword'],$request['usrEmail']);
    case "fetch":
      return fetchData($request['usrName'], $request['sqlStatement']);
    //case "validate_session":
      //return doValidate($request['sessionId']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

