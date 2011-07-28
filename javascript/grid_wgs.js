//--------------------------------------------------------------------------------
//	$Id: grid_wgs.js,v 1.6 2011/02/02 20:37:31 wolf Exp wolf $
//--------------------------------------------------------------------------------
//	Erklärung:	http://www.netzwolf.info/kartografie/openlayers/wgsgrid
//--------------------------------------------------------------------------------
//	Fragen, Wuensche, Bedenken, Anregungen?
//	<openlayers(%40)netzwolf.info>
//--------------------------------------------------------------------------------

OpenLayers.Layer.GridWGS = OpenLayers.Class (OpenLayers.Layer.Vector, {

	initialize: function (name, options){
		OpenLayers.Layer.Vector.prototype.initialize.apply(this, [name, options]);
	},

	gridSizeText: null,

	gridSizeDiv: null,

	zoomUnits: null,

	//---------------------------------------------------------
	//	Find matching grid unit (minutes) or return null
	//---------------------------------------------------------

	getGridUnit: function (distance) {

		if (this.zoomUnits) return this.zoomUnits[this.map.zoom];

		for (var i=0; i<this.gridUnits.length; i++) {
			if (distance<this.gridUnits[i])
				return this.gridUnits[i];
		}
		return null;
	},

	// in Winkelsekunden
	gridUnits: [
		//3,		// 0.05'
		6, 12, 30,	// 0.1'  0.2'  0.5'
		1*60, 2*60, 3*60, 5*60, 10*60, 20*60, 30*60,
		1*3600, 2*3600, 3*3600, 4*3600, 6*3600, 10*3600, 15*3600, 30*3600, 45*3600],

	gridPixelDistance: 100,

	//---------------------------------------------------------
	//	Format gridsize
	//---------------------------------------------------------

	dd: function (n) {
		return parseInt(n)>=10 ? n : '0'+n;
	},

	formatGridSize: function (s) {
		var h = Math.floor(s/3600);
		var m = s%3600/60;
		return (h?h+"°":"")+(m?m+"'":"");
	},

	formatDegrees: function (s, unit) {
		return Math.floor(s/3600) + "°"
			+ (unit%3600?this.dd(s%3600/60)+"'":"")
	},

	//---------------------------------------------------------
	//	Draw grid on move or zoom
	//---------------------------------------------------------

	moveTo: function (bounds, zoomChanged, dragging) {

		//---------------------------------------------------------
		//	but not while dragging
		//---------------------------------------------------------

		if (dragging) return;

		//---------------------------------------------------------
		//	Remove old grid
		//---------------------------------------------------------

		this.destroyFeatures();

		//---------------------------------------------------------
		//	Transform center and border to geogr. Coordinates
		//---------------------------------------------------------

		var mapBounds = bounds.clone().
			transform(this.map.getProjectionObject(), this.map.displayProjection);

		//---------------------------------------------------------
		//	Grid unit
		//---------------------------------------------------------

		var seconds = 3600 * (mapBounds.top-mapBounds.bottom);

		var unit = this.getGridUnit (seconds / this.map.getSize().h * this.gridPixelDistance);

		//---------------------------------------------------------
		//	Grid size display object
		//	(TODO: create a OpenLayers.Control-Object)
		//---------------------------------------------------------

		if (this.gridSizeText && !this.gridSizeDiv) {
			this.gridSizeDiv=OpenLayers.Util.createDiv(this.id);
			this.gridSizeDiv.className='olControlGridWGS';
			this.gridSizeDiv.style.zIndex=map.Z_INDEX_BASE['Control']+ map.controls.length;
			this.gridSizeDiv.setAttribute("unselectable","on");
			this.map.viewPortDiv.appendChild (this.gridSizeDiv);
		}

		//---------------------------------------------------------
		//	Hide grid size (if configured)
		//---------------------------------------------------------

		if (this.gridSizeDiv) this.gridSizeDiv.style.display='none';

		//---------------------------------------------------------
		//	Create new grid
		//---------------------------------------------------------

		if (unit) {

			//---------------------------------------------------------
			//	Compute grid
			//---------------------------------------------------------

			var x1 = Math.max (-180.0*3600, Math.ceil  (3600 * mapBounds.left  / unit) * unit);
			var x2 = Math.min (+180.0*3600, Math.floor (3600 * mapBounds.right / unit) * unit);
			var y1 = Math.max ( -90.0*3600, Math.ceil  (3600 * mapBounds.bottom/ unit) * unit);
			var y2 = Math.min ( +90.0*3600, Math.floor (3600 * mapBounds.top   / unit) * unit);

			var features = [];

			//---------------------------------------------------------
			//	Vertical lines
			//---------------------------------------------------------

			for (var x=x1; x<=x2; x+= unit) {
				var p1 = new OpenLayers.LonLat (x/3600, Math.min(+85, mapBounds.top))
					.transform(map.displayProjection, map.getProjectionObject());
				var p2 = new OpenLayers.LonLat (x/3600, Math.max(-85, mapBounds.bottom))
					.transform(map.displayProjection, map.getProjectionObject());
				v1 = new OpenLayers.Feature.Vector ( new OpenLayers.Geometry.LineString( [
					new OpenLayers.Geometry.Point (p1.lon, p1.lat),
					new OpenLayers.Geometry.Point (p2.lon, p2.lat)
				]));
				v1.style={
					label: this.formatDegrees (Math.abs(x), unit),
					labelAlign: "lt",
					strokeColor: "#666666",
					strokeWidth: 1,
					strokeOpacity: 0.8
				};
				features.push (v1);
			}

			//---------------------------------------------------------
			//	Horizontal lines
			//---------------------------------------------------------

			for (var y=y1; y<=y2; y+=unit) {
				var p1 = new OpenLayers.LonLat (Math.max(-180, mapBounds.left), y/3600)
					.transform(map.displayProjection, map.getProjectionObject());
				var p2 = new OpenLayers.LonLat (Math.min(+180, mapBounds.right), y/3600)
					.transform(map.displayProjection, map.getProjectionObject());
				v1 = new OpenLayers.Feature.Vector ( new OpenLayers.Geometry.LineString( [
					new OpenLayers.Geometry.Point (p1.lon, p1.lat),
					new OpenLayers.Geometry.Point (p2.lon, p2.lat)
				]));
				v1.style={
					label: this.formatDegrees (Math.abs(y), unit),
					labelAlign: "lb",
					strokeColor: "#666666",
					strokeWidth: 1,
					strokeOpacity: 0.8
				};
				features.push (v1);
			}

			//---------------------------------------------------------
			//	Add grid lines to vector layer
			//---------------------------------------------------------

			this.addFeatures(features);

			//---------------------------------------------------------
			//	Display grid size
			//---------------------------------------------------------

			if (this.gridSizeDiv) {
				this.gridSizeDiv.innerHTML = OpenLayers.String.format(this.gridSizeText,
					{grid: this.formatGridSize(unit)});
				this.gridSizeDiv.style.display=null;
			}
		}

		//---------------------------------------------------------
		//	Superclass
		//---------------------------------------------------------

		OpenLayers.Layer.Vector.prototype.moveTo.apply(this,arguments);
	},

	CLASS_NAME: "OpenLayers.Layer.GridWGS"
});

//--------------------------------------------------------------------------------
//	$Id: grid_wgs.js,v 1.6 2011/02/02 20:37:31 wolf Exp wolf $
//--------------------------------------------------------------------------------
