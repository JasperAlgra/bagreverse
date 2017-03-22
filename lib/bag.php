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
 * @since      22-12-2015 / 17:27
 */

namespace bag;

use DOMDocument;
use Dotenv\Dotenv;
use PDO;
use PDOException;

require '../vendor/autoload.php';

include 'helpers.php';

class bag {

    /** @var PDO */
    var $DB;

    /** @var array for storing BAG results */
    var $BAGResults;

    public function __construct() {

        // Check for .env file
        if (!file_exists('../.env')) {
            die("No .env file found. Create one, see .env.example");
        }

        // Get config from .env file
        $dotEnv = new Dotenv('..');
        $dotEnv->load();

        // Create connection to the postgres DB
        $this->initDb();

    }

    /**
     * setup connection to postgres DB
     * connects or dies
     */
    private function initDb() {

        // Full DNS for connection to postgres
        $dsn = "pgsql:host=" . getenv('DB_HOST') . ";port=" . env('DB_PORT') . ";dbname=" . getenv('DB_DATABASE') . ";user=" . getenv('DB_USERNAME') . ";password=" . getenv('DB_PASSWORD');

        // Setup PDO
        try {
            $this->DB = new PDO($dsn);
            $this->DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->DB->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error in Connecting to database: ' . $e->getMessage());
        }

        $this->DB->exec("SET DateStyle TO 'sql,european'");
        $this->DB->exec("SET client_encoding TO 'utf-8'");
        $this->DB->exec("SET search_path TO " . env('DB_SEARCH_PATH'));

    }

    /**
     * @param array $latLngs
     * @param int   $radius
     *
     * @return $this
     * @throws \Exception
     */
    public function search(array $latLngs, $radius = 500) {

        // Max records per lat/lng
        $maxRecords = 1;

        // Lookup all lat/lngs
        foreach ($latLngs as $latLng) {

            // Convert lat/lon to RijksDriehoek
            $rd = wgs2rd($latLng[0], $latLng[1]);
            $x = $rd['x'];
            $y = $rd['y'];
            $q = "SELECT * FROM nlx_adressen_voor_xy({$x},{$y},{$radius},{$maxRecords})";
            // Do lookup
            try {
                $query = $this->DB->query("SELECT * FROM nlx_adressen_voor_xy({$x},{$y},{$radius},{$maxRecords})");
                $rows = $query->fetchObject();
            } catch (\Exception $exception) {
                $message = "DB Error". (getenv('APP_DEBUG')? $exception->getMessage(): null );
                throw new \Exception($message, 500);
            }
            // Add result to BAGResults var
            if($rows) $this->BAGResults[] = $rows;
        }

        // Return self
        return $this;

    }

    public function searchPostal($postcode, $number) {

        $query = $this->DB->query("SELECT ST_X(geopunt) as x, ST_Y(geopunt) as y, * FROM geo_adres WHERE postcode = '$postcode' AND huisnummer = '$number' LIMIT 1;");
        $row = $query->fetchObject();

        // Do lookup
        $latLng = rd2wgs($row->x, $row->y);
        $row->lat = $latLng['lat'];
        $row->lon = $latLng['lon'];

        // Add result to BAGResults var
        $this->BAGResults[] = $row;

        // Return self
        return $this;
    }


    /**
     * @throws \Exception
     */
    public function outputXml() {

        $xmlDoc = new DOMDocument('1.0', 'UTF-8');

        /* create the root element of the xml tree */
        $xmlRoot = $xmlDoc->createElement("reversegeocode");
        $xmlRoot->setAttribute('timestamp', Date(DATE_RFC822));

        /* append it to the document created */
        $xmlRoot = $xmlDoc->appendChild($xmlRoot);

        // No valid results found?
        if(!$this->BAGResults) throw new \Exception('No results', 400);

        foreach ($this->BAGResults as $address) {

            // Addressparts
            $addressParts = $xmlDoc->createElement("addressparts");

            // House number
            $houseNumber = $address->huisnummer
                . ($address->huisletter ? $address->huisletter : NULL)
                . ($address->toevoeging ? " " . $address->toevoeging : NULL);
            $addressPart = $xmlDoc->createElement('house_number', $houseNumber);
            $addressParts->appendChild($addressPart);

            // Road
            $addressPart = $xmlDoc->createElement('road', $address->straatnaam);
            $addressParts->appendChild($addressPart);

            // City
            $addressPart = $xmlDoc->createElement('city', $address->woonplaats);
            $addressParts->appendChild($addressPart);

            // State
            $addressPart = $xmlDoc->createElement('state', $address->provincie);
            $addressParts->appendChild($addressPart);

            // Postcode
            $addressPart = $xmlDoc->createElement('postcode', $address->postcode);
            $addressParts->appendChild($addressPart);

            // Country + country_code (always NL for BAG)
            $addressPart = $xmlDoc->createElement('country', 'Nederland');
            $addressParts->appendChild($addressPart);
            $addressPart = $xmlDoc->createElement('country_code', 'nl');
            $addressParts->appendChild($addressPart);

            // Result 'header'
            // Full text address
            $stringAddress = $houseNumber . ", " . $address->straatnaam . ", " . $address->woonplaats . ", "
                . $address->provincie . ", " . $address->postcode . ", Nederland, nl";
            $result = $xmlDoc->createElement("result", $stringAddress);

            // Place id
            if(isset($address->gid)) $result->setAttribute('place_id', $address->gid);

            // Add header to doc
            $xmlRoot->appendChild($result);

            // Append the addressParts to the XML doc
            $xmlRoot->appendChild($addressParts);
        }


        /* get the xml printed */
//            header("content-type: text/xml; charset=UTF-8");
//            header("Access-Control-Allow-Origin: *");
        echo $xmlDoc->saveXML();
    }

}