<?php
define('VIEW_MODE', TRUE);

include('include/header.php');

if(get('id'))
{
?>

<div id="jquery_jplayer"></div>

<script type="text/javascript">
	$(function(){
		$("#jquery_jplayer").jPlayer();
	
		$(".recording").click(function(e) {
			$("#jquery_jplayer").jPlayer("setFile", $(this).attr("href")).jPlayer("play");
			$(this).blur();
			return false;
		});
	});
</script>
<?php

	include('survey.php');
	$labels = array();
	foreach($questions as $q)
		$data[$q['key']] = $q;

	$call = db()->prepare('SELECT * FROM `calls` WHERE `id` = :id');
	$call->bindParam(':id', get('id'));
	$call->execute();
	$call = $call->fetch(PDO::FETCH_ASSOC);

	echo '<table>';
		echo '<tr><td>Date:</td><td>' . $call['date'] . '</td></tr>';
		echo '<tr><td>Caller ID:</td><td>' . $call['callerID'] . '</td></tr>';
	echo '</table>';
	
	$query = db()->prepare('SELECT `id`, `key`, `value`, `recording` FROM `responses` WHERE `callID` = :id');
	$query->bindParam(':id', get('id'));
	$query->execute();
	$responses = array();
	while($q = $query->fetch(PDO::FETCH_ASSOC))
		$responses[$q['key']] = $q;

	echo '<table>';
	foreach($responses as $r)
	{
		if(array_key_exists($r['key'], $data) && array_key_exists('lookup', $data[$r['key']]))
		{
			$lookup = $data[$r['key']]['lookup'];
			$query = db()->prepare('SELECT ' . $lookup['value'] . ' AS val FROM ' . $lookup['table'] . ' WHERE ' . $lookup['key'] . ' = :key');
			$query->bindValue(':key', $r['value']);
			$query->execute();
			$value = $query->fetch(PDO::FETCH_ASSOC);
			$value = $value['val'];
		}
		else
			$value = $r['value'];
	
		echo '<tr>';
			echo '<td>' . (array_key_exists($r['key'], $data) && array_key_exists('name', $data[$r['key']]) ? $data[$r['key']]['name'] : $r['key']) . '</td>';
			echo '<td>' . $value . '</td>';
			echo '<td>' . ($r['recording'] ? '<a href="recordings/' . $r['recording'] . '" id="recording_' . $r['id'] . '" class="recording">listen</a>' : '') . '</td>';
		echo '</tr>';
	}
	echo '</table>';
}
else
{
	$calls = db()->query('SELECT * FROM `calls` ORDER BY `date` DESC');
	echo '<table>';
	foreach($calls as $call)
	{
		echo '<tr>';
			echo '<td><a href="?id=' . $call['id'] . '">' . $call['date'] . '</a></td>';
			echo '<td>' . $call['callerID'] . '</td>';
			#echo '<td>' . $call['dateFinished'] . '</td>';
			#echo '<td>' . ((strtotime($call['dateFinished']) - strtotime($call['date'])) / 60) . '</td>';
		echo '</tr>';
	}
	echo '</table>';
}

include('include/footer.php');

?>