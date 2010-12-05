<?php

$questions[] = array(
	'key' => 'insurance',
	'prompt' => 'Do you have insurance?',
	'choices' => '[BOOLEAN]'
);

if(surveyVal('insurance'))
{
	$questions[] = array(
		'key' => 'provider',
		'prompt' => 'Who is your insurance provider?',
		'choices' => '[RECORD]'
	);
}


$questions[] = array(
	'key' => 'owner',
	'prompt' => 'Are you an owner or a renter?',
	'choices' => '1(owner, own, 1, yes), 0(renter, rent, 2)'
);
$questions[] = array(
	'key' => 'estpredistfmv',
	'prompt' => 'What is the estimated fair market value of the structure before the disaster in dollars?',
	'choices' => '[CURRENCY]'
);
$questions[] = array(
	'key' => 'eststructloss',
	'prompt' => 'What is the estimated dollar amount of the structural loss?',
	'choices' => '[CURRENCY]'
);
$questions[] = array(
	'key' => 'estperproploss',
	'prompt' => 'What is the estimated dollar amount of the personal property loss?',
	'choices' => '[CURRENCY]'
);
/*
$questions[] = array(
	'key' => 'insurance',
	'prompt' => 'Do you have insurance?',
	'choices' => '[BOOLEAN]'
);
$questions[] = array(
	'key' => 'provider',
	'prompt' => 'Who is your insurance provider?',
	'choices' => '[RECORD]'
);
*/

?>