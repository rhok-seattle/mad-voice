<?php

$firstPrompt = 'Thanks for calling the Mobile Assessment of Damage hotline.';

/*
 * TODO: resume a past session by entering a reference number
 
$questions[] = array(
	'key' => 'hasreferencenumber',
	'name' => 'Reference Number',
	'prompt' => 'Do you already have a reference number?',
	'prompt2' => didntUnderstand() . 'Do you want to continue a past session by entering a reference number?',
	'choices' => '[BOOLEAN]'
);

if(defined('SURVEY_MODE') && ($callID = surveyVal('hasreferencenumber')) !== FALSE)
{
	if($callID)
	{
		// Resume a previous session
	}
	else
	{
		$questions[] = array(
			'key' => 'referencenumber',
			'name' => 'Reference Number',
			'prompt' => 'What is your reference number?',
			'prompt2' => 'Please speak the digits of your reference number.',
			'choices' => '[DIGITS]'
		);
	}
}
 */

if(defined('SURVEY_MODE'))
{
	$refnum = implode(' ', str_split($_SESSION['callID']));
	
	$firstPrompt .= ' If we get disconnected, you can continue this call by entering the following reference number. . ';
	// TODO: Say "Please say ok when you have a pen and paper ready." and wait for up to 60 seconds rather than the default 10
	$firstPrompt .= ' Your reference number is ' . $refnum . '. Once again, that number is ' . $refnum . '.';
}

$questions[] = array(
	'key' => 'wanttohearinstructions',
	'name' => 'Want to hear instructions?',
	'prompt' => 'Do you want to know what questions we are going to ask you?',
	'prompt2' => didntUnderstand() . 'Please say yes or no.',
	'choices' => '[BOOLEAN]'
);

if(defined('SURVEY_MODE') && surveyVal('wanttohearinstructions') !== FALSE)
{
	$questions[] = array(
		'say' => 'You will need to know the zip code of the damaged property, the primary cause of damage, your insurance provider, if any, and the estimated dollar amount of damages, and a few other questions. Let\'s get started.'
	);
}


$questions[] = array(
	'key' => 'name',
	'name' => 'Name',
	'prompt' => 'What is your full name?',
	'prompt2' => didntUnderstand() . 'What is your name?',
	'choices' => '[RECORD]'
);


$questions[] = array(
	'key' => 'postcode',
	'name' => 'Zip Code',
	'prompt' => 'What is the zip code of the property?',
	'prompt2' => didntUnderstand() . 'Speak each digit of the zip code slowly.',
	'choices' => '[5 DIGITS]'
);

if(defined('SURVEY_MODE') && ($zip = surveyVal('postcode')) !== FALSE && surveyVal('county') == FALSE)
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
		$fips = array_pop(array_flip($counties));
		storeSurveyResponse('county', $fips);
		storeSurveyResponse('state', getStateForFIPS($fips));
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
	'key' => 'street1',
	'name' => 'Address',
	'prompt' => 'What is the address of the property?',
	'prompt2' => didntUnderstand() . 'What is the address?',
	'choices' => '[RECORD]'
);


$questions[] = array(
	'key' => 'habitable',
	'name' => 'Is Habitable?',
	'prompt' => 'Is the property currently habitable?',
	'prompt2' => didntUnderstand() . 'Please say yes or no.',
	'choices' => '[BOOLEAN]'
);

$questions[] = array(
	'key' => 'accessible',
	'name' => 'Accessible?',
	'prompt' => 'Is the property currently accessible?',
	'prompt2' => didntUnderstand() . 'Please say yes or no.',
	'choices' => '[BOOLEAN]'
);



$questions[] = array(
	'key' => 'damagetoday',
	'name' => 'Date of Damage',
	'prompt' => 'Did the damage happen today?',
	'prompt2' => didntUnderstand() . 'Was today the day the damage happened?',
	'choices' => '[BOOLEAN]'
);

if(defined('SURVEY_MODE') && ($today = surveyVal('damagetoday')) !== FALSE)
{
	if($today)
	{
		storeSurveyResponse('date', date('Y-m-d'));
	}
	else
	{
		$questions[] = array(
			'key' => 'date',
			'name' => 'Date of Damage',
			'prompt' => 'What day did the damage happen?',
			'prompt2' => 'Please tell me the full date.',
			'choices' => '[DATE]'
		);
	}
}

$questions[] = array(
	'key' => 'damage_type',
	'name' => 'Type of Damage',
	'prompt' => 'What type of damage happened? You can say structural, roadway, or land.',
	'prompt2' => didntUnderstand() . 'Please say structural, road, or land.',
	'choices' => 'structural(structural, structure, building), roadway(roadway, road), land(land)'
);

$questions[] = array(
	'key' => 'cause',
	'name' => 'Primary Cause',
	'prompt' => 'What was the primary cause of the damage? You can say things like fire, flood, earthquake, and others.',
	'prompt2' => didntUnderstand() . 'The options are fire, flood, earthquake, wind, volcano, snow, ice, vandalism, other.',
	'choices' => 'fire(fire), flood(flood), earthquake(earthquake), wind(wind), volcano(volcano), snow(snow), ice(ice), vandalism(vandalism), other(other)'
);

$questions[] = array(
	'key' => 'description',
	'name' => 'Description',
	'prompt' => 'Please provide a brief description of the damages.',
	'prompt2' => didntUnderstand(),
	'choices' => '[RECORD]'
);


$questions[] = array(
	'key' => 'owner',
	'name' => 'Owner?',
	'prompt' => 'Are you an owner or a renter?',
	'prompt2' => didntUnderstand() . 'Do you own or rent?',
	'choices' => '1(owner, own, 1, yes), 0(renter, rent, 2)'
);

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