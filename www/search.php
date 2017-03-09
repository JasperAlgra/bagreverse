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

$number = '';
$postcode = '';

// Search + output XML
header("Access-Control-Allow-Origin: *");

// Get single lat/lon from GET
if (isset($_GET['number']) AND isset($_GET['postcode'])) {
    $number = $_GET['number'];
    $postcode = $_GET['postcode'];

    $bag
        ->searchPostal($postcode, $number);
    if ($_GET['format'] == 'json') {
        header('Content-Type: application/json');
        echo json_encode($bag->BAGResults);
    } else {
        header("content-type: text/xml; charset=UTF-8");
        $bag->outputXML();
    }
}