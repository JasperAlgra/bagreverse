<?php

	require_once('settings.php');
	require_once('lib.php');
	require_once('DB.php');

	if (get_magic_quotes_gpc()) {
		echo "Please disable magic quotes in your php.ini configuration";
		exit;
	}

	if (CONST_ClosedForIndexing && strpos(CONST_ClosedForIndexingExceptionIPs, ','.$_SERVER["REMOTE_ADDR"].',') === false) {
		echo "Closed for re-indexing...";
		exit;
	}

	if (strpos(CONST_BlockedIPs, ','.@$_SERVER["REMOTE_ADDR"].',') !== false) {
		echo "Your IP has been blocked. \n";
		exit;
	}

	// Get the database object
	$bagDB =& DB::connect(CONST_Database_DSN, false);
	if (PEAR::IsError($bagDB))
	{
		echo 'Standard Message: ' . $db->getMessage() . "\n";
		echo 'Standard Code: ' . $db->getCode() . "\n";
		echo 'DBMS/User Message: ' . $db->getUserInfo() . "\n";
		echo 'DBMS/Debug Message: ' . $db->getDebugInfo() . "\n";

		exit("Unable to connect to the database: ". $bagDB->getMessage());
	}
	
	$bagDB->setFetchMode(DB_FETCHMODE_ASSOC);
	$bagDB->query("SET DateStyle TO 'sql,european'");
	$bagDB->query("SET client_encoding TO 'utf-8'");

	// header('Content-type: text/html; charset=utf-8');
	
?>