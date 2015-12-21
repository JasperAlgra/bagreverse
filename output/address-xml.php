<?php
	header("content-type: text/xml; charset=UTF-8");
	header("Access-Control-Allow-Origin: *");

	echo "<";
	echo "?xml version=\"1.0\" encoding=\"UTF-8\" ?";
	echo ">\n";

	echo "<reversegeocode";
	echo " timestamp='".date(DATE_RFC822)."'";
	echo " attribution='Data Copyright Kadaster, Creative Commons BY-SA'";
	echo " querystring='".htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES)."'";
	echo ">\n";

	if (!isset($object) || !sizeof($object)) {
		if (isset($error) && $error)
			echo "<error>{$error}</error>";
		else
			echo "<error>Unable to geocode</error>";
	} else {
		echo "<result";
		if ($object['place_id']) echo ' place_id="'.$object['place_id'].'"';
		if ($object['osm_type'] && $object['osm_id']) echo ' osm_type="'.($object['osm_type']=='N'?'node':($object['osm_type']=='W'?'way':'relation')).'"'.' osm_id="'.$object['osm_id'].'"';
		if ($object['ref']) echo ' ref="'.htmlspecialchars($object['ref']).'"';
		echo ">".htmlspecialchars($object['langaddress'])."</result>";

		echo "\n<addressparts>";
		foreach($address as $key => $value)
		{
			if(!$value) continue;
			$key = str_replace(' ','_',$key);
			echo "\n\t<{$key}>";
			echo htmlspecialchars($value);
			echo "</{$key}>";
		}
		echo "\n</addressparts>";
	}

	echo "\n</reversegeocode>";
	
?>
