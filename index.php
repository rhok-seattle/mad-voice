<?php
include('include/include.php');

$input = tropoInput();
/*
if(is_object($input))
{
	if(property_exists($input, 'session'))
	{
		$sessionID = $input->session->id;
		$_SESSION['session'] = $input->session;
		$_SESSION['callerID'] = (@$input->session->from->id ? $input->session->from->id : '');
		ircLog('Incoming call from ' . $_SESSION['callerID']);
	}
	else
	{
		$sessionID = $_SESSION['session']->id;
	}
}
else
	$sessionID = FALSE;
*/

$tropo = array();
$voice = 'allison';

switch(get('method'))
{
	case 'incoming':
	case 'firstName':
		include('app/' . get('method') . '.php');
		break;
	default: 
		include('app/question.php');
		break;
}


$json = json_encode(array('tropo'=>$tropo));
header('Content-type: text/plain');

if(get('test'))
{
	echo formatJSON($json);

	if(isset($_SESSION))
	{	
		echo "\n\nSession:\n";
		print_r($_SESSION);
	}
}
else
{
	echo $json;
}


filedebug($json);

?>