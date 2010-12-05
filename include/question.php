<?php

$key = get('method');

ircdebug($key);

// Handle the successful response of the question

if(get('record') || get('hadRecording') || ($val=tropoInputValue()) !== FALSE)
{
	if(get('record') == 1)
	{
		// Handle uploaded recordings
		$filename = md5(microtime(TRUE)) . '.wav';
		$fullPath = RECORDING_PATH . $filename;
		move_uploaded_file($_FILES['filename']['tmp_name'], $filename);
		$_SESSION['recordings'][$key] = $filename;

		// Attempt to update the record in the DB
		$query = db()->prepare('UPDATE `responses` SET `recording` = :recording WHERE `callID` = :callID AND `name` = :name');
		$query->bindValue(':callID', $_SESSION['callID']);
		$query->bindValue(':name', $key);
		$query->bindValue(':recording', $filename);
		$query->execute();

		// If no rows were updated, that means there wasn't already a row for this key, so insert it now
		if($query->rowCount() == 0)
		{
			$query = db()->prepare('INSERT INTO `responses` (`callID`, `name`, `recording`) VALUES(:callID, :name, :recording)');
			$query->bindValue(':callID', $_SESSION['callID']);
			$query->bindValue(':name', $key);
			$query->bindValue(':recording', $filename);
			$query->execute();
		}

		die();
	}
	elseif(isset($val))
	{
		$_SESSION['responses'][$key] = $val;
		
		// Attempt to update the record in the DB
		$query = db()->prepare('UPDATE `responses` SET `value` = :value WHERE `callID` = :callID AND `name` = :name');
		$query->bindValue(':callID', $_SESSION['callID']);
		$query->bindValue(':name', $key);
		$query->bindValue(':value', $val);
		$query->execute();

		// If no rows were updated, that means there wasn't already a row for this key, so insert it now
		if($query->rowCount() == 0)
		{
			$query = db()->prepare('INSERT INTO `responses` (`callID`, `name`, `value`) VALUES(:callID, :name, :value)');
			$query->bindValue(':callID', $_SESSION['callID']);
			$query->bindValue(':name', $key);
			$query->bindValue(':value', $val);
			$query->execute();
		}
	}

	// After processing the input, include the questions file so logic can happen in it based on the session values set above
	include('survey.php');

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