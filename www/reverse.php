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

    if ($_GET['lat'] == "%s" OR $_GET['lon'] == "%s") {
        http_response_code(400);
        exit('Error. LAT or LON is set to "%s"');
    }
}

// Get lat/lon from CLI arguments
if(isset($_SERVER['argv'][1]) AND isset($_SERVER['argv'][2])) {
    $latLngs[] = Array($_SERVER['argv'][1], $_SERVER['argv'][2]);

    if (!floatval($_SERVER['argv'][1]) OR !floatval($_SERVER['argv'][2])) {
        http_response_code(400);
        exit("Error. CLI arguments '{$_SERVER['argv'][1]}' and '{$_SERVER['argv'][2]}' can only be float LAT/LON");
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


try {
    $bag->search($latLngs, $radius)
        ->outputXml();
    header("content-type: text/xml; charset=UTF-8");
    header("Access-Control-Allow-Origin: *");
} catch (Exception $exception) {

    // DB errors
    if($exception->getCode() == 500) {
        http_response_code(500);
        exit($exception->getMessage());
    }

    if($exception->getCode() == 400) {
        http_response_code(400);
        exit("No valid results for this lat/lon");
    }

    // All other errors
    http_response_code(400);
    exit("Error ". (getenv('APP_DEBUG')? $exception->getMessage(): null ));
}
