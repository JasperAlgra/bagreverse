<?php

function reverseSearchAddress(&$bagDB, $lat=0, $lon=0, $radius=CONST_Default_Radius, $maxRecords=1) {
	
	if(!$lat || !$lon) return Array();

	$rd	= wgs2rd($lat,$lon);
	$x	= $rd['x'];
	$y	= $rd['y'];
	
	$query = "SELECT * FROM nlx_adressen_voor_xy({$x},{$y},{$radius},{$maxRecords})";
	$bag = $bagDB->getRow($query);
	if (PEAR::IsError($bag)) {
		exit("reverseSearchAddress DBerror: ".$bag->getMessage());
	}
	$address = Array();
	// straatnaam  | huisnummer | huisletter | toevoeging | postcode | woonplaats | gemeente |  provincie   | geopunt
	if($bag['woonplaats']) {
		$address = array(
			'house_number' 	=> $bag['huisnummer'].($bag['huisletter']?$bag['huisletter']:null).($bag['toevoeging']?" ".$bag['toevoeging']:null),
			'road'			=> $bag['straatnaam'],
			// 'residential' => $bag[''],
			// 'suburb' => $bag[''],
			'city'			=> $bag['woonplaats'],
			'state' 		=> $bag['provincie'],
			'postcode' 		=> $bag['postcode'],
			'country' 		=> 'Nederland',
			'country_code' 	=> 'nl',
		);
	}
	
	return $address;
}

// "SELECT ST_X(geopunt) as x, ST_Y(geopunt) as y, * FROM adres WHERE ((openbareruimtenaam = '$straat' AND woonplaatsnaam = '$woonplaats') OR postcode = '$postcode') $huisnummer LIMIT 1;"
function searchAddress(&$bagDB, $text, $maxRecords=20) {

	$query = "SELECT ST_X(geopunt) as x, ST_Y(geopunt) as y, * FROM ("
				."SELECT DISTINCT ON (woonplaatsnaam, openbareruimtenaam) woonplaatsnaam, openbareruimtenaam, huisnummer, huisletter, huisnummertoevoeging,"
				." postcode, gemeentenaam, provincienaam, geopunt"
				." FROM adres WHERE";

	$where = "";
	if(!is_array($text)) {
		if (!preg_match("/^[a-zA-Z0-9 ,\-'\"]*$/", $text))
			exit("searchAddress error: characters not allowed");
		
		// Create a query where every combination is possible :D
		if (preg_match("/,/",$text)) {
			$parts = explode(",", $text);
			
			if (preg_match("/ /",$parts[0])) {
				// trim the spaces
				for($i=0;$i<count($parts);$i++)
					$parts[$i] = trim($parts[$i]);
					
				// Probably a space for streetname [space] number
				$streetparts = explode(" ", $parts[0]);
				// trim the spaces
				for($i=0;$i<count($streetparts);$i++)
					$streetparts[$i] = trim($streetparts[$i]);
					
				$where .= ($where?" AND ":null)." (openbareruimtenaam LIKE '".ucfirst(strtolower($streetparts[0]))."%'"
						.(intval($streetparts[1])?" AND huisnummer = '".intval($streetparts[1])."'":null)." AND (woonplaatsnaam LIKE '".ucfirst(strtolower($parts[1]))."%' ".($parts[1]<6?" OR postcode LIKE '".strtoupper($parts[1])."%' ":null)." ))"
						." OR (postcode LIKE '".strtoupper($streetparts[0])."%' AND woonplaatsnaam LIKE '".ucfirst(strtolower($streetparts[1]))."%')";
			} else {
				$where .= ($where?" AND ":null).(intval($parts[1])?" (openbareruimtenaam LIKE '%".ucfirst(strtolower($parts[0]))."%' AND huisnummer = '".intval($parts[1])."') OR":null)
							." (openbareruimtenaam LIKE '".ucfirst(strtolower($parts[0]))."%' AND (woonplaatsnaam LIKE '%".ucfirst(strtolower($parts[1]))."%'".($parts[1]<6?" OR postcode LIKE '".strtoupper($parts[1])."%' ":null)."))"
						." OR (".($parts[1]<6?" postcode LIKE '".strtoupper($parts[0])."%' AND ":null)." woonplaatsnaam LIKE '%".ucfirst(strtolower($parts[1]))."%')";
			}

		} else {
			
		// NO comma's... maybe spaces
			if (preg_match("/ /",$text)) {
				$parts = explode(",", $text);
				for($i=0;$i<count($parts);$i++)
					$parts[$i] = trim($parts[$i]);
				
				if (count($parts) == 2) {
					$where .= ($where?" AND ":null).(intval($parts[1])?" (openbareruimtenaam LIKE '".ucfirst(strtolower($parts[0]))."%' AND huisnummer = '".intval($parts[1])."') OR ":null)
								." (openbareruimtenaam LIKE '".ucfirst(strtolower($parts[0]))."%' AND (woonplaatsnaam LIKE '%".ucfirst(strtolower($parts[1]))."%' OR postcode LIKE '".$parts[1]."%' ))"
							." OR (postcode LIKE '".strtoupper($parts[0])."%' AND woonplaatsnaam LIKE '%".ucfirst(strtolower($parts[1]))."%')";
				} else {
					$where .= ($where?" AND ":null)." (openbareruimtenaam LIKE '".ucfirst(strtolower($parts[0]))."%' "
								.(intval($parts[1])?" AND huisnummer = '".intval($parts[1])."'":null)." AND woonplaatsnaam LIKE '%".$parts[2]."%' )"
							." OR (openbareruimtenaam LIKE '".ucfirst(strtolower($parts[0]))."%' AND (woonplaatsnaam LIKE '".ucfirst(strtolower($parts[1]))."%' OR postcode LIKE '".strtoupper($parts[1])."%' ))"
							." OR (postcode LIKE '".strtoupper($parts[0])."%' AND woonplaatsnaam LIKE '%".ucfirst(strtolower($parts[1]))."%')"
							." OR (postcode LIKE '".strtoupper($parts[1])."%' AND woonplaatsnaam LIKE '%".ucfirst(strtolower($parts[2]))."%')";
				}
			} else {
				$where .= ($where?" AND ":null)." (openbareruimtenaam LIKE '".ucfirst(strtolower($text))."%' "
							.(intval($text)?" OR huisnummer LIKE '".intval($text)."'":null)
							." OR woonplaatsnaam LIKE '".ucfirst(strtolower($text))."%'"
							." OR postcode LIKE '".strtoupper($text)."%')";
			}
		
		}
		
	} else {

		
		if (isset($text['street']) && $text['street'])
			$where .= ($where?" AND ":null)." (openbareruimtenaam LIKE '".ucfirst(strtolower($text['street']))."%' OR openbareruimtenaam LIKE '%".strtolower($text['street'])."%')";
		
		if (isset($text['city']) && $text['city'])
			$where .= ($where?" AND ":null)." (woonplaatsnaam LIKE '".ucfirst(strtolower($text['city']))."%' OR woonplaatsnaam LIKE '%".strtolower($text['city'])."%')";
			
		if (isset($text['postcode']) && $text['postcode'])
			$where .= ($where?" AND ":null)." postcode LIKE '".strtoupper($text['postcode'])."%'";
		
		if (isset($text['number']) && $text['number'] && intval($text['number'])) {
			$where .= ($where?" AND ":null)." huisnummer = '".intval($text['number'])."'";
			if (preg_match("/[A-Za-z]/",$text['number']))
				$where .= ($where?" AND ":null)." huisnummertoevoeging LIKE '".preg_replace("/[^a-zA-Z]*/","",$text['number'])."%'";
				
		}
			
		
	}

	$query .= $where
			."   ORDER BY woonplaatsnaam, openbareruimtenaam, huisnummer ASC"
			." ) as a "
			." LIMIT {$maxRecords}";
	
	// exit($query);
	
	$bag = $bagDB->getAll($query);
	if (PEAR::IsError($bag)) {
			exit("searchAddress DBerror: ".$bag->getMessage());
	}
	return $bag;

}

function rd2wgs ($x, $y) {
	// Calculate WGS84 coördinates
	$dX = ($x - 155000) * pow(10, - 5);
	$dY = ($y - 463000) * pow(10, - 5);
	$SomN = (3235.65389 * $dY) + 
			(- 32.58297 * pow($dX, 2)) + 
			( - 0.24750 * pow($dY, 2)) +
			( - 0.84978 * pow($dX, 2) * $dY) + 
			( - 0.06550 * pow($dY, 3)) + 
			( - 0.01709 * pow($dX, 2) * pow($dY, 2)) + 
			( - 0.00738 * $dX) + 
			(   0.00530 * pow($dX, 4)) + 
			( - 0.00039 * pow($dX, 2) * pow($dY, 3)) + 
			(   0.00033 * pow($dX, 4) * $dY) + 
			( - 0.00012 * $dX * $dY);
	$SomE = (5260.52916 * $dX) +
			( 105.94684 * $dX * $dY) + 
			(   2.45656 * $dX * pow($dY, 2)) + 
			( - 0.81885 * pow($dX, 3)) + 
			(   0.05594 * $dX * pow($dY, 3)) + 
			( - 0.05607 * pow($dX, 3) * $dY) + 
			(   0.01199 * $dY) + 
			( - 0.00256 * pow($dX, 3) * pow($dY, 2)) + 
			(   0.00128 * $dX * pow($dY, 4)) + 
			(   0.00022 * pow($dY,2)) + 
			( - 0.00022 * pow($dX, 2)) + 
			(   0.00026 * pow($dX, 5));
 
	$Latitude = 52.15517440 + ($SomN / 3600);
	$Longitude = 5.38720621 + ($SomE / 3600);
 
	return Array(
		'lat' => $Latitude ,
		'lon' => $Longitude);
}

function wgs2rd ($lat, $lon) {

	$lat0 = 52.15517440;
	$lon0 =  5.38720621;

	$dF = 0.36 * ($lat - $lat0);
	$dL = 0.36 * ($lon - $lon0);

	$c01=190094.945;	$d10=309056.544;
	$c11=-11832.228;	$d02=  3638.893;
	$c21=  -114.221;	$d20=    73.077;
	$c03=   -32.391;	$d12=  -157.984;
	$c10=    -0.705;	$d30=    59.788;
	$c31=    -2.340;	$d01=     0.433;
	$c13=    -0.608;	$d22=    -6.439;
	$c02=    -0.008;	$d11=    -0.032;
	$c23=     0.148;	$d04=     0.092;
				$d14=    -0.054;

	// Volgens "Benaderingsformules voor de transformatie, tussen RD- en WGS84-kaartcoördinate"
	// http://www.dekoepel.nl/pdf/Transformatieformules.pdf
	$SomX = ($c01 * $dL) + ($c11 * $dF * $dL) + ($c21 * pow($dF,2) * $dL) + ($c03 * pow($dL,3)) + ($c10 * $dF) + ($c31 * pow($dF,3) * $dL) + ($c13 * $dF * pow($dL,3)) + ($c02 * pow($dL,2)) + ($c23 * pow($dF,2) * pow($dL,3));
	$SomY = ($d10 * $dF) + ($d02 * pow($dL,2)) + ($d20 * pow($dF,2)) + ($d12 * $dF * pow($dL,2)) + ($d30 * pow($dF,3)) + ($d01 * $dL) + ($d22 * pow($dF,2) * pow($dL,2)) + ($d11 * $dF * $dL) + ($d04 * pow($dL,4)) + ($d14 * $dF * pow($dL,4));

	$x = 155000 + $SomX;
	$y = 463000 + $SomY;
		
	return Array(
		'x' => $x ,
		'y' => $y);
}

function wgs2rd2 ($lat, $lon) {

	$lat0 = 52.15616056;
	$lon0 =  5.38763889;
	
	$dF = 0.36 * ($lat - $lat0);
	$dL = 0.36 * ($lon - $lon0);

	$c01=190066.98903;	$d10=309020.31810;
	$c11=-11830.85831;	$d02=  3638.36193;
	$c21=  -114.19754;	$d12=  -157.95222;
	$c03=   -32.38360;	$d20=    72.97141;
	$c31=    -2.34078;	$d30=    59.79734;
	$c13=    -0.60639;	$d22=    -6.43481;
	$c23=     0.15774;	$d04=     0.09351;
	$c41=    -0.04158;	$d32=    -0.07379;
	$c05=    -0.00661;	$d14=    -0.05419;
				$d40=    -0.03444;
						
						
	$SomX = ($c01 * $dL) + ($c11 * $dF * $dL) + ($c21 * pow($dF,2) * $dL) + ($c03 * pow($dL,3)) + ($c41 * pow($dF,4) * $dL) + ($c31 * pow($dF,3) * $dL) + ($c13 * $dF * pow($dL,3)) + ($c41 * pow($dF,4) * $dL) + ($c23 * pow($dF,2) * pow($dL,3)) + ($c05 * pow($dL,5));
	$SomY = ($d10 * $dF) + ($d02 * pow($dL,2)) + ($d20 * pow($dF,2)) + ($d12 * $dF * pow($dL,2)) + ($d30 * pow($dF,3)) + ($d32 * pow($dF,3) * pow($dL,2)) + ($d22 * pow($dF,2) * pow($dL,2)) + ($d40 * $dF * $dL) + ($d04 * pow($dL,4)) + ($d14 * $dF * pow($dL,4));

	$x = 155000 + $SomX;
	$y = 463000 + $SomY;
	
	return Array(
		'x' => $x ,
		'y' => $y);
}
