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
     * @since      22-12-2015 / 17:32
     */

    if (!function_exists('env')) {
        /**
         * Gets the value of an environment variable. Supports boolean, empty and null.
         *
         * @param  string $key
         * @param  mixed  $default
         *
         * @return mixed
         */
        function env($key, $default = NULL) {
            $value = getenv($key);

            if ($value === FALSE) {
                return value($default);
            }

            switch (strtolower($value)) {
                case 'true':
                case '(true)':
                    return TRUE;

                case 'false':
                case '(false)':
                    return FALSE;

                case 'empty':
                case '(empty)':
                    return '';

                case 'null':
                case '(null)':
                    return;
            }

            if (strlen($value) > 1 && startsWith($value, '"') && endsWith($value, '"')) {
                return substr($value, 1, -1);
            }

            return $value;
        }
    }

    if (!function_exists('value')) {
        /**
         * Return the default value of the given value.
         *
         * @param  mixed $value
         *
         * @return mixed
         */
        function value($value) {
            return $value instanceof Closure ? $value() : $value;
        }
    }


    if (!function_exists('startsWith')) {

        /**
         * Determine if a given string starts with a given substring.
         *
         * @param  string       $haystack
         * @param  string|array $needles
         *
         * @return bool
         */
        function startsWith($haystack, $needles) {
            foreach ((array)$needles as $needle) {
                if ($needle != '' && strpos($haystack, $needle) === 0) {
                    return TRUE;
                }
            }

            return FALSE;
        }
    }

    if (!function_exists('endsWith')) {
        /**
         * Determine if a given string ends with a given substring.
         *
         * @param  string       $haystack
         * @param  string|array $needles
         *
         * @return bool
         */
        function endsWith($haystack, $needles) {
            foreach ((array)$needles as $needle) {
                if ((string)$needle === substr($haystack, -strlen($needle))) {
                    return TRUE;
                }
            }

            return FALSE;
        }
    }

    if (!function_exists('wgs2rd')) {
        /**
         * Convert lat/lon into Rijksdriehoek coordinaten
         *
         * @param $lat
         * @param $lon
         *
         * @return array ($x,$y)
         */
        function wgs2rd($lat, $lon) {

            $lat0 = 52.15517440;
            $lon0 = 5.38720621;

            $dF = 0.36 * ($lat - $lat0);
            $dL = 0.36 * ($lon - $lon0);

            $c01 = 190094.945;
            $d10 = 309056.544;
            $c11 = -11832.228;
            $d02 = 3638.893;
            $c21 = -114.221;
            $d20 = 73.077;
            $c03 = -32.391;
            $d12 = -157.984;
            $c10 = -0.705;
            $d30 = 59.788;
            $c31 = -2.340;
            $d01 = 0.433;
            $c13 = -0.608;
            $d22 = -6.439;
            $c02 = -0.008;
            $d11 = -0.032;
            $c23 = 0.148;
            $d04 = 0.092;
            $d14 = -0.054;

            // Volgens "Benaderingsformules voor de transformatie, tussen RD- en WGS84-kaartco�rdinate"
            // http://www.dekoepel.nl/pdf/Transformatieformules.pdf
            $SomX = ($c01 * $dL) + ($c11 * $dF * $dL) + ($c21 * pow($dF, 2) * $dL) + ($c03 * pow($dL, 3)) + ($c10 * $dF) + ($c31 * pow($dF, 3) * $dL) + ($c13 * $dF * pow($dL, 3)) + ($c02 * pow($dL, 2)) + ($c23 * pow($dF, 2) * pow($dL, 3));
            $SomY = ($d10 * $dF) + ($d02 * pow($dL, 2)) + ($d20 * pow($dF, 2)) + ($d12 * $dF * pow($dL, 2)) + ($d30 * pow($dF, 3)) + ($d01 * $dL) + ($d22 * pow($dF, 2) * pow($dL, 2)) + ($d11 * $dF * $dL) + ($d04 * pow($dL, 4)) + ($d14 * $dF * pow($dL, 4));

            $x = 155000 + $SomX;
            $y = 463000 + $SomY;

            return Array(
                'x' => $x,
                'y' => $y);
        }
    }