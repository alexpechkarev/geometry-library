<?php namespace GeometryLibrary;

/*
 * Copyright 2013 Google Inc.
 * 
 * https://github.com/googlemaps/android-maps-utils/blob/master/library/src/com/google/maps/android/PolyUtil.java
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
use GeometryLibrary\SphericalUtil;

class PolyUtil {
    
    const DEFAULT_TOLERANCE = 0.1;  // meters.

    /**
     * Returns tan(latitude-at-lng3) on the great circle (lat1, lng1) to (lat2, lng2). lng1==0.
     * See http://williams.best.vwh.net/avform.htm .
     */
    private static function tanLatGC( $lat1,  $lat2,  $lng2,  $lng3) {
        return (tan($lat1) * sin($lng2 - $lng3) + tan($lat2) * sin($lng3)) / sin($lng2);
    }  
    
    /**
     * Returns mercator(latitude-at-lng3) on the Rhumb line (lat1, lng1) to (lat2, lng2). lng1==0.
     */        
    private static function  mercatorLatRhumb( $lat1, $lat2,  $lng2,  $lng3) {
        
        return (MathUtil::mercator($lat1) * ($lng2 - $lng3) + MathUtil::mercator($lat2) * $lng3) / $lng2;
    }
    
  
    
    
 /**
     * Computes whether the vertical segment (lat3, lng3) to South Pole intersects the segment
     * (lat1, lng1) to (lat2, lng2).
     * Longitudes are offset by -lng1; the implicit lng1 becomes 0.
     */
    private static function intersects( $lat1,  $lat2, $lng2, $lat3, $lng3, $geodesic) {
        // Both ends on the same side of lng3.
        if (($lng3 >= 0 && $lng3 >= $lng2) || ($lng3 < 0 && $lng3 < $lng2)) {
            return false;
        }
        // Point is South Pole.
        if ($lat3 <= -M_PI/2) {
            return false;
        }
        // Any segment end is a pole.
        if ($lat1 <= -M_PI/2 || $lat2 <= -M_PI/2 || $lat1 >= M_PI/2 || $lat2 >= M_PI/2) {
            return false;
        }
        if ($lng2 <= -M_PI) {
            return false;
        }
        $linearLat = ($lat1 * ($lng2 - $lng3) + $lat2 * $lng3) / $lng2;
        // Northern hemisphere and point under lat-lng line.
        if ($lat1 >= 0 && $lat2 >= 0 && $lat3 < $linearLat) {
            return false;
        }
        // Southern hemisphere and point above lat-lng line.
        if ($lat1 <= 0 && $lat2 <= 0 && $lat3 >= $linearLat) {
            return true;
        }
        // North Pole.
        if ($lat3 >= M_PI/2) {
            return true;
        }
        // Compare lat3 with latitude on the GC/Rhumb segment corresponding to lng3.
        // Compare through a strictly-increasing function (tan() or mercator()) as convenient.
        return $geodesic ?
            tan($lat3) >= self::tanLatGC($lat1, $lat2, $lng2, $lng3) :
            MathUtil::mercator($lat3) >= self::mercatorLatRhumb($lat1, $lat2, $lng2, $lng3);
    }  
    
    
/**
     * Computes whether the given point lies inside the specified polygon.
     * The polygon is always cosidered closed, regardless of whether the last point equals
     * the first or not.
     * Inside is defined as not containing the South Pole -- the South Pole is always outside.
     * The polygon is formed of great circle segments if geodesic is true, and of rhumb
     * (loxodromic) segments otherwise.
     */
    public static function containsLocation($point, $polygon, $geodesic = false) {
       
        $size = count( $polygon );
        
        if ($size == 0) {
            return false;
        }
        $lat3 = deg2rad( $point['lat'] );
        $lng3 = deg2rad( $point['lng'] );
        $prev = $polygon[$size - 1];
        $lat1 = deg2rad( $prev['lat'] );
        $lng1 = deg2rad( $prev['lng'] );
        
        $nIntersect = 0;
        
        foreach($polygon as $key => $val) {
            
            $dLng3 = MathUtil::wrap($lng3 - $lng1, -M_PI, M_PI);
            // Special case: point equal to vertex is inside.
            if ($lat3 == $lat1 && $dLng3 == 0) {
                return true;
            }
            
            $lat2 = deg2rad($val['lat']);
            $lng2 = deg2rad($val['lng']);
            
            // Offset longitudes by -lng1.
            if (self::intersects($lat1, $lat2, MathUtil::wrap($lng2 - $lng1, -M_PI, M_PI), $lat3, $dLng3, $geodesic)) {
                ++$nIntersect;
            }
            $lat1 = $lat2;
            $lng1 = $lng2;
        }
        return ($nIntersect & 1) != 0;
    }    
    
    
  /**
     * Computes whether the given point lies on or near the edge of a polygon, within a specified
     * tolerance in meters. The polygon edge is composed of great circle segments if geodesic
     * is true, and of Rhumb segments otherwise. The polygon edge is implicitly closed -- the
     * closing segment between the first point and the last point is included.
     */
    public static function isLocationOnEdge($point, $polygon, $tolerance = self::DEFAULT_TOLERANCE, $geodesic = true) {
        return self::isLocationOnEdgeOrPath($point, $polygon, true, $geodesic, $tolerance);
    }  
    
    
 /**
     * Computes whether the given point lies on or near a polyline, within a specified
     * tolerance in meters. The polyline is composed of great circle segments if geodesic
     * is true, and of Rhumb segments otherwise. The polyline is not closed -- the closing
     * segment between the first point and the last point is not included.
     */
    public static function isLocationOnPath($point, $polyline, $tolerance = self::DEFAULT_TOLERANCE, $geodesic = true) {
        return self::isLocationOnEdgeOrPath($point, $polyline, false, $geodesic, $tolerance);
    }    
    
    
    private static function isLocationOnEdgeOrPath($point, $poly, $closed, $geodesic, $toleranceEarth) {
        
        $size = count( $poly );
        
        if ($size == 0) {
            return false;
        }
        
        $tolerance = $toleranceEarth / MathUtil::$earth_radius;
        $havTolerance = MathUtil::hav($tolerance);
        $lat3 = deg2rad($point['lat']);
        $lng3 = deg2rad($point['lng']);
        $prev = !empty($closed) ? $poly[$size - 1] : $poly[0];
        $lat1 = deg2rad($prev['lat']);
        $lng1 = deg2rad($prev['lng']);
        
        if ($geodesic) {
            foreach($poly as $val) {
                $lat2 = deg2rad($val['lat']);
                $lng2 = deg2rad($val['lng']);
                if ( self::isOnSegmentGC($lat1, $lng1, $lat2, $lng2, $lat3, $lng3, $havTolerance)) {
                    return true;
                }
                $lat1 = $lat2;
                $lng1 = $lng2;
            }
        } else {
            // We project the points to mercator space, where the Rhumb segment is a straight line,
            // and compute the geodesic distance between point3 and the closest point on the
            // segment. This method is an approximation, because it uses "closest" in mercator
            // space which is not "closest" on the sphere -- but the error is small because
            // "tolerance" is small.
            $minAcceptable = $lat3 - $tolerance;
            $maxAcceptable = $lat3 + $tolerance;
            $y1 = MathUtil::mercator($lat1);
            $y3 = MathUtil::mercator($lat3);
            $xTry = [];
            foreach($poly as $val) {
                $lat2 = deg2rad($val['lat']);
                $y2 = MathUtil::mercator($lat2);
                $lng2 = deg2rad($val['lng']);                
                if (max($lat1, $lat2) >= $minAcceptable && min($lat1, $lat2) <= $maxAcceptable) {
                    // We offset longitudes by -lng1; the implicit x1 is 0.
                    $x2 = MathUtil::wrap($lng2 - $lng1, -M_PI, M_PI);
                    $x3Base = MathUtil::wrap($lng3 - $lng1, -M_PI, M_PI);
                    $xTry[0] = $x3Base;
                    // Also explore wrapping of x3Base around the world in both directions.
                    $xTry[1] = $x3Base + 2 * M_PI;
                    $xTry[2] = $x3Base - 2 * M_PI;
                    
                    foreach($xTry as $x3) {
                        $dy = $y2 - $y1;
                        $len2 = $x2 * $x2 + $dy * $dy;
                        $t = $len2 <= 0 ? 0 : MathUtil::clamp(($x3 * $x2 + ($y3 - $y1) * $dy) / $len2, 0, 1);
                        $xClosest = $t * $x2;
                        $yClosest = $y1 + $t * $dy;
                        $latClosest = MathUtil::inverseMercator($yClosest);
                        $havDist = MathUtil::havDistance($lat3, $latClosest, $x3 - $xClosest);
                        if ($havDist < $havTolerance) {
                            return true;
                        }
                    }
                }
                $lat1 = $lat2;
                $lng1 = $lng2;
                $y1 = $y2;
            }
        }
        return false;
    }
    
    
    
 /**
     * Returns sin(initial bearing from (lat1,lng1) to (lat3,lng3) minus initial bearing
     * from (lat1, lng1) to (lat2,lng2)).
     */
    private static function sinDeltaBearing( $lat1, $lng1, $lat2, $lng2, $lat3, $lng3) {
        
        $sinLat1 = sin($lat1);
        $cosLat2 = cos($lat2);
        $cosLat3 = cos($lat3);
        $lat31 = $lat3 - $lat1;
        $lng31 = $lng3 - $lng1;
        $lat21 = $lat2 - $lat1;
        $lng21 = $lng2 - $lng1;
        $a = sin($lng31) * $cosLat3;
        $c = sin($lng21) * $cosLat2;
        $b = sin($lat31) + 2 * $sinLat1 * $cosLat3 * MathUtil::hav($lng31);
        $d = sin($lat21) + 2 * $sinLat1 * $cosLat2 * MathUtil::hav($lng21);
        $denom = ($a * $a + $b * $b) * ($c * $c + $d * $d);
        return $denom <= 0 ? 1 : ($a * $d - $b * $c) / sqrt($denom);
    }    
  
    
    private static function isOnSegmentGC( $lat1, $lng1, $lat2, $lng2, $lat3, $lng3, $havTolerance) {
        
        $havDist13 = MathUtil::havDistance($lat1, $lat3, $lng1 - $lng3);
        if ($havDist13 <= $havTolerance) {
            return true;
        }
        $havDist23 = MathUtil::havDistance($lat2, $lat3, $lng2 - $lng3);
        if ($havDist23 <= $havTolerance) {
            return true;
        }
        $sinBearing = self::sinDeltaBearing($lat1, $lng1, $lat2, $lng2, $lat3, $lng3);
        $sinDist13 = MathUtil::sinFromHav($havDist13);
        $havCrossTrack = MathUtil::havFromSin($sinDist13 * $sinBearing);
        if ($havCrossTrack > $havTolerance) {
            return false;
        }
        $havDist12 = MathUtil::havDistance($lat1, $lat2, $lng1 - $lng2);
        $term = $havDist12 + $havCrossTrack * (1 - 2 * $havDist12);
        if ($havDist13 > $term || $havDist23 > $term) {
            return false;
        }
        if ($havDist12 < 0.74) {
            return true;
        }
        $cosCrossTrack = 1 - 2 * $havCrossTrack;
        $havAlongTrack13 = ($havDist13 - $havCrossTrack) / $cosCrossTrack;
        $havAlongTrack23 = ($havDist23 - $havCrossTrack) / $cosCrossTrack;
        $sinSumAlongTrack = MathUtil::sinSumFromHav($havAlongTrack13, $havAlongTrack23);
        return $sinSumAlongTrack > 0;  // Compare with half-circle == PI using sign of sin().
    }
    
     /**
     * Computes the distance on the sphere between the point p and the line segment start to end.
     *
     * @param p the point to be measured
     * @param start the beginning of the line segment
     * @param end the end of the line segment
     * @return the distance in meters (assuming spherical earth)
     */
     
    public static function distanceToLine($p, $start, $end) {
        if ($start == $end) {
            return SphericalUtil::computeDistanceBetween($end, $p);
        }

        $s0lat = deg2rad($p['lat']);
        $s0lng = deg2rad($p['lng']);
        $s1lat = deg2rad($start['lat']);
        $s1lng = deg2rad($start['lng']);
        $s2lat = deg2rad($end['lat']);
        $s2lng = deg2rad($end['lng']);

        $s2s1lat = $s2lat - $s1lat;
        $s2s1lng = $s2lng - $s1lng;
        $u = (($s0lat - $s1lat) * $s2s1lat + ($s0lng - $s1lng) * $s2s1lng)
                / ($s2s1lat * $s2s1lat + $s2s1lng * $s2s1lng);
        if ($u <= 0) {
            return SphericalUtil::computeDistanceBetween($p, $start);
        }
        if ($u >= 1) {
            return SphericalUtil::computeDistanceBetween($p, $end);
        }
        $su = ['lat' => $start['lat'] + $u * ($end['lat'] - $start['lat']), 'lng' => $start['lng'] + $u * ($end['lng'] - $start['lng'])];
        return SphericalUtil::computeDistanceBetween($p, $su);
    }
    
    /**
     * Decodes an encoded path string into a sequence of LatLngs.
     */
    public static function decode($encodedPath) {
        
        $len = strlen( $encodedPath ) -1;
        // For speed we preallocate to an upper bound on the final length, then
        // truncate the array before returning.
        $path = [];
        $index = 0;
        $lat = 0;
        $lng = 0;

        while( $index < $len) {
            $result = 1;
            $shift = 0;
            $b;
            do {
                $b = ord($encodedPath[$index++]) - 63 - 1;
                $result += $b << $shift;
                $shift += 5;
            } while ($b >= hexdec("0x1f"));
            
            $lat += ($result & 1) != 0 ? ~($result >> 1) : ($result >> 1);

            $result = 1;
            $shift = 0;
            do {
                $b = ord($encodedPath[$index++]) - 63 - 1;
                $result += $b << $shift;
                $shift += 5;
            } while ($b >= hexdec("0x1f"));
            $lng += ($result & 1) != 0 ? ~($result >> 1) : ($result >> 1);
            
            array_push($path, ['lat' => $lat * 1e-5, 'lng' => $lng * 1e-5]);
        }

        return $path;
    }    
    
    
    /**
     * Encodes a sequence of LatLngs into an encoded path string.
     */
    public static function encode($path) {
        
        $lastLat = 0;
        $lastLng = 0;

        $result = '';

        foreach( $path as $point ) {
            $lat = round( $point['lat'] * 1e5);
            $lng = round( $point['lng'] * 1e5);
            
            $dLat = $lat - $lastLat;
            $dLng = $lng - $lastLng;

            $result.=self::enc($dLat);
            $result.=self::enc($dLng);

            $lastLat = $lat;
            $lastLng = $lng;
        }
        return $result;
    }    
    
    
    
    private static function enc($v) {

        $v = $v < 0 ? ~($v << 1) : $v << 1;

        $result = '';
        
        while ($v >= 0x20) {
            $result.= chr((int) ((0x20 | ($v & 0x1f)) + 63));
            $v >>= 5;
        }
        
        $result.=chr((int) ($v + 63));
        
        return $result;
    }    
    
}

?>
