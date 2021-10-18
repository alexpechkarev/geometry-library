## Geometry Library Google Maps API V3 
PHP Geometry Library provides utility functions for the computation of geometric data on the surface of the Earth. Code ported from Google [Maps Android API](https://github.com/googlemaps/android-maps-utils/).


Features
------------
* [Spherical](https://developers.google.com/maps/documentation/javascript/reference#spherical) contains spherical geometry utilities allowing you to compute angles, distances and areas from latitudes and longitudes.
* [Poly](https://developers.google.com/maps/documentation/javascript/reference#poly) utility functions for computations involving polygons and polylines.
* [Encoding](https://developers.google.com/maps/documentation/javascript/reference#encoding) utilities for polyline encoding and decoding.

Dependency
------------
* [PHP 5](http://php.net/)



Installation
------------

Issue following command:

```php
composer require alexpechkarev/geometry-library:1.0.4
```

Alternatively  edit composer.json by adding following line and run **`composer update`**
```php
"require": { 
		....,
		"alexpechkarev/geometry-library":"1.0.4",
	
	},
```

Usage
------------

Here is an example of using GeometryLibrary:
```php
$response =  \GeometryLibrary\SphericalUtil::computeHeading(
                ['lat' => 25.775, 'lng' => -80.190], // from array [lat, lng]
                ['lat' => 21.774, 'lng' => -80.190]); // to array [lat, lng]
  echo $response; // -180
  
$response = \GeometryLibrary\SphericalUtil::computeDistanceBetween(
              ['lat' => 25.775, 'lng' => -80.190], //from array [lat, lng]
              ['lat' => 21.774, 'lng' => -80.190]); // to array [lat, lng]
              
  echo $response; // 444891.52998049          
  
  
$response =  \GeometryLibrary\PolyUtil::isLocationOnEdge(
              ['lat' => 25.774, 'lng' => -80.190], // point array [lat, lng]
              [ // poligon arrays of [lat, lng]
                ['lat' => 25.774, 'lng' => -80.190], 
                ['lat' => 18.466, 'lng' => -66.118], 
                ['lat' => 32.321, 'lng' => -64.757]
              ])  ;
              
  echo $response; // true
  
  
  
$response =  \GeometryLibrary\PolyUtil::isLocationOnPath(
              ['lat' => 25.771, 'lng' => -80.190], // point array [lat, lng]
             [ // poligon arrays of [lat, lng]
              ['lat' => 25.774, 'lng' => -80.190], 
              ['lat' => 18.466, 'lng' => -66.118], 
              ['lat' => 32.321, 'lng' => -64.757]
             ]);  
             
  echo $response; // false  
  
$response =  \GeometryLibrary\PolyUtil::containsLocation(
              ['lat' => 23.886, 'lng' => -65.269], // point array [lat, lng]
             [ // poligon arrays of [lat, lng]
                ['lat' => 25.774, 'lng' => -80.190], 
                ['lat' => 18.466, 'lng' => -66.118], 
                ['lat' => 32.321, 'lng' => -64.757]
             ]);  
             
  echo $response; // false    
  
$response =  \GeometryLibrary\PolyUtil::distanceToLine(
              ['lat' => 61.387002, 'lng' => 23.890636], // point array [lat, lng]
              ['lat' => 61.487002, 'lng' => 23.790636], // line startpoint array [lat, lng]
              ['lat' => 60.48047, 'lng' => 22.052754] // line endpoint array [lat, lng]
             );  
             
  echo $response; // 12325.124046196 in meters
  
$response =  \GeometryLibrary\PolyUtil::encode(
              [ 
                ['lat' => 38.5, 'lng' => -120.2], 
                ['lat' => 40.7, 'lng' => -120.95], 
                ['lat' => 43.252, 'lng' => -126.453]
              ]);
              
  echo $response; // '_p~iF~ps|U_ulLnnqC_mqNvxq`@'
  
  
$response =  \GeometryLibrary\PolyUtil::decode('_p~iF~ps|U_ulLnnqC_mqNvxq`@');  

  echo $response; /** array (size=3)
                        0 => 
                          array (size=2)
                            'lat' => float 38.5
                            'lng' => float -120.2
                        1 => 
                          array (size=2)
                            'lat' => float 40.7
                            'lng' => float -120.95
                        2 => 
                          array (size=2)
                            'lat' => float 43.252
                            'lng' => float -126.453
                   */
  
```

Available methods
------------
## PolyUtil class
* [`containsLocation($point, $polygon, $geodesic = false)`](#containsLocation)
* [`isLocationOnEdge($point, $polygon, $tolerance = self::DEFAULT_TOLERANCE, $geodesic = true)`](#isLocationOnEdge)
* [`isLocationOnPath($point, $polyline, $tolerance = self::DEFAULT_TOLERANCE, $geodesic = true)`](#isLocationOnPath)
* [`distanceToLine($p, $start, $end)`](#distanceToLine)
* [`decode($encodedPath)`](#decode)
* [`encode($path)`](#encode)

## SphericalUtil class
* [`computeHeading($from, $to)`](#computeHeading)
* [`computeOffset($from, $distance, $heading)`](#computeOffset)
* [`computeOffsetOrigin($to, $distance,  $heading)`](#computeOffsetOrigin)
* [`interpolate($from, $to, $fraction)`](#interpolate)
* [`computeDistanceBetween( $from, $to)`](#computeDistanceBetween)
* [`computeLength($path)`](#computeLength)
* [`computeArea($path)`](#computeArea)
* [`computeSignedArea($path)`](#computeSignedArea)


---

<a name="containsLocation"></a>
**`containsLocation( $point, $polygon, $geodesic = false )`** - To find whether a given point falls within a polygon

* `$point` -  ['lat' => 38.5, 'lng' => -120.2 ]
* `$polygon` - [ ['lat' => 38.5, 'lng' => -120.2], ['lat' => 40.7, 'lng' => -120.95], ['lat' => 43.252, 'lng' => -126.453]]
* `$geodesic` - boolean

Returns boolean

```php

$response =  \GeometryLibrary\PolyUtil::containsLocation(
              ['lat' => 23.886, 'lng' => -65.269], // point array [lat, lng]
             [ // poligon arrays of [lat, lng]
                ['lat' => 25.774, 'lng' => -80.190], 
                ['lat' => 18.466, 'lng' => -66.118], 
                ['lat' => 32.321, 'lng' => -64.757]
             ]);  
             
  echo $response; // false

```
---


<a name="isLocationOnEdge"></a>
**`isLocationOnEdge( $point, $polygon, $tolerance = self::DEFAULT_TOLERANCE, $geodesic = true )`** - To determine whether a point falls on or near a polyline, or on or near the edge of a polygon, within a specified tolerance in meters.

* `$point` -  ['lat' => 25.774, 'lng' => -80.190 ]
* `$polygon` -  [ ['lat' => 38.5, 'lng' => -120.2], ['lat' => 40.7, 'lng' => -120.95], ['lat' => 43.252, 'lng' => -126.453]]
* `$tolerance` -  tolerance value in degrees
* `$geodesic` - boolean

Returns boolean

```php

$response =  \GeometryLibrary\PolyUtil::isLocationOnEdge(
              ['lat' => 25.774, 'lng' => -80.190], // point array [lat, lng]
              [ // poligon arrays of [lat, lng]
                ['lat' => 25.774, 'lng' => -80.190], 
                ['lat' => 18.466, 'lng' => -66.118], 
                ['lat' => 32.321, 'lng' => -64.757]
              ])  ;
              
  echo $response; // true

```
---

<a name="isLocationOnPath"></a>
**`isLocationOnPath( $point, $polygon, $tolerance = self::DEFAULT_TOLERANCE, $geodesic = true )`** - To determine whether a point falls on or near a polyline, within a specified tolerance in meters

* `$point` -  ['lat' => 25.774, 'lng' => -80.190 ]
* `$polygon` -  [ ['lat' => 38.5, 'lng' => -120.2], ['lat' => 40.7, 'lng' => -120.95], ['lat' => 43.252, 'lng' => -126.453]]
* `$tolerance` -  tolerance value in degrees
* `$geodesic` - boolean

Returns boolean

```php

$response =  \GeometryLibrary\PolyUtil::isLocationOnPath(
              ['lat' => 25.774, 'lng' => -80.190], // point array [lat, lng]
              [ // poligon arrays of [lat, lng]
                ['lat' => 25.774, 'lng' => -80.190], 
                ['lat' => 18.466, 'lng' => -66.118], 
                ['lat' => 32.321, 'lng' => -64.757]
              ])  ;
              
  echo $response; // true

```
---

<a name="distanceToLine"></a>
**`distanceToLine( $p, $start, $end )`** - To calculate distance from a point to line start->end on sphere.

* `$p` -  ['lat' => 61.387002, 'lng' => 23.890636]
* `$start` -  ['lat' => 61.487002, 'lng' => 23.790636]
* `$end` -  ['lat' => 60.48047, 'lng' => 22.052754]

Returns distance from a point to line

```php

$response =  \GeometryLibrary\PolyUtil::distanceToLine(
              ['lat' => 61.387002, 'lng' => 23.890636], // point array [lat, lng]
              ['lat' => 61.487002, 'lng' => 23.790636], // line startpoint array [lat, lng]
              ['lat' => 60.48047, 'lng' => 22.052754] // line endpoint array [lat, lng]
             );  
             
  echo $response; // 12325.124046196 in meters

```
---

<a name="decode"></a>
**`decode( $encodedPath )`** - Decodes an encoded path string into a sequence of LatLngs.

* `$encodedPath` - string '_p~iF~ps|U_ulLnnqC_mqNvxq`@'

Returns array

```php

$response =  \GeometryLibrary\PolyUtil::decode('_p~iF~ps|U_ulLnnqC_mqNvxq`@');  

  echo $response; /** array (size=3)
                        0 => 
                          array (size=2)
                            'lat' => float 38.5
                            'lng' => float -120.2
                        1 => 
                          array (size=2)
                            'lat' => float 40.7
                            'lng' => float -120.95
                        2 => 
                          array (size=2)
                            'lat' => float 43.252
                            'lng' => float -126.453
                   */

```
---

<a name="encode"></a>
**`encode( $path )`** - Encodes a sequence of LatLngs into an encoded path string.

* `$path` -  [ ['lat' => 38.5, 'lng' => -120.2], ['lat' => 40.7, 'lng' => -120.95], ['lat' => 43.252, 'lng' => -126.453] ]

Returns string

```php

$response =  \GeometryLibrary\PolyUtil::encode(
              [ 
                ['lat' => 38.5, 'lng' => -120.2], 
                ['lat' => 40.7, 'lng' => -120.95], 
                ['lat' => 43.252, 'lng' => -126.453]
              ]);
              
  echo $response; // '_p~iF~ps|U_ulLnnqC_mqNvxq`@'

```
---

<a name="computeHeading"></a>
**`computeHeading( $from, $to )`** - Returns the heading from one LatLng to another LatLng.

* `$from` -  ['lat' => 38.5, 'lng' => -120.2]
* `$to` -  ['lat' => 40.7, 'lng' => -120.95]

Returns int

```php

$response =  \GeometryLibrary\SphericalUtil::computeHeading(
              ['lat' => 25.775, 'lng' => -80.190], 
              ['lat' => 21.774, 'lng' => -80.190]));
              
  echo $response; // -180

```
---

<a name="computeOffset"></a>
**`computeOffset( $from, $distance, $heading )`** - Returns the LatLng resulting from moving a distance from an origin in the specified heading.

* `$from` -  ['lat' => 38.5, 'lng' => -120.2]
* `$distance` - number, the distance to travel
* `$heading` - number, the heading in degrees clockwise from north

Returns array

```php

$response =  \GeometryLibrary\SphericalUtil::computeOffset(['lat' => 25.775, 'lng' => -80.190], 152, 120);
              
  echo $response; /** array (size=2)
                      'lat' => float 25.774316510639
                      'lng' => float -80.188685385944
                  */

```
---

<a name="computeOffsetOrigin"></a>
**`computeOffsetOrigin( $from, $distance, $heading )`** - Returns the location of origin when provided with a LatLng destination, meters travelled and original heading. Headings are expressed in degrees clockwise from North. 

* `$from` -  ['lat' => 38.5, 'lng' => -120.2]
* `$distance` - number, the distance to travel
* `$heading` - number, the heading in degrees clockwise from north

Returns array 

```php

$response =  \GeometryLibrary\SphericalUtil::computeOffsetOrigin(['lat' => 25.775, 'lng' => -80.190], 152, 120);
              
  echo $response; /** array (size=2)
                        'lat' => float 14.33435503928
                        'lng' => float -263248.24242931
                  */

```
---

<a name="interpolate"></a>
**`interpolate( $from, $to, $fraction )`** - Returns the LatLng which lies the given fraction of the way between the origin LatLng and the destination LatLng.

* `$from` -  ['lat' => 38.5, 'lng' => -120.2]
* `$to` -  ['lat' => 38.5, 'lng' => -120.2]
* `$fraction` - number, a fraction of the distance to travel

Returns array

```php

$response =  \GeometryLibrary\SphericalUtil::interpolate(['lat' => 25.775, 'lng' => -80.190], 
                                                          ['lat' => 26.215, 'lng' => -81.218], 2);
              
  echo $response; /** array (size=2)
                      'lat' => float 26.647635362403
                      'lng' => float -82.253737943391
                  */

```
---

<a name="computeDistanceBetween"></a>
**`computeDistanceBetween( $from, $to )`** - Returns the distance, in meters, between two LatLngs. You can optionally specify a custom radius. The radius defaults to the radius of the Earth.

* `$from` - ['lat' => 38.5, 'lng' => -120.2]
* `$to` - ['lat' => 38.5, 'lng' => -120.2]

Returns float

```php

$response =  \GeometryLibrary\SphericalUtil::computeDistanceBetween(['lat' => 25.775, 'lng' => -80.190], ['lat' => 26.215, 'lng' => -81.218]);
              
  echo $response; //float 113797.92421349

```
---

<a name="computeLength"></a>
**`computeLength( $path )`** - Returns the length of the given path, in meters, on Earth.

* `$path` - [ ['lat' => 38.5, 'lng' => -120.2], ['lat' => 40.7, 'lng' => -120.95], ['lat' => 43.252, 'lng' => -126.453] ]

Returns float

```php

$response =  \GeometryLibrary\SphericalUtil::computeLength([ 
                ['lat' => 38.5, 'lng' => -120.2], 
                ['lat' => 40.7, 'lng' => -120.95], 
                ['lat' => 43.252, 'lng' => -126.453]
              ]);
              
  echo $response; //float 788906.98459431

```
---

<a name="computeArea"></a>
**`computeArea( $path )`** - Returns the area of a closed path.

* `$path` - [ ['lat' => 38.5, 'lng' => -120.2], ['lat' => 40.7, 'lng' => -120.95], ['lat' => 43.252, 'lng' => -126.453] ]

Returns float

```php

$response =  \GeometryLibrary\SphericalUtil::computeArea([ 
                ['lat' => 38.5, 'lng' => -120.2], 
                ['lat' => 40.7, 'lng' => -120.95], 
                ['lat' => 43.252, 'lng' => -126.453]
              ]);
              
  echo $response; //float 44766785529.143

```
---

<a name="computeSignedArea"></a>
**`computeSignedArea( $path )`** - Returns the signed area of a closed path.

* `$path` - [ ['lat' => 38.5, 'lng' => -120.2], ['lat' => 40.7, 'lng' => -120.95], ['lat' => 43.252, 'lng' => -126.453] ]

Returns float

```php

$response =  \GeometryLibrary\SphericalUtil::computeSignedArea([ 
                ['lat' => 38.5, 'lng' => -120.2], 
                ['lat' => 40.7, 'lng' => -120.95], 
                ['lat' => 43.252, 'lng' => -126.453]
              ]);
              
  echo $response; //float 44766785529.143

```
---

Support
-------

[Please open an issue on GitHub](https://github.com/alexpechkarev/geometry-library/issues)


License
-------

Geometry Library Google Maps API V3 is released under the MIT License. See the bundled
[LICENSE](https://github.com/alexpechkarev/geometry-library/blob/master/LICENSE)
file for details.
