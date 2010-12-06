<?php
include('include/include.php');

$input = tropoInput();

// Tropo seems to not send the cookie with the recorded file, so we pass it in a query string parameter
if(get('session_id'))
	session_id($_GET['session_id']);

session_start();

if(is_object($input))
{
	if(property_exists($input, 'session'))
	{
		$_SESSION['session'] = $input->session;
		$_SESSION['callerID'] = (@$input->session->from->id ? $input->session->from->id : '');
		ircdebug('Incoming call from ' . $_SESSION['callerID']);
	}
	else
	{
		$sessionID = $_SESSION['session']->id;
	}
}
else
	$sessionID = FALSE;


$tropo = array();
$voice = 'allison';

switch(get('method'))
{
	case '':
		include('view.php');
		die();
		break;
	case 'incoming':
		define('SURVEY_MODE', TRUE);
		include('survey.php');
		
		$query = db()->prepare('INSERT INTO `calls` (`date`, `callerID`, `sessionID`) VALUES(:date, :callerID, :sessionID)');
		$query->bindValue(':date', date('Y-m-d H:i:s'));
		$query->bindValue(':callerID', $_SESSION['callerID']);
		$query->bindValue(':sessionID', session_id());
		$query->execute();
		$_SESSION['callID'] = db()->lastInsertId();

		$tropo[] = array(
			'say' => array(
				'value' => '. . . ' . $firstPrompt,
			),
			'voice' => $voice
		);
		
		askQuestion(array_shift($questions));

		break;
	case 'hangup':
		if(defined('REMOTE_UPLOAD_SERVER') && REMOTE_UPLOAD_SERVER)
			sendCallToServer($_SESSION['callID'], REMOTE_UPLOAD_SERVER);
		ircdebug('Call completed');
		break;
	default: 
		define('SURVEY_MODE', TRUE);
		include('include/question.php');
		break;
}


$json = json_encode(array('tropo'=>$tropo));
header('Content-type: text/plain');

if(get('test'))
{
	echo formatJSON($json);
}
else
{
	echo $json;
}
/*
if(isset($_SESSION))
{	
	filedebug("\n\nSession:\n");
	filedebug($_SESSION);
}
*/

filedebug($json);

?>