<?php
	define("CONST_BulkUserIPs",'');
	require_once('../lib/init.php');

	if (strpos(CONST_BulkUserIPs, ','.$_SERVER["REMOTE_ADDR"].',') !== false)
	{
			$fLoadAvg = getLoadAverage();
			if ($fLoadAvg > 2) sleep(60);
			if ($fLoadAvg > 4) sleep(120);
			if ($fLoadAvg > 6)
			{
					echo "Bulk User: Temporary block due to high server load\n";
					exit;
			}
	}

	ini_set('memory_limit', '200M');

	// Format for output
	$sOutputFormat = 'xml';
	if (isset($_GET['format']) && ($_GET['format'] == 'xml' || $_GET['format'] == 'json' || $_GET['format'] == 'jsonv2')) {
			$sOutputFormat = $_GET['format'];
	}
	
	if (false) {
		// If place_id is given.. use it to get the info...
	} else {
		
		$fLat = (float)$_GET['lat'];
		$fLon = (float)$_GET['lon'];
		
		$address = reverseSearchAddress($bagDB, $fLat, $fLon);
	}

	$objects = Array();
	
	if (count($address)) {
		
		$object = Array(
						'place_id' 		=> 0,
						'osm_type' 		=> 'node',
						'osm_id' 		=> 0,
						'lat' 			=> $fLat,
						'lon' 			=> $fLon,
						'ref' 			=> 0,
		);
		
		$object['langaddress'] = "";
		foreach ($address as $item)
			$object['langaddress'] .= ($object['langaddress']?", ":null).$item;
			
		$objects[] = $object;
		
	}
	
	include('../output/address-'.$sOutputFormat.'.php');
	
?>
