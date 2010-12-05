<?php

$key = get('method');

// Handle the successful response of the question

if(get('record') || get('hadRecording') || (($responseValue = tropoInputValue()) !== FALSE))
{
	if(get('record') == 1)
	{
		// Handle uploaded recordings
		$filename = md5(microtime(TRUE)) . '.mp3';
		$fullpath = RECORDING_PATH . $filename;
		move_uploaded_file($_FILES['filename']['tmp_name'], $fullpath);

		$_SESSION['recordings'][$key] = $filename;

		// Attempt to update the record in the DB
		$query = db()->prepare('UPDATE `responses` SET `recording` = :recording WHERE `callID` = :callID AND `key` = :key');
		$query->bindValue(':callID', $_SESSION['callID']);
		$query->bindValue(':key', $key);
		$query->bindValue(':recording', $filename);
		$query->execute();

		// If no rows were updated, that means there wasn't already a row for this key, so insert it now
		if($query->rowCount() == 0)
		{
			$query = db()->prepare('INSERT INTO `responses` (`callID`, `key`, `recording`) VALUES(:callID, :key, :recording)');
			$query->bindValue(':callID', $_SESSION['callID']);
			$query->bindValue(':key', $key);
			$query->bindValue(':recording', $filename);
			$query->execute();
		}

		die();
	}
	elseif(isset($responseValue))
	{
		storeSurveyResponse($key, $responseValue);
	}

	// After processing the input, include the questions file so logic can happen in it based on the session values set above
	include('survey.php');

	// Find the next question and ask it
	foreach($questions as $i=>$q)
	{
		if($q['key'] == $key)
		{
			$thisQuestion = $q;
			if(array_key_exists($i+1, $questions))
				$nextQuestion = $questions[$i+1];
			else
				$nextQuestion = FALSE;
		}
	}

	// If we got a valid response from the user, continue, otherwise ask them the same question again
	if(get('hadRecording') || isset($responseValue))
	{
		if($nextQuestion)
			askQuestion($nextQuestion);
		else
			complete();
	}
	else
	{
		askQuestion($thisQuestion, TRUE);
	}
}
else
{
	$tropo[] = array(
		'say' => array('value'=>'Sorry, there was an error. Goodbye.'),
		'voice' => $voice
	);
}



function getQuestionName($key)
{
	global $questions;
	
	foreach($questions as $q)
		if($q['key'] == $key)
			return $q['name'];
	else
		return FALSE;
}


function complete()
{
	global $tropo, $voice;
	
	$tropo[] = array(
		'say' => array('value'=>'The survey has been completed. Thanks.'),
		'voice' => $voice
	);
	
	$query = db()->prepare('UPDATE `calls` SET `dateFinished` = :date WHERE `sessionID` :id');
	$query->bindParam(':id', session_id());
	$query->bindParam(':date', date('Y-m-d H:i:s'));
	$query->execute();
}

?>