<?php
/**
 * [Short description for file]
 *
 * [Long description for file (if any)...]
 *
 * @category   EuropeTrack 2.0
 * @package    EuropeTrack 2.0
 * @author     Jasper Algra <jasper@yarp-bv.nl>
 * @copyright  (C)Copyright 2015 YARP B.V.
 * @version    CVS: $Id:$
 * @since      22-12-2015 / 17:48
 */

include('../lib/bag.php');

$bag = new bag\bag();

$latLngs = Array();

// Get single lat/lon from GET
if (isset($_GET['lat']) AND isset($_GET['lon'])) {
    $latLngs[] = Array($_GET['lat'], $_GET['lon']);

    if($_GET['lat'] == "%s" OR $_GET['lon'] == "%s") {
        http_response_code(400);
        exit('Error. LAT or LON is set to "%s"');
    }
}

// Get array of lats/lons from GET or POST
if (isset($_GET['latLngs'])) {
    $latLngs = json_decode($_GET['latLngs']);
} elseif (isset($_POST['latLngs'])) {
    $latLngs = json_decode($_POST['latLngs']);
}

$radius = 150;
if (isset($_GET['radius'])) {
    $radius = $_GET['radius'];
}

// Search + output XML
header("content-type: text/xml; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
$bag
    ->search($latLngs, $radius)
    ->outputXml();