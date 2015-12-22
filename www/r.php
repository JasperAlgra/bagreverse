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

$lat = $_GET['lat'];
$lon = $_GET['lon'];

$result = $bag->search($lat, $lon);