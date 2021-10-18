<?php namespace GeometryLibrary;

/*
 * Copyright 2013 Google Inc.
 * https://github.com/googlemaps/android-maps-utils/blob/master/library/src/com/google/maps/android/MathUtil.java
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


class MathUtil {
  /**
     * The earth's radius, in meters.
     * Mean radius as defined by IUGG.
     * https://developers.google.com/maps/documentation/javascript/reference/geometry
     * Utility functions for computing geodesic angles, distances and areas. 
     * The default radius is Earth's radius of 6378137 meters.
     *
     * 2021-10-18 - Earth's radius updated in accordance with Google Maps JavaScript API;
     */    
    public static $earth_radius = 6378137;

    /**
     * Change the earth radius for different earth points
     */
    public static function changeEarthRadius($newRadius) {
        self::$earth_radius = $newRadius;
    }

    /**
     * Restrict x to the range [low, high].
     */
    public static function clamp( $x, $low, $high) {
        return $x < $low ? $low : ($x > $high ? $high : $x);
    }
    
    /**
     * Wraps the given value into the inclusive-exclusive interval between min and max.
     * @param n   The value to wrap.
     * @param min The minimum.
     * @param max The maximum.
     */
    public static function wrap($n, $min, $max) {
        return ($n >= $min && $n < $max) ? $n : (self::mod($n - $min, $max - $min) + $min);
    }

    /**
     * Returns the non-negative remainder of x / m.
     * @param x The operand.
     * @param m The modulus.
     */
    public static function mod($x, $m) {
        return (($x % $m) + $m) % $m;
    }

    /**
     * Returns mercator Y corresponding to latitude.
     * See http://en.wikipedia.org/wiki/Mercator_projection .
     */
    public static function mercator($lat) {
        return log(tan($lat * 0.5 + M_PI/4));
    }

    /**
     * Returns latitude from mercator Y.
     */
    public static function inverseMercator($y) {
        return 2 * atan(exp($y)) - M_PI / 2;
    }
    
    /**
     * Returns haversine(angle-in-radians).
     * hav(x) == (1 - cos(x)) / 2 == sin(x / 2)^2.
     */
    public static function hav($x) {
        $sinHalf = sin($x * 0.5);
        return $sinHalf * $sinHalf;
    }

    /**
     * Computes inverse haversine. Has good numerical stability around 0.
     * arcHav(x) == acos(1 - 2 * x) == 2 * asin(sqrt(x)).
     * The argument must be in [0, 1], and the result is positive.
     */
    public static function arcHav($x) {
        return 2 * asin(sqrt($x));
    }
    
    // Given h==hav(x), returns sin(abs(x)).
    public static function sinFromHav($h) {
        return 2 * sqrt($h * (1 - $h));
    }

    // Returns hav(asin(x)).
    public static function havFromSin($x) {
        $x2 = $x * $x;
        return $x2 / (1 + sqrt(1 - $x2)) * .5;
    }

    // Returns sin(arcHav(x) + arcHav(y)).
    public static function sinSumFromHav($x, $y) {
        $a = sqrt($x * (1 - $x));
        $b = sqrt($y * (1 - $y));
        return 2 * ($a + $b - 2 * ($a * $y + $b * $x));
    }

    /**
     * Returns hav() of distance from (lat1, lng1) to (lat2, lng2) on the unit sphere.
     */
    public static function havDistance($lat1, $lat2, $dLng) {
        return self::hav($lat1 - $lat2) + self::hav($dLng) * cos($lat1) * cos($lat2);
    }
}

?>
