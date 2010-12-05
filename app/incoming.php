<?php

session_start();

include('include/questions.php');

$tropo[] = array(
	'say' => array(
		'value' => '. . . Thanks for calling the Mobile Assessment of Damage hotline.',
	),
	'voice' => $voice
);

askQuestion(array_shift($questions));

?>