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
    use Dotenv\Dotenv;

    require '../vendor/autoload.php';

    include 'DB.php';
    include 'helpers.php';

    class bag
    {

        /**
         * @var DB
         */
        var $DB;

        /**
         * @var
         */
        var $config;

        public function __construct() {

            // Get config from .env file
            $dotEnv = new Dotenv('..');
            $dotEnv->load();

            $this->initDb();

        }

        /**
         *
         */
        private function initDb() {

            $DSN = Array(
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'phptype' => 'pgsql',
                'hostspec' => env('DB_HOST'),
                'port'     => env('DB_PORT'),
                'database' => env('DB_DATABASE'),
            );


            $this->DB = DB::connect($DSN);

            if (\PEAR::IsError($this->DB)) {
                if (env('APP_DEBUG')) {
                    echo 'Standard Message: ' . $this->DB->getMessage() . "\n";
                    echo 'Standard Code: ' . $this->DB->getCode() . "\n";
                    echo 'DBMS/User Message: ' . $this->DB->getUserInfo() . "\n";
                    echo 'DBMS/Debug Message: ' . $this->DB->getDebugInfo() . "\n";
                }

                die("Unable to connect to the database: " . $this->DB->getMessage());
            }


            $this->DB->setFetchMode(DB_FETCHMODE_ASSOC);
            $this->DB->query("SET DateStyle TO 'sql,european'");
            $this->DB->query("SET client_encoding TO 'utf-8'");
            $this->DB->query("SET search_path TO " . env('DB_SEARCH_PATH'));

        }

        public function search($lat, $lon, $radius = 500, $maxRecords = 1) {

            // Convert lat/lon to RijksDriehoek
            $rd = wgs2rd($lat, $lon);
            $x = $rd['x'];
            $y = $rd['y'];


            /** @var PEAR\db $this->DB */
            $row = $this->DB->getRow("SELECT * FROM nlx_adressen_voor_xy({$x},{$y},{$radius},{$maxRecords})");

    }

        public function outputXml(array $bagAddress) {

        }

        private function bagQuery($query) {

        }
    }