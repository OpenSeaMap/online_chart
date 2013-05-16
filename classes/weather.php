<?PHP

/******************************************************************************
 Weather service for OpenSeaMap
 Required: PHP 5 
 author Olaf Hannemann
 license GPL
 version 0.1.0

 This file is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This file is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License (http://www.gnu.org/licenses/) for more details.
*******************************************************************************/
// Create a new instance for translation
$utc = new Weather();

class Weather {

	function getWeatherUtc($time) {
		$value = file_get_contents("http://openportguide.org/tiles/actual/wind_vector/" .$time ."/time.txt");

		return trim($value);
	}
}

?> 