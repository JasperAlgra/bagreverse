<?php
	header ("Content-Type: application/json; charset=UTF-8");
	header("Access-Control-Allow-Origin: *");

	
	if (!sizeof($objects)) {
		$json=Array();
		if ($error)
			$json['error'] = $error;
		else
			$json['error'] = 'Unable to geocode';
		echo json_encode($json);
	} else {
		
		for($i=0;$i<count($objects);$i++) {
			$objects[$i]['licence'] = "Data Copyright OpenStreetMap Contributors, Some Rights Reserved. CC-BY-SA 2.0.";
		}
	}

	if (isset($_GET['json_callback']) && preg_match('/^[-A-Za-z0-9:_.]+$/',$_GET['json_callback'])) {
		echo $_GET['json_callback'].'('.json_encode($objects).')';
	} else {
		echo json_encode($objects);
	}

?>