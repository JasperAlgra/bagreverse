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
 * @since      22-12-2015 / 17:23
 */

    /* create a dom document with encoding utf8 */
    $domtree = new DOMDocument('1.0', 'UTF-8');

    /* create the root element of the xml tree */
    $xmlRoot = $domtree->createElement("xml");
    /* append it to the document created */
    $xmlRoot = $domtree->appendChild($xmlRoot);

    $currentTrack = $domtree->createElement("track");
    $currentTrack = $xmlRoot->appendChild($currentTrack);

    /* you should enclose the following two lines in a cicle */
    $currentTrack->appendChild($domtree->createElement('path','song1.mp3'));
    $currentTrack->appendChild($domtree->createElement('title','title of song1.mp3'));

    $currentTrack->appendChild($domtree->createElement('path','song2.mp3'));
    $currentTrack->appendChild($domtree->createElement('title','title of song2.mp3'));

    /* get the xml printed */
    header("content-type: text/xml; charset=UTF-8");
    header("Access-Control-Allow-Origin: *");
    echo $domtree->saveXML();
?>