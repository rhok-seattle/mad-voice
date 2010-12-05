<?php

$key = get('method');


// Handle the successful response of the question

if(get('record') || get('hadRecording') || ($val=tropoInputValue()) !== FALSE)
{
	if(get('record') == 1)
	{
		// Tropo seems to not send the cookie with the recorded file
		session_name($_GET['session_id']);
		session_start();
		// Handle uploaded recordings
		$filename = '/web/sites/loqi.me/www/madpub/recordings/' . md5(microtime(TRUE)) . '.wav';
		move_uploaded_file($_FILES['filename']['tmp_name'], $filename);
		$_SESSION['recordings'][$key] = $filename;
		die();
	}
	elseif(isset($val))
	{
		session_start();
		$_SESSION['responses'][$key] = $val;
	}
	else
	{
		session_start();
	}

	// After processing the input, include the questions file so logic can happen in it based on the session values set above
	include('include/questions.php');

	// Find the next question and ask it
	foreach($questions as $i=>$q)
		if($q['key'] == $key)
			if(array_key_exists($i+1, $questions))
				askQuestion($questions[$i+1]);
			else
				complete();
}
else
{
	$tropo[] = array(
		'say' => array('value'=>'Sorry, there was an error. Goodbye.'),
		'voice' => $voice
	);
}


function complete()
{
	global $tropo, $voice;
	
	$tropo[] = array(
		'say' => array('value'=>'The survey has been completed. Thanks.'),
		'voice' => $voice
	);
}

?>