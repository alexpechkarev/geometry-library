<?php namespace GeometryLibrary;

/*
 * Copyright 2013 Google Inc.
 * https://github.com/googlemaps/android-maps-utils/blob/master/library/src/com/google/maps/android/SphericalUtil.java
 *
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

use GeometryLibrary\MathUtil;

class SphericalUtil {
    
    /**
     * Returns the heading from one LatLng to another LatLng. Headings are
     * expressed in degrees clockwise from North within the range [-180,180).
     * @return The heading in degrees clockwise from north.
     */
    public static function computeHeading($from, $to) {
        // http://williams.best.vwh.net/avform.htm#Crs
        $fromLat = deg2rad($from['lat']);
        $fromLng = deg2rad($from['lng']);
        $toLat = deg2rad($to['lat']);
        $toLng = deg2rad($to['lng']);
        $dLng = $toLng - $fromLng;
        $heading = atan2(
                sin($dLng) * cos($toLat),
                cos($fromLat) * sin($toLat) - sin($fromLat) * cos($toLat) * cos($dLng));
        
        return MathUtil::wrap(rad2deg($heading), -180, 180);
    }
    
    
/**
     * Returns the LatLng resulting from moving a distance from an origin
     * in the specified heading (expressed in degrees clockwise from north).
     * @param from     The LatLng from which to start.
     * @param distance The distance to travel.
     * @param heading  The heading in degrees clockwise from north.
     */
    public static function computeOffset($from, $distance, $heading) {
        $distance /= MathUtil::$earth_radius;
        $heading = deg2rad($heading);
        // http://williams.best.vwh.net/avform.htm#LL
        $fromLat = deg2rad($from['lat']);
        $fromLng = deg2rad($from['lng']);
        $cosDistance = cos($distance);
        $sinDistance = sin($distance);
        $sinFromLat = sin($fromLat);
        $cosFromLat = cos($fromLat);
        $sinLat = $cosDistance * $sinFromLat + $sinDistance * $cosFromLat * cos($heading);
        $dLng = atan2(
                $sinDistance * $cosFromLat * sin($heading),
                $cosDistance - $sinFromLat * $sinLat);
        
        return ['lat' => rad2deg(asin($sinLat)), 'lng' =>rad2deg($fromLng + $dLng)];
    }    
    
    
    
    /**
     * Returns the location of origin when provided with a LatLng destination,
     * meters travelled and original heading. Headings are expressed in degrees
     * clockwise from North. This function returns null when no solution is
     * available.
     * @param to       The destination LatLng.
     * @param distance The distance travelled, in meters.
     * @param heading  The heading in degrees clockwise from north.
     */
    public static function computeOffsetOrigin($to, $distance,  $heading) {
        $heading = deg2rad($heading);
        $distance /= MathUtil::$earth_radius;
        // http://lists.maptools.org/pipermail/proj/2008-October/003939.html
        $n1 = cos($distance);
        $n2 = sin($distance) * cos($heading);
        $n3 = sin($distance) * sin($heading);
        $n4 = sin(rad2deg($to['lat']));
        // There are two solutions for b. b = n2 * n4 +/- sqrt(), one solution results
        // in the latitude outside the [-90, 90] range. We first try one solution and
        // back off to the other if we are outside that range.
        $n12 = $n1 * $n1;
        $discriminant = $n2 * $n2 * $n12 + $n12 * $n12 - $n12 * $n4 * $n4;
        if ($discriminant < 0) {
            // No real solution which would make sense in LatLng-space.
            return null;
        }
        $b = $n2 * $n4 + sqrt($discriminant);
        $b /= $n1 * $n1 + $n2 * $n2;
        $a = ($n4 - $n2 * $b) / $n1;
        $fromLatRadians = atan2($a, $b);
        if ($fromLatRadians < -M_PI / 2 || $fromLatRadians > M_PI / 2) {
            $b = $n2 * $n4 - sqrt($discriminant);
            $b /= $n1 * $n1 + $n2 * $n2;
            $fromLatRadians = atan2($a, $b);
        }
        if ($fromLatRadians < -M_PI / 2 || $fromLatRadians > M_PI / 2) {
            // No solution which would make sense in LatLng-space.
            return null;
        }
        $fromLngRadians = rad2deg($to['lng']) -
                atan2($n3, $n1 * cos($fromLatRadians) - $n2 * sin($fromLatRadians));
        return ['lat' => rad2deg($fromLatRadians), 'lng' => rad2deg($fromLngRadians)];
    }    
    
    
    
  /**
     * Returns the LatLng which lies the given fraction of the way between the
     * origin LatLng and the destination LatLng.
     * @param from     The LatLng from which to start.
     * @param to       The LatLng toward which to travel.
     * @param fraction A fraction of the distance to travel.
     * @return The interpolated LatLng.
     */
    public static function interpolate($from, $to, $fraction) {
        // http://en.wikipedia.org/wiki/Slerp
        $fromLat = deg2rad($from['lat']);
        $fromLng = deg2rad($from['lng']);
        $toLat = deg2rad($to['lat']);
        $toLng = deg2rad($to['lng']);
        $cosFromLat = cos($fromLat);
        $cosToLat = cos($toLat);

        // Computes Spherical interpolation coefficients.
        $angle = self::computeAngleBetween($from, $to);
        $sinAngle = sin($angle);
        if ($sinAngle < 1E-6) {
            return $from;
        }
        $a = sin((1 - $fraction) * $angle) / $sinAngle;
        $b = sin($fraction * $angle) / $sinAngle;

        // Converts from polar to vector and interpolate.
        $x = $a * $cosFromLat * cos($fromLng) + $b * $cosToLat * cos($toLng);
        $y = $a * $cosFromLat * sin($fromLng) + $b * $cosToLat * sin($toLng);
        $z = $a * sin($fromLat) + $b * sin($toLat);

        // Converts interpolated vector back to polar.
        $lat = atan2($z, sqrt($x * $x + $y * $y));
        $lng = atan2($y, $x);
        return [ 'lat' => rad2deg($lat), 'lng' => rad2deg($lng)];
    }    
    
    
    /**
     * Returns distance on the unit sphere; the arguments are in radians.
     */
    private static function distanceRadians( $lat1,  $lng1,  $lat2,  $lng2) {
        return MathUtil::arcHav(MathUtil::havDistance($lat1, $lat2, $lng1 - $lng2));
    }
    
    /**
     * Returns the angle between two LatLngs, in radians. This is the same as the distance
     * on the unit sphere.
     */
    private static function computeAngleBetween($from, $to) {
        return self::distanceRadians(deg2rad($from['lat']), deg2rad($from['lng']),
                               deg2rad($to['lat']), deg2rad($to['lng']));
    }    
    
    
    /**
     * Returns the distance between two LatLngs, in meters.
     */
    public static function computeDistanceBetween( $from, $to) {
        return self::computeAngleBetween($from, $to) * MathUtil::$earth_radius;
    }  
    
    
    /**
     * Returns the length of the given path, in meters, on Earth.
     */
    public static function computeLength($path) {
        if (count($path) < 2) {
            return 0;
        }
        $length = 0;
        $prev = $path[0];
        $prevLat = deg2rad($prev['lat']);
        $prevLng = deg2rad($prev['lng']);
        foreach($path as $point) {
            $lat = deg2rad($point['lat']);
            $lng = deg2rad($point['lng']);
            $length += self::distanceRadians($prevLat, $prevLng, $lat, $lng);
            $prevLat = $lat;
            $prevLng = $lng;
        }
        return $length * MathUtil::$earth_radius;
    }
    
    
    /**
     * Returns the area of a closed path on Earth.
     * @param path A closed path.
     * @return The path's area in square meters.
     */
    public static function computeArea($path) {
        return abs(self::computeSignedArea($path));
    }    
    
    
    /**
     * Returns the signed area of a closed path on Earth. The sign of the area may be used to
     * determine the orientation of the path.
     * "inside" is the surface that does not contain the South Pole.
     * @param path A closed path.
     * @return The loop's area in square meters.
     */
    public static function computeSignedArea($path) {
        return self::computeSignedAreaP($path, MathUtil::$earth_radius);
    }  
    
/**
     * Returns the signed area of a closed path on a sphere of given radius.
     * The computed area uses the same units as the radius squared.
     * Used by SphericalUtilTest.
     */
    private static function computeSignedAreaP($path,  $radius) {
        $size = count($path);
        if ($size < 3) { return 0; }
        $total = 0;
        $prev = $path[$size - 1];
        $prevTanLat = tan((M_PI / 2 - deg2rad($prev['lat'])) / 2);
        $prevLng = deg2rad($prev['lng']);
        // For each edge, accumulate the signed area of the triangle formed by the North Pole
        // and that edge ("polar triangle").
        foreach($path as $point) {
            $tanLat = tan((M_PI / 2 - deg2rad($point['lat'])) / 2);
            $lng = deg2rad($point['lng']);
            $total += self::polarTriangleArea($tanLat, $lng, $prevTanLat, $prevLng);
            $prevTanLat = $tanLat;
            $prevLng = $lng;
        }
        return $total * ($radius * $radius);
    }   
    
    
    /**
     * Returns the signed area of a triangle which has North Pole as a vertex.
     * Formula derived from "Area of a spherical triangle given two edges and the included angle"
     * as per "Spherical Trigonometry" by Todhunter, page 71, section 103, point 2.
     * See http://books.google.com/books?id=3uBHAAAAIAAJ&pg=PA71
     * The arguments named "tan" are tan((pi/2 - latitude)/2).
     */
    private static function polarTriangleArea($tan1,  $lng1, $tan2, $lng2) {
        $deltaLng = $lng1 - $lng2;
        $t = $tan1 * $tan2;
        return 2 * atan2($t * sin($deltaLng), 1 + $t * cos($deltaLng));
    }    
    
    
}

?>
