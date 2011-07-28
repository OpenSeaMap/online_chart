//--------------------------------------------------------------------------------
//	$Id: mouseposition_dm.js,v 1.3 2010/08/14 23:34:47 wolf Exp wolf $
//--------------------------------------------------------------------------------
//	Erklärung:	http://www.netzwolf.info/kartografie/openlayers/dm
//--------------------------------------------------------------------------------
//	Fragen, Wuensche, Bedenken, Anregungen?
//	<openlayers(%40)netzwolf.info>
//--------------------------------------------------------------------------------

OpenLayers.Control.MousePositionDM = OpenLayers.Class (OpenLayers.Control.MousePosition, {

	format_f: function (number, length, fraclength) {
		var text = number.toFixed(fraclength);
		while (text.length < length) text = "0"+text;
		return text;
	},

	formatOutput: function (lonLat) {
		var ns = lonLat.lat >= 0 ? 'N' : 'S';
		var we = lonLat.lon >= 0 ? 'E' : 'W';
		var lon_m = Math.abs(lonLat.lon*60).toFixed(3);
		var lat_m = Math.abs(lonLat.lat*60).toFixed(3);
		var lon_d = Math.floor (lon_m/60);
		var lat_d = Math.floor (lat_m/60);
		lon_m -= lon_d*60;
		lat_m -= lat_d*60;

		return	"Zoom:" + zoom + " " + ns + lat_d + "&#176;" + this.format_f(lat_m,6,3) + "'" + "&#160;" +
			we + lon_d + "&#176;" + this.format_f(lon_m,6,3) + "'" ;
       	},

	CLASS_NAME:"OpenLayers.Control.MousePositionDM"
    }
);

//--------------------------------------------------------------------------------
//	$Id: mouseposition_dm.js,v 1.3 2010/08/14 23:34:47 wolf Exp wolf $
//--------------------------------------------------------------------------------
