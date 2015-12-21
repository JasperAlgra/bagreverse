<?php

	require_once('lib/init.php');

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
	
	
	
	$text="";
	if(isset($_GET['q']) && $_GET['q'])
		$text = $_GET['q'];
	else {
		$text = Array();
		foreach(Array('street','number','city','postcode') as $item) {
			if(isset($_GET[$item])) $text[$item] = $_GET[$item];
		}
	}
	if (!$text && !count($text))
		exit("No query given");
		
	$max = 20;
	if(isset($_GET['max']) && intval($_GET['max']))
		$max = intval($max);
	
	$bag = searchAddress($bagDB, $text, $max);
	
		
	$objects = Array();

	for($i=0;$i<count($bag);$i++) {
		
		$object = Array(
						'place_id' 		=> 0,
						'osm_type' 		=> 'node',
						'osm_id' 		=> 0,
						'lat' 			=> $fLat,
						'lon' 			=> $fLon,
						'ref' 			=> 0,
		);
		
		
		if($bag[$i]['woonplaatsnaam']) {
			
			$address = array(
				'house_number' 	=> $bag[$i]['huisnummer'].($bag[$i]['huisletter']?$bag[$i]['huisletter']:null).($bag[$i]['huisnummertoevoeging']?" ".$bag[$i]['huisnummertoevoeging']:null),
				'road' 			=> $bag[$i]['openbareruimtenaam'],
				// 'residential' => $bag[$i][''],
				// 'suburb' => $bag[$i][''],
				'city' 			=> $bag[$i]['woonplaatsnaam'],
				'state' 		=> $bag[$i]['provincienaam'],
				'postcode' 		=> $bag[$i]['postcode'],
				'country' 		=> 'Nederland',
				'country_code' 	=> 'nl',
			);
			$object['address'] = $address;
			
			$object['display_name'] = "";
			foreach ($address as $item)
				$object['display_name'] .= ($object['display_name']?", ":null).$item;
			
			$coord = rd2wgs($bag[$i]['x'], $bag[$i]['y']);
			
			$object['lat'] = $coord['lat'];
			$object['lon'] = $coord['lon'];
		}
		$objects[] = $object;
	}

	include('output/address-'.$sOutputFormat.'.php');
	
?>