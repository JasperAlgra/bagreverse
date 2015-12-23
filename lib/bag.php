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

    use DB;
    use DOMDocument;
    use Dotenv\Dotenv;

    require '../vendor/autoload.php';

    include 'DB.php';
    include 'helpers.php';

    class bag
    {

        /** @var \DB */
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
            $DSN = Array(
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'phptype'  => 'pgsql',
                'hostspec' => env('DB_HOST'),
                'port'     => env('DB_PORT'),
                'database' => env('DB_DATABASE'),
            );


            // Connect to postgres DB
            $this->DB = DB::connect($DSN);

            // Die on error
            if (\PEAR::IsError($this->DB)) {
                if (env('APP_DEBUG')) {
                    echo 'Standard Message: ' . $this->DB->getMessage() . "\n";
                    echo 'Standard Code: ' . $this->DB->getCode() . "\n";
                    echo 'DBMS/User Message: ' . $this->DB->getUserInfo() . "\n";
                    echo 'DBMS/Debug Message: ' . $this->DB->getDebugInfo() . "\n";
                }

                die("Unable to connect to the database: " . $this->DB->getMessage());
            }


            $this->DB->setFetchMode(DB_FETCHMODE_OBJECT);
            $this->DB->query("SET DateStyle TO 'sql,european'");
            $this->DB->query("SET client_encoding TO 'utf-8'");
            $this->DB->query("SET search_path TO " . env('DB_SEARCH_PATH'));

        }

        /**
         * @param array $latLngs
         * @param int   $radius
         *
         * @return $this
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

                // Do lookup
                $rows = $this->DB->getRow("SELECT * FROM nlx_adressen_voor_xy({$x},{$y},{$radius},{$maxRecords})");

                // Add result to BAGResults var
                $this->BAGResults[] = $rows;
            }

            // Return self
            return $this;

        }

        public function outputXml() {

            $xmlDoc = new DOMDocument('1.0', 'UTF-8');

            /* create the root element of the xml tree */
            $xmlRoot = $xmlDoc->createElement("reversegeocode");
            $xmlRoot->setAttribute('timestamp', Date(DATE_RFC822));

            /* append it to the document created */
            $xmlRoot = $xmlDoc->appendChild($xmlRoot);

            foreach ($this->BAGResults as $address) {

                // Addressparts
                $addressParts = $xmlDoc->createElement("addressParts");

                // House nummber
                $houseNumber = $address->huisnummer
                    . ($address->huisletter ? $address->huisletter : NULL)
                    . ($address->toevoeging ? " " . $address->toevoeging : NULL);
                $addressPart = $xmlDoc->createElement('house_numer',$houseNumber);
                $addressParts->appendChild($addressPart);

                // Road
                $addressPart = $xmlDoc->createElement('road',$address->straatnaam);
                $addressParts->appendChild($addressPart);

                // City
                $addressPart = $xmlDoc->createElement('city',$address->woonplaats);
                $addressParts->appendChild($addressPart);

                // State
                $addressPart = $xmlDoc->createElement('state',$address->provincie);
                $addressParts->appendChild($addressPart);

                // Postcode
                $addressPart = $xmlDoc->createElement('postcode',$address->postcode);
                $addressParts->appendChild($addressPart);

                // Country + country_code (always NL for BAG)
                $addressPart = $xmlDoc->createElement('country','Nederland');
                $addressParts->appendChild($addressPart);
                $addressPart = $xmlDoc->createElement('country_code','nl');
                $addressParts->appendChild($addressPart);

//                foreach($address as $key=>$value) {
//                    // Skip empty
//                    if(is_null($value)) continue;
//
//                    // Create elements for each key/value pair
//                    $addressPart = $xmlDoc->createElement($key, $value);
//                    $addressParts->appendChild($addressPart);
//                }

                // Result 'header'
                // Full text address
                $stringAddress = $houseNumber . ", " . $address->straatnaam . ", " . $address->woonplaats . ", "
                    . $address->provincie . ", " . $address->postcode . ", Nederland, nl";
                $result = $xmlDoc->createElement("result", $stringAddress);

                // Place id
                $result->setAttribute('place_id', $address->gid);

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
