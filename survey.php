<?php

$firstPrompt = 'Thanks for calling the Mobile Assessment of Damage hotline.';
/*
$questions[] = array(
	'key' => 'name',
	'name' => 'Name',
	'prompt' => 'What is your full name?',
	'prompt2' => didntUnderstand() . 'What is your name?',
	'choices' => '[RECORD]'
);
*/

$questions[] = array(
	'key' => 'postcode',
	'name' => 'Zip Code',
	'prompt' => 'What is the zip code of the property?',
	'prompt2' => didntUnderstand() . 'Speak each digit of the zip code slowly.',
	'choices' => '[5 DIGITS]'
);

if(defined('SURVEY_MODE') && ($zip = surveyVal('postcode')) !== FALSE)
{
	// Look up the counties for this zip code
	$counties = getCountiesForZipcode($zip);
	
	if(count($counties) > 1)
	{
		$countyChoices = array();
		foreach($counties as $fips=>$c)
			$countyChoices[] = $fips . '(' . $c . ')';
	
		$questions[] = array(
			'key' => 'county',
			'name' => 'County',
			'prompt' => 'Which county is the property in?',
			'prompt2' => didntUnderstand() . 'What county is the property in?',
			'choices' => implode(', ', $countyChoices)
		);
	}
	else
	{
		storeSurveyResponse('county', array_pop($counties));
	}
}
else
{
	// define the key/name pair for the web view, and optionally a way to look up foreign keys in the database
	$questions[] = array(
		'key' => 'county', 
		'name' => 'County',
		'lookup' => array('table'=>'counties', 'key'=>'fips', 'value'=>'countyName')
	);
}

$questions[] = array(
	'key' => 'insurance',
	'name' => 'Has Insurance',
	'prompt' => 'Do you have insurance?',
	'prompt2' => didntUnderstand() . 'Do you have insurance? Yes or no.',
	'choices' => '[BOOLEAN]'
);

if(surveyVal('insurance'))
{
	$questions[] = array(
		'key' => 'provider',
		'name' => 'Insurance Provider',
		'prompt' => 'Who is your insurance provider?',
		'prompt2' => didntUnderstand() . 'Who is your provides your insurance?',
		'choices' => '[RECORD]'
	);
}


$questions[] = array(
	'key' => 'owner',
	'name' => 'Owner?',
	'prompt' => 'Are you an owner or a renter?',
	'prompt2' => didntUnderstand() . 'Do you own or rent?',
	'choices' => '1(owner, own, 1, yes), 0(renter, rent, 2)'
);
$questions[] = array(
	'key' => 'eststructloss',
	'name' => 'Est. Structural Damage',
	'prompt' => 'What is the estimated dollar amount of the structural loss?',
	'prompt2' => didntUnderstand() . 'Say the dollar amount slowly',
	'choices' => '[CURRENCY]'
);
$questions[] = array(
	'key' => 'estperproploss',
	'name' => 'Est. Personal Loss',
	'prompt' => 'What is the estimated dollar amount of the personal property loss?',
	'prompt2' => didntUnderstand() . 'Please say the dollar amount slowly',
	'choices' => '[CURRENCY]'
);





function didntUnderstand()
{
	$res[] = 'Sorry, I didn\'t understand that.';
	$res[] = 'Sorry, I didn\'t get that.';
	$res[] = 'I\'m sorry, I didn\'t get that.';
	$res[] = 'Sorry, I\'m having trouble understanding you.';
	$res[] = 'Can you please repeat that?';
	$res[] = 'Can you say that again?';
	return $res[array_rand($res)] . ' ';
}
	
?>