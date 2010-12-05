<?php
include('config.php');

function askQuestion($question, $secondTry=FALSE)
{
	global $tropo, $voice;
	
	$ask = 'ask';
	switch($question['choices'])
	{
		case '[BOOLEAN]':
			$choices = '1(yes, 1), 0(no, 0)';
			break;

		default:
			$choices = $question['choices'];
			break;
	}
	
	$tropo[] = array(
		'on' => array(
			'event' => 'continue',
			'next' => $question['key'] . '.json' . ($question['choices'] == '[RECORD]' ? '?hadRecording=1' : '')
		)
	);
	$tropo[] = array(
		'on' => array(
			'event' => 'incomplete',
			'next' => $question['key'] . '.json'
		)
	);

	if($question['choices'] == '[RECORD]')
	{
		$tropo[] = array(
			'record' => array(
				'say' => array(
					'value' => $question[($secondTry && array_key_exists('prompt2', $question) ? 'prompt2' : 'prompt')],
				),
				'voice' => $voice,
				'name' => $question['key'],
				'maxSilence' => 2,   // if they stop talking for 2 seconds it will end recording and move on
				'beep' => FALSE,
				'format' => 'audio/mp3',
				'url' => 'http://' . $_SERVER['SERVER_NAME'] . WEB_ROOT . $question['key'] . '.json?record=1&session_id=' . session_id()
			)
		);
	}
	else
	{
		$tropo[] = array(
			'record' => array(
				'say' => array(
					'value' => $question[($secondTry && array_key_exists('prompt2', $question) ? 'prompt2' : 'prompt')],
				),
				'voice' => $voice,
				'bargein' => FALSE,
				'timeout' => 15,
				'name' => $question['key'],
				'choices' => array(
					'value' => $choices
				),
				'beep' => FALSE,
				'format' => 'audio/mp3',
				'url' => 'http://' . $_SERVER['SERVER_NAME'] . WEB_ROOT . $question['key'] . '.json?record=1&session_id=' . session_id()
			)
		);
	}
}

function storeSurveyResponse($key, $value)
{
	$_SESSION['responses'][$key] = $value;
	
	// Attempt to update the record in the DB
	$query = db()->prepare('UPDATE `responses` SET `value` = :value WHERE `callID` = :callID AND `key` = :key');
	$query->bindValue(':callID', $_SESSION['callID']);
	$query->bindValue(':key', $key);
	$query->bindValue(':value', $value);
	$query->execute();

	// If no rows were updated, that means there wasn't already a row for this key, so insert it now
	if($query->rowCount() == 0)
	{
		$query = db()->prepare('INSERT INTO `responses` (`callID`, `key`, `value`) VALUES(:callID, :key, :value)');
		$query->bindValue(':callID', $_SESSION['callID']);
		$query->bindValue(':key', $key);
		$query->bindValue(':value', $value);
		$query->execute();
	}
}

function getCountiesForZipcode($zip)
{
	switch($zip)
	{
		case 98683:
			return array(53011=>'Clark');
		case 98112:
			return array(53033=>'King');
		case 98111:
			return array(53033=>'King', 53053=>'Pierce');
		default:
			return array(53033=>'King');
	}
}

function getCitiesForZipcode($zip)
{
	switch($zip)
	{
		case 98683:
			return array('Vancouver');
		case 98112:
			return array('Seattle');
		case 98111:
			return array('Seattle', 'Bellevue');
		default:
			return array('Seattle');
	}
}



function tropoInput()
{
	if(get('test') == 1)
	{
		return new ObjectArray($_GET);
	}

	$jsonInput = file_get_contents("php://input");
	$input = json_decode($jsonInput);
	if(DEBUG_MODE)
	{
		ob_start();
			echo "\n\n\n\n" . date('Y-m-d H:i:s') . ' ' . $_SERVER['REMOTE_ADDR'] . "\n";
			echo '$_GET' . "\n";
			print_r($_GET);
			echo 'request headers' . "\n";
			print_r(apache_request_headers());
			echo '$_FILES' . "\n";
			print_r($_FILES);
			echo 'JSON input' . "\n";
			print_r($input);
			echo "\n";
		filedebug(ob_get_clean());
	}
	return $input;
}

function tropoInputValue()
{
	global $input;

	if(get('test'))
		return get('value');

	if(property_exists($input, 'result') && property_exists($input->result, 'actions'))
	{
		if(property_exists($input->result->actions, 'value'))
			return $input->result->actions->value;
		else
			return FALSE;
	}
	else
		return FALSE;
}

function db()
{
	static $db;
	
	if(!isset($db))
	{
		try {
			$db = new PDO(PDO_DSN, PDO_USER, PDO_PASS);
		} catch (PDOException $e) {
			header('HTTP/1.1 500 Server Error');
			die('Connection failed: ' . $e->getMessage());
		}
	}
	
	return $db;
}

function get($k)
{
	if(array_key_exists($k, $_GET))
		return $_GET[$k];
	else
		return FALSE;
}

function post($k)
{
	if(array_key_exists($k, $_POST))
		return $_POST[$k];
	else
		return FALSE;
}

function surveyVal($k)
{
	// If we're not in the middle of a survey, respond with TRUE so the survey.php file runs all blocks
	if(defined('VIEW_MODE'))
		return TRUE;

	if(array_key_exists('responses', $_SESSION) && array_key_exists($k, $_SESSION['responses']))
		return $_SESSION['responses'][$k];
	else
		return FALSE;
}

function ircdebug($msg)
{
	$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
	socket_sendto($sock, $msg, strlen($msg), 0, MW_IRC_HOST, MW_IRC_PORT);
}

function filedebug($obj)
{
	static $fp;
	
	if(!isset($fp))
		$fp = fopen(TMP_LOGFILE, 'a');

	if(is_array($obj) || is_object($obj))
	{	
		ob_start();
			print_r($obj);
			echo "\n";
		fwrite($fp, ob_get_clean());
	}
	else
		fwrite($fp, $obj . "\n");
}

/**
 * Indents a flat JSON string to make it more human-readable
 *
 * @param string $json The original JSON string to process
 * @return string Indented version of the original JSON string
 */
function formatJSON($json)
{
    $result    = '';
    $pos       = 0;
    $strLen    = strlen($json);
    $indentStr = '  ';
    $newLine   = "\n";

    for($i = 0; $i <= $strLen; $i++) {
        
        // Grab the next character in the string
        $char = substr($json, $i, 1);
        
        // If this character is the end of an element, 
        // output a new line and indent the next line
        if($char == '}' || $char == ']') {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        // Add the character to the result string
        $result .= $char;

        // If the last character was the beginning of an element, 
        // output a new line and indent the next line
        if ($char == ',' || $char == '{' || $char == '[') {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
    }

    return $result;
}

class ObjectArray
{
	private $_data;
	public function __construct($data)
	{
		$this->_data = $data;
	} 
	public function __get($key)
	{
		return array_key_exists($key, $this->_data) ? $this->_data[$key] : FALSE;
	}
}

?>