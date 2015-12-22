<?php

	@define('CONST_Debug', true);
	@define('CONST_ClosedForIndexing', false);
	@define('CONST_ClosedForIndexingExceptionIPs', ',192.168.1.137,77.100.156.176,87.194.178.147,');
	@define('CONST_BlockedIPs', ',85.22.48.42,66.249.71.236,');

	@define('CONST_Database_DSN', 'pgsql://bag:bag@geo1.eztrack.nl/bag');

	@define('CONST_Default_Language', 'nl');
	@define('CONST_Default_Lat', 52.011788);
	@define('CONST_Default_Lon', 4.359405);
	@define('CONST_Default_Radius', 500); // in meters

?>
