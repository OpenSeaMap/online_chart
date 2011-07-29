/**
 * @requires OpenLayers/Control.js
 */

/**
 * Class: OpenLayers.Control.NauticalRoute
 * The NauticalRoute control allows sailors to draw a route as a serie of
 * waypoints. The control outputs course and distance (orthodromic as well as
 * loxodromic).
 *
 * Inherits from:
 *  - <OpenLayers.Control>
 */
OpenLayers.Control.NauticalRoute = OpenLayers.Class(OpenLayers.Control, {

    /**
     * Constant: EARTH_RADIUS
     * {Float} An approximation of earth radius expressed in kilometers
     */
    EARTH_RADIUS: 6371.221,

    /**
     * Property: handler
     * {<OpenLayers.Control.Handler>}
     */
    handler: null,

    /**
     * Property: outputFields
     * {Array} An associative array which old references to relevant cells of
     * the output table.
     */
    outputFields: [],

    /** 
     * Property: closeDiv
     * {DOMElement} the closer image
     */
    closeDiv: null,

    /** 
     * Property: closeDiv
     * {DOMElement} the title bar
     */
    titleDiv: null,

    /** 
     * Property: prefDiv
     * {DOMElement} the preference section
     */
    prefDiv: null,

    /** 
     * Property: exportDiv
     * {DOMElement} the export section
     */
    exportDiv: null,

    /** 
     * Property: controlDiv
     * {DOMElement} the control panel section
     */
    controlDiv: null,

    /** 
     * Property: waypoints
     * {Array} Waypoint list
     */
    waypoints: [],

    /** 
     * Property: sumDisanceOrtho
     * {Float} Total orthodromic distance for the current route
     */
    sumDisanceOrtho: 0,

    /** 
     * Property: sumDisanceLoxo
     * {Float} Total loxodromic distance for the current route
     */
    sumDisanceLoxo: 0,

    /** 
     * Property: waypoints
     * {Array} Waypoint list
     */
    waypoints: [],

    /** 
     * Property: formats
     * {Array} Export format list
     */
    formats: [],

    /** 
     * Property: preferences
     * {Object} holds everything about user preferences
     */
    preferences: {
        'units': {
            'label': 'Unit system',
            'values': {
                'nm': 'Nautical miles',
                'km': 'Metric'
            },
            'selected': 'nm'
        },
        'format': {
            'label': 'Coordinates format',
            'values': {
                'dd': 'Decimal degrees',
                'dms': 'Degree/minute/second'
            },
            'selected': 'dd'
        },
        'order': {
            'label': 'Coordinates order',
            'values': {
                'latlon': 'Latitude, longitude',
                'lonlat': 'Longitude, latitude'
            },
            'selected': 'latlon'
        },
    },

    /** 
     * Property: events
     * {<OpenLayers.Events>} custom event manager 
     */
    events: null,

    /** 
     * Property: dragEvents
     * {<OpenLayers.Events>} custom event manager 
     */
    dragEvents: null,

    /**
     * Property: delay
     * {Number} Number of milliseconds between clicks before the event is
     *     considered a double-click.  The "measurepartial" event will not
     *     be triggered if the sketch is completed within this time.  This
     *     is required for IE where creating a browser reflow (if a listener
     *     is modifying the DOM by displaying the measurement values) messes
     *     with the dblclick listener in the sketch handler.
     */
    partialDelay: 300,

    /**
     * Property: delayedTrigger
     * {Number} Timeout id of trigger for measurepartial.
     */
    delayedTrigger: null,

    /** 
     * APIProperty: defaultHidden
     * {Boolean} Wait for an explicit call to the 'show' method if true.
     * Defaults to 'false'.
     */
    defaultHidden: false,

    /** 
     * APIProperty: size
     * {<OpenLayers.Size>} The size of the output box.
     */
    size: new OpenLayers.Size(300, 250),

    /** 
     * APIProperty: position
     * {<OpenLayers.Pixel>} The position of the output box. Use it this 
     * property to initialize the control or to know the current position of
     * the control.
     */
    position: null,

    /** 
     * APIProperty: exportUrl
     * {String} The URL where exported data should be posted.
     */
    exportUrl: '../classes/export.php',

    /**
     * Constructor: OpenLayers.Control.NauticalRoute
     * 
     * Parameters:
     * options - {Object} Options for control.
     */
    initialize: function(options) {
        OpenLayers.Control.prototype.initialize.apply(this, arguments);
        this.allowSelection = true;
        this.handler = new OpenLayers.Handler.Path(this, {
                                                    create: function() {
                                                        this.clearWaypoints(false);
                                                    },
                                                    point: this.addWaypoint
                                                }, {persist: true});
        // Set i18n now (not available during class declaration)
        for (pkey in this.preferences) {
            this.preferences[pkey].label = OpenLayers.i18n(
                                        this.preferences[pkey].label);
            for (vkey in this.preferences[pkey].values) {
                this.preferences[pkey].values[vkey] = OpenLayers.i18n(
                                        this.preferences[pkey].values[vkey]);
            }
        }

        this.formats['csv'] = OpenLayers.i18n('CSV (spreadsheet)');
        this.formats['kml'] = OpenLayers.i18n('KML (Google Earth)');
        //this.formats['gpx'] = OpenLayers.i18n('GPX (GPS receiver)');//FIXME: GPX Format seems read-only?
        this.formats['gml'] = OpenLayers.i18n('GML (standard vector)');

        this.title = OpenLayers.i18n('Course and distance');
    },

    /**
     * Method: destroy
     */
    destroy: function() {
        this.events.destroy();
        this.events = null;
        this.dragEvents.destroy();
        this.dragEvents = null;

        if (this.closeDiv) {
            OpenLayers.Event.stopObservingElement(this.closeDiv); 
            this.div.removeChild(this.closeDiv);
        }
        this.closeDiv = null;

        if (this.titleDiv) {
            this.div.removeChild(this.titleDiv);
        }
        this.titleDiv = null;

        if (this.controlDiv) {
            this.div.removeChild(this.controlDiv);
            OpenLayers.Event.stopObservingElement($(this.id+'_control_clear'));
        }
        this.controlDiv = null;

        if (this.prefDiv) {
            for (pkey in this.preferences) {
                for (vkey in this.preferences[pkey].values) {
                    OpenLayers.Event.stopObservingElement(
                                            $(this.id+'_pref_'+pkey+'_'+vkey));
                }
            }
            this.div.removeChild(this.prefDiv);
        }
        this.prefDiv = null;

        OpenLayers.Control.prototype.destroy.apply(this, arguments);
    },

    /**
     * Method: draw
     * Returns:
     * {DOMElement} 
     */
    draw: function() {
        // Default position is 10% appart from top right corner
        if (!this.position) {
            var x = parseInt(this.map.getSize().w * 0.9) - this.size.w;
            var y = parseInt(this.map.getSize().h * 0.1);
            this.position = new OpenLayers.Pixel(x > 0 ? x : 10, y);
        }

        OpenLayers.Control.prototype.draw.apply(this, arguments);

        if (this.defaultHidden) {
            OpenLayers.Element.hide(this.div);
        }
        this.div.style.width = this.size.w+"px";

        this.events = new OpenLayers.Events(this, this.div, null, true);
        this.events.on({
            "mousedown": this.onmousedown,
            "mousemove": this.onmousemove,
            "mouseup": this.onmouseup,
            "click": this.onclick,
            "mouseout": this.onmouseout,
            "dblclick": this.ondblclick,
            scope: this
        });

        this._drawWindowElements();
        this._drawWPTable();
        this._drawPrefSection();
        this._drawExportSection();

        return this.div;
    },

    // Utility function to draw section header
    _makeHeader: function(text, opened) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        OpenLayers.Element.addClass(div, 'olWindowToggleSection');
        if (opened) {
            OpenLayers.Element.addClass(div, 'olWindowToggleSectionOpened');
        }
        OpenLayers.Event.observe(div, "click",
            OpenLayers.Function.bindAsEventListener(function() {
                if (OpenLayers.Element.hasClass(this, 'olWindowToggleSectionOpened')) {
                    OpenLayers.Element.removeClass(this, 'olWindowToggleSectionOpened');
                    OpenLayers.Element.hide(this.nextSibling);
                } else {
                    OpenLayers.Element.addClass(this, 'olWindowToggleSectionOpened');
                    OpenLayers.Element.show(this.nextSibling);
                }}, div));
        return div;
    },

    _drawWPTable: function() {
        var table, thead, tbody, tr, td, th;
        table = document.createElement('table');
        this.div.appendChild(table);
        thead = document.createElement('thead');
        table.appendChild(thead);
        tbody = document.createElement('tbody');
        table.appendChild(tbody); //FIXME tbody should have a maximum size and overflow:scroll
        this.outputFields['tbody'] = tbody;

        // 1st thead line
        tr = document.createElement('tr');
        thead.appendChild(tr);
        th = document.createElement('th');
        th.innerHTML = OpenLayers.i18n('Start');
        tr.appendChild(th);
        td = document.createElement('td');
        td.colSpan = 3;
        this.outputFields['start'] = td;
        tr.appendChild(td);

        // 2nd thead line
        tr = document.createElement('tr');
        thead.appendChild(tr);
        th = document.createElement('th');
        th.innerHTML = OpenLayers.i18n('Finish');
        tr.appendChild(th);
        td = document.createElement('td');
        td.colSpan = 3;
        this.outputFields['finish'] = td;
        tr.appendChild(td);

        // 3rd thead line
        tr = document.createElement('tr');
        thead.appendChild(tr);
        th = document.createElement('th');
        th.innerHTML = OpenLayers.i18n('Loxodromic distance');
        th.style.width = "25%"
        tr.appendChild(th);
        td = document.createElement('td');
        this.outputFields['loxo'] = td;
        tr.appendChild(td);
        th = document.createElement('th');
        th.innerHTML = OpenLayers.i18n('Difference');
        th.rowSpan = 2;
        th.style.width = "20%"
        tr.appendChild(th);
        td = document.createElement('td');
        td.rowSpan = 2;
        this.outputFields['diff'] = td;
        tr.appendChild(td);

        // 4th thead line
        tr = document.createElement('tr');
        thead.appendChild(tr);
        th = document.createElement('th');
        th.innerHTML = OpenLayers.i18n('Orthodromic distance');
        tr.appendChild(th);
        td = document.createElement('td');
        this.outputFields['ortho'] = td;
        tr.appendChild(td);

        // Waypoint list header
        tr = document.createElement('tr');
        this.outputFields['wplistheader'] = tr; //FIXME should this be a caption?
        OpenLayers.Element.hide(tr);
        tbody.appendChild(tr);
        th = document.createElement('th');
        th.innerHTML = OpenLayers.i18n('Waypoint');
        tr.appendChild(th);
        th = document.createElement('th');
        th.innerHTML = OpenLayers.i18n('Course');
        tr.appendChild(th);
        th = document.createElement('th');
        th.innerHTML = OpenLayers.i18n('Distance');
        tr.appendChild(th);
        th = document.createElement('th');
        th.innerHTML = OpenLayers.i18n('Coordinates');
        tr.appendChild(th);

        // Control buttons
        this.controlDiv = document.createElement('div');
        this.controlDiv.id = this.id + "_control";
        this.controlDiv.className = this.displayClass + "Control";
        this.div.appendChild(this.controlDiv);
        var button = document.createElement('button');
        button.id = this.id + '_control_clear';
        button.innerHTML = OpenLayers.i18n('Clear');
        this.controlDiv.appendChild(button);
        OpenLayers.Event.observe(button, "click",
                                OpenLayers.Function.bindAsEventListener(
                                this.clearWaypoints, this));
        OpenLayers.Element.hide(this.controlDiv);
    },

    _drawPrefSection: function() {
        this.div.appendChild(this._makeHeader(OpenLayers.i18n('Preferences'), false));
        this.prefDiv = document.createElement('div');
        this.prefDiv.id = this.id + "_pref";
        this.prefDiv.className = this.displayClass + "Prefs";
        this.div.appendChild(this.prefDiv);
        for (pkey in this.preferences) {
            var fieldset, legend;
            fieldset = document.createElement('fieldset');
            this.prefDiv.appendChild(fieldset);
            legend = document.createElement('legend');
            legend.innerHTML = this.preferences[pkey].label;
            fieldset.appendChild(legend);
            for (vkey in this.preferences[pkey].values) {
                var input, label, br;
                input = document.createElement('input');
                input.id = this.id + '_pref_' + pkey + '_' + vkey;
                input.name = pkey;
                input.type = "radio";
                input.value = vkey;
                input.checked = vkey == this.preferences[pkey].selected;
                label = document.createElement('label');
                label.htmlFor = this.id + '_pref_' + pkey + '_' + vkey;
                label.innerHTML = ' ' + this.preferences[pkey].values[vkey];
                br = document.createElement('br');
                fieldset.appendChild(input);
                fieldset.appendChild(label);
                fieldset.appendChild(br);
                OpenLayers.Event.observe(input, "click",
                                OpenLayers.Function.bindAsEventListener(
                                this.selectPreference, this));
            }
        }
        OpenLayers.Element.hide(this.prefDiv);
    },

    _drawExportSection: function() {
        this.div.appendChild(this._makeHeader(OpenLayers.i18n('Export'), false));
        this.exportDiv = document.createElement('div');
        this.exportDiv.id = this.id + "_export";
        this.exportDiv.className = this.displayClass + "Export";
        this.div.appendChild(this.exportDiv);
        var fieldset, legend, input, label, form;

        fieldset = document.createElement('fieldset');
        this.exportDiv.appendChild(fieldset);
        legend = document.createElement('legend');
        legend.innerHTML = OpenLayers.i18n('Format');
        fieldset.appendChild(legend);

        for (fkey in this.formats) {
            input = document.createElement('input');
            input.id = this.id + '_export_format_' + fkey;
            input.name = 'export';
            input.type = "radio";
            input.value = fkey;
            label = document.createElement('label');
            label.htmlFor = this.id + '_export_format_' + fkey;
            label.innerHTML = ' ' + this.formats[fkey];
            fieldset.appendChild(input);
            fieldset.appendChild(label);
            fieldset.appendChild(document.createElement('br'));
        }

        form = document.createElement('form');
        form.id = this.id + '_export_form';
        form.method = 'post';
        form.action = this.exportUrl;
        this.exportDiv.appendChild(form);
        div = document.createElement('div');
        div.className = this.displayClass + "Control";
        form.appendChild(div);
        input = document.createElement('input');
        input.id = this.id + '_export_input_mimetype';
        input.name = 'mimetype';
        input.type = 'hidden';
        div.appendChild(input);
        input = document.createElement('input');
        input.id = this.id + '_export_input_filename';
        input.name = 'filename';
        input.type = 'hidden';
        div.appendChild(input);
        input = document.createElement('input');
        input.id = this.id + '_export_input_content';
        input.name = 'content';
        input.type = 'hidden';
        div.appendChild(input);
        input = document.createElement('input');
        input.id = this.id + '_export_button';
        input.type = 'button';
        input.value = OpenLayers.i18n('Get file');
        div.appendChild(input);
        OpenLayers.Event.observe(input, "click",
                                OpenLayers.Function.bindAsEventListener(
                                this.exportWaypoints, this));

        OpenLayers.Element.hide(this.exportDiv);
    },

    _drawWindowElements: function() {
        // Title bar (actually a drag handle)
        this.titleDiv = OpenLayers.Util.createDiv( this.id + "_title",
                                null, new OpenLayers.Size(this.size.w, 20));
        this.titleDiv.className = this.displayClass + "TitleBar";
        this.titleDiv.innerHTML = this.title;
        this.div.appendChild(this.titleDiv);
        this.div.style.paddingTop = "20px";
        this.dragEvents = new OpenLayers.Events(this, this.titleDiv, null, true, {includeXY: true});
        this.dragEvents.on({
            "mousedown": this.downTitleBar,
            "mousemove": this.moveWindow,
            "mouseup": this.stopDraggingWindow
        });

        // Close button
        this.closeDiv = OpenLayers.Util.createDiv( this.id + "_close",
                                        null, new OpenLayers.Size(17, 17));
        this.closeDiv.className = this.displayClass + "CloseBox";
        var bgImg = this.map.theme.replace('style.css', 'img/close.gif');
        this.closeDiv.style.backgroundImage = 'url(' + bgImg + ')';
        this.div.appendChild(this.closeDiv);
        OpenLayers.Event.observe(
            this.closeDiv, 
            "click", 
            OpenLayers.Function.bindAsEventListener(this.toggle, 
                                        this)
        );
    },

    /**
     * Method: redraw
     */
    redraw: function() {
        for (var wid in this.waypoints) {
            this.outputFields['tbody'].removeChild(this.waypoints[wid].row);
            this.waypoints[wid].row = this.renderWaypoint(wid);
        }
        this.updateRouteSummary();
    },

    /**
     * Method: updateRouteSummary
     */
    updateRouteSummary: function() {
        if (this.waypoints.length == 0) {
            this.outputFields['start'].innerHTML = "";
            this.outputFields['finish'].innerHTML = "";
            this.outputFields['loxo'].innerHTML = "";
            this.outputFields['ortho'].innerHTML = "";
            this.outputFields['diff'].innerHTML = "";
        } else {
            this.outputFields['start'].innerHTML = this.formatCoordinates(0);
            this.outputFields['finish'].innerHTML = this.formatCoordinates(
                                                    this.waypoints.length - 1);
            this.outputFields['loxo'].innerHTML = this.formatDistance(
                                                        this.sumDisanceLoxo);
            this.outputFields['ortho'].innerHTML = this.formatDistance(
                                                        this.sumDisanceOrtho);
            this.outputFields['diff'].innerHTML = this.formatDistance(
                        Math.abs(this.sumDisanceOrtho - this.sumDisanceLoxo));
        }
    },

    /**
     * Method: selectPreference
     */
    selectPreference: function(e) {
        var src = OpenLayers.Event.element(e);
        this.preferences[src.name].selected = src.value;
        this.redraw();
    },

    /**
     * Method: addWaypoint
     */
    addWaypoint: function(point, geometry) {
        var wid = this.waypoints.length;
        this.waypoints[wid] = {
            point: point.clone(),
            pointLL: point.clone().transform(map.projection, 
                                                    map.displayProjection),
        };
        if (wid == 0) {
            OpenLayers.Element.show(this.outputFields['wplistheader']);
            OpenLayers.Element.show(this.controlDiv);
        } else {
            var distOrtho = this.getDistanceOrtho(wid-1, wid);
            var distLoxo = this.getDistanceLoxo(wid-1, wid);
            if (distOrtho && distLoxo) {
                this.sumDisanceOrtho += distOrtho;
                this.sumDisanceLoxo += distLoxo;
            }
        }
        this.waypoints[wid].row = this.renderWaypoint(wid);
        this.updateRouteSummary();
    },

    /**
     * Method: renderWaypoint
     * 
     * Returns:
     * {DOMElement}
     */
    renderWaypoint: function(wid) {
        var tr, td;

        tr = document.createElement('tr');
        this.outputFields['tbody'].appendChild(tr);

        // 1st col: WP id
        td = document.createElement('td');
        td.innerHTML = wid;
        tr.appendChild(td);

        // 2nd col: course from previous WP, if any
        td = document.createElement('td');
        if (wid > 0) {
            td.innerHTML = this.formatCourse(this.getCourseLoxo(wid-1, wid));
        }
        tr.appendChild(td);

        // 3rd col: distance from previous WP, if any
        td = document.createElement('td');
        if (wid > 0) {
            td.innerHTML = this.formatDistance(this.getDistanceLoxo(wid-1, wid));
        }
        tr.appendChild(td);

        // 4th col: WP coordinates
        td = document.createElement('td');
        td.innerHTML = this.formatCoordinates(wid);
        tr.appendChild(td);

        return tr;
    },

    /**
     * Method: clearWaypoints
     */
    clearWaypoints: function(deep) {
        OpenLayers.Element.hide(this.outputFields['wplistheader']);
        OpenLayers.Element.hide(this.controlDiv);
        while (this.waypoints.length > 0) {
            var wp = this.waypoints.pop();
            this.outputFields['tbody'].removeChild(wp.row);
        }
        if (deep) {
            if (this.handler.drawing) {
                this.handler.removePoint();
                this.handler.finalize(true);
            } else {
                this.handler.destroyFeature();
            }
        }
        this.sumDisanceOrtho = 0;
        this.sumDisanceLoxo = 0;
        this.updateRouteSummary();
    },

    /** 
     * Method: getCourseLoxo
     * Compute the course (direction) between two waypoints
     * 
     * Parameters:
     * a - {Integer} the start waypoint index in this.waypoints
     * b - {Integer} the finish waypoint index in this.waypoints
     * 
     * Returns:
     * {Float} the course followed from a to b
     */
    getCourseLoxo: function (a, b) {
        if (!(this.waypoints[a] && this.waypoints[b])) {
            return Number.NaN;
        }

        var latA = OpenLayers.Util.rad(this.waypoints[a].pointLL.y);
        var lonA = OpenLayers.Util.rad(this.waypoints[a].pointLL.x);
        var latB = OpenLayers.Util.rad(this.waypoints[b].pointLL.y);
        var lonB = OpenLayers.Util.rad(this.waypoints[b].pointLL.x);

        if (latB == latA) {
            var goEast = (lonA - lonB >= 0) && (lonA - lonB < 180);
            return 90 * goEast ? 1 : -1;
        }

        var course = Math.atan((lonB-lonA)/(
                        Math.log(Math.tan(Math.PI/4 + latB/2))-
                        Math.log(Math.tan(Math.PI/4 + latA/2))
                    ));
        if (latB - latA < 0) {
            course += Math.PI;
        }
        if (course < 0) {
            course += 2 * Math.PI;
        }

        return course * 180/Math.PI;
    },

    /** 
     * Method: getCourseOrtho
     * Compute the course (direction) between two waypoints
     * 
     * Parameters:
     * a - {Integer} the start waypoint index in this.waypoints
     * b - {Integer} the finish waypoint index in this.waypoints
     * 
     * Returns:
     * {Array(Float, Float)} the course followed from a to b
     */
    getCourseOrtho: function (a, b) {
        if (!(this.waypoints[a] && this.waypoints[b])) {
            return Number.NaN;
        }

        var latA = OpenLayers.Util.rad(this.waypoints[a].pointLL.y);
        var lonA = OpenLayers.Util.rad(this.waypoints[a].pointLL.x);
        var latB = OpenLayers.Util.rad(this.waypoints[b].pointLL.y);
        var lonB = OpenLayers.Util.rad(this.waypoints[b].pointLL.x);

        var dist = Math.acos(
                        Math.sin(latA) * Math.sin(latB) + 
                        Math.cos(latA) * Math.cos(latB) * Math.cos(lonB - lonA)
                    );

        var departureCourse = Math.asin(Math.cos(latB) * Math.sin(lonB - lonA) / Math.sin(dist));
        var arrivalCourse   = Math.asin(Math.cos(latA) * Math.sin(lonA - lonB) / Math.sin(dist));

        if (latA > latB) {
            departureCourse = Math.PI - departureCourse;
        }
        if (latB > latA) {
            arrivalCourse = Math.PI - arrivalCourse;
        }

        return [
            (departureCourse >= 0 ? departureCourse : departureCourse + 2 * Math.PI) * 180/Math.PI,
            (arrivalCourse >= 0 ? arrivalCourse : arrivalCourse + 2 * Math.PI) * 180/Math.PI
        ];
    },

    /** 
     * Method: formatCourse
     * Prepare a course value for display
     * 
     * Parameters:
     * course - {Number} the raw course value
     * 
     * Returns:
     * {String} the formatted course
     */
    formatCourse: function (course) {
        if ((typeof course == "number") && (course != Number.NaN)) {
            return Math.round(course) + '°';
        } else {
            return '###°';
        }
    },

    /** 
     * Method: getDistanceOrtho
     * Compute the orthodromic distance two waypoints
     * 
     * Parameters:
     * a - {Integer} the start waypoint index in this.waypoints
     * b - {Integer} the finish waypoint index in this.waypoints
     * 
     * Returns:
     * {Float} the distance followed from a to b expressed in the units of
     * this.EARTH_RADIUS
     */
    getDistanceOrtho: function (a, b) {
        if (!(this.waypoints[a] && this.waypoints[b])) {
            return Number.NaN;
        }

        var latA = OpenLayers.Util.rad(this.waypoints[a].pointLL.y);
        var lonA = OpenLayers.Util.rad(this.waypoints[a].pointLL.x);
        var latB = OpenLayers.Util.rad(this.waypoints[b].pointLL.y);
        var lonB = OpenLayers.Util.rad(this.waypoints[b].pointLL.x);

        var dist = Math.acos(
                        Math.sin(latA) * Math.sin(latB) + 
                        Math.cos(latA) * Math.cos(latB) * Math.cos(lonB - lonA)
                    );

        return dist * this.EARTH_RADIUS;
    },

    /** 
     * Method: getDistanceLoxo
     * Compute the loxodromic distance two waypoints
     * 
     * Parameters:
     * a - {Integer} the start waypoint index in this.waypoints
     * b - {Integer} the finish waypoint index in this.waypoints
     * 
     * Returns:
     * {Float} the distance followed from a to b expressed in the units of
     * this.EARTH_RADIUS
     */
    getDistanceLoxo: function (a, b) {
        if (!(this.waypoints[a] && this.waypoints[b])) {
            return Number.NaN;
        }

        var latA = OpenLayers.Util.rad(this.waypoints[a].pointLL.y);
        var lonA = OpenLayers.Util.rad(this.waypoints[a].pointLL.x);
        var latB = OpenLayers.Util.rad(this.waypoints[b].pointLL.y);
        var lonB = OpenLayers.Util.rad(this.waypoints[b].pointLL.x);
        var dist;

        if (latB == latA) {
            dist = Math.abs((lonB - lonA)) * Math.cos(latA);
        } else {
            dist = Math.abs((latB - latA)) / Math.cos(Math.atan((lonB-lonA)/(
                                        Math.log(Math.tan(Math.PI/4 + latB/2))-
                                        Math.log(Math.tan(Math.PI/4 + latA/2))
                                    )));
        }

        return dist * this.EARTH_RADIUS;
    },

    /** 
     * Method: convertDistance
     * Convert a distance according to user preferences
     * 
     * Parameters:
     * Distance - {Float} the distance to be converted
     * 
     * Returns:
     * {Float} the distance converted
     */
    convertDistance: function (distance) {
        if ((typeof distance == "number") && (distance != Number.NaN)) {
            if (this.preferences.units.selected == 'nm') {
                distance *= OpenLayers.INCHES_PER_UNIT["km"] / 
                                                OpenLayers.INCHES_PER_UNIT["nmi"];
            }
        }
        return distance;
    },

    /** 
     * Method: formatDistance
     * Prepare a distance value expressed in kilometers for display according to
     * user preferences
     * 
     * Parameters:
     * distance - {Number} the raw distance value
     * 
     * Returns:
     * {String} the formatted distance
     */
    formatDistance: function (distance) {
        if ((typeof distance == "number") && (distance != Number.NaN)) {
            return this.convertDistance(distance).toFixed(2) + this.preferences.units.selected;
        } else {
            return '###' + this.preferences.units.selected;
        }
    },

    /** 
     * Method: formatCoordinates
     * Prepare a coordinates for display according to user preference
     * 
     * Parameters:
     * wid - {Integer} the waypoint index in this.waypoints
     * 
     * Returns:
     * {String} the formatted coordinates
     */
    formatCoordinates: function (wid) {
        var f = this.preferences.format.selected;
        var o = this.preferences.order.selected;
        if (!this.waypoints[wid]) {
            switch (f) {
                case 'dd':
                    return '##,###° ##,###°';
                case 'dms':
                    return '##°##\'##" ##°##\'##"';
            }
        } else {
            switch (f) {
                case 'dd':
                    var lat = this.waypoints[wid].pointLL.y.toFixed(3) + '°';
                    var lon = this.waypoints[wid].pointLL.x.toFixed(3) + '°';
                    return o == 'lonlat' ? lon + ' ' + lat : lat + ' ' + lon;
                case 'dms':
                    var lat = OpenLayers.Util.getFormattedLonLat(
                                this.waypoints[wid].pointLL.y, 'lat', 'dms');
                    var lon = OpenLayers.Util.getFormattedLonLat(
                                this.waypoints[wid].pointLL.x, 'lon', 'dms');
                    return o == 'lonlat' ? lon + ' ' + lat : lat + ' ' + lon;
            }
        }
    },

    /** 
     * Method: exportWaypoints
     * Handle waypoints export
     */
    exportWaypoints: function () {
        //Detect format
        var format;
        for (fkey in this.formats) {
            if ($(this.id + '_export_format_' + fkey).checked) {
                format = fkey;
                break;
            }
        }
        if (!format) {
            OpenLayers.Console.userError(OpenLayers.i18n('Which format?'));
            return false;
        }

        //Get route geometry
        var feature = this.handler.getSketch();
        if (!feature) {
            OpenLayers.Console.userError(OpenLayers.i18n('No route to export'));
            return false;
        } else {
            feature = feature.clone();
        }

        var mimetype, filename, content;
        switch (format) {
            case 'csv':
                mimetype = 'text/csv';
                filename = 'route.csv';
                var rows = [];
                rows[0] = ['"Latitude 0"', '"Longitude 0"', '"Latitude 1"', '"Longitude 1"', '"Distance ortho"', '"Distance loxo"', '"Course ortho 0"', '"Course ortho 1"', '"Course loxo"'].join(',') + '\n';
                for (var wid=1; wid<this.waypoints.length; wid++) {
                    var cols = [];
                    cols[cols.length] = this.waypoints[wid-1].pointLL.y;
                    cols[cols.length] = this.waypoints[wid-1].pointLL.x;
                    cols[cols.length] = this.waypoints[wid].pointLL.y;
                    cols[cols.length] = this.waypoints[wid].pointLL.x;
                    cols[cols.length] = this.convertDistance(this.getDistanceOrtho(wid-1, wid));
                    cols[cols.length] = this.convertDistance(this.getDistanceLoxo(wid-1, wid));
                    cols[cols.length] = this.getCourseOrtho(wid-1, wid)[0];
                    cols[cols.length] = this.getCourseOrtho(wid-1, wid)[1];
                    cols[cols.length] = this.getCourseLoxo(wid-1, wid);
                    rows[rows.length] = cols.join(',') + '\n';
                }
                content = rows.join('');
                break;
            case 'kml':
                var parser = new OpenLayers.Format.KML({
                    internalProjection: map.projection,
                    externalProjection: new OpenLayers.Projection('EPSG:900913')
                });
                mimetype = 'application/vnd.google-earth.kml+xml';
                filename = 'route.kml';
                content = parser.write(feature);
                break;
            case 'gpx':
                var parser = new OpenLayers.Format.GPX({
                    internalProjection: map.projection,
                    externalProjection: new OpenLayers.Projection('EPSG:4326')
                });
                mimetype = 'application/gpx+xml';
                filename = 'route.gpx';
                content = parser.write(feature);
                break;
            case 'gml':
                var parser = new OpenLayers.Format.GML.v2({
                    featureType: "nauticalRoute",
                    featureNS: "http://openseamap.org/nauticalRoute",
                    internalProjection: map.projection,
                    externalProjection: new OpenLayers.Projection('EPSG:4326')
                });
                feature.attributes.distanceOrtho = this.sumDisanceOrtho;
                feature.attributes.distanceLoxo = this.sumDisanceLoxo;
                mimetype = 'application/gml+xml';
                filename = 'route.gml';
                content = parser.write(feature);
                break;
        }
        $(this.id + '_export_input_mimetype').value = mimetype;
        $(this.id + '_export_input_filename').value = filename;
        $(this.id + '_export_input_content').value = content;
        $(this.id + '_export_form').submit();
    },

    /**
     * Method: visible
     *
     * Returns:      
     * {Boolean} Boolean indicating whether or not the control div is visible
     */
    visible: function() {
        return this.div && OpenLayers.Element.visible(this.div);
    },

    /**
     * Method: hide
     * FIXME: the handler is deactivated and the route geometry is therefore destroyed. It would be preferable to hide the geometry and keep the route for future use.
     */
    hide: function() {
        if (this.div) {
            this.handler.deactivate();
            this.clearWaypoints();
            OpenLayers.Element.hide(this.div);
        }
    },

    /**
     * Method: show
     */
    show: function() {
        if (this.div) {
            OpenLayers.Element.show(this.div);
            this.handler.activate();
        } else {
            this.draw();
        }
    },

    /**
     * Method: toggle
     */
    toggle: function() {
        this.visible() ? this.hide() : this.show();
        if ((arguments.length == 1) && (typeof window.Event == "function") 
                                && (arguments[0] instanceof window.Event)) {
            OpenLayers.Event.stop(arguments[0]);
        }
    },

    /** 
     * Method: onmousedown 
     * When mouse goes down within the control div, make a note of
     *   it locally, and then do not propagate the mousedown 
     *   (but do so safely so that user can select text inside)
     * 
     * Parameters:
     * evt - {Event} 
     */
    onmousedown: function (evt) {
        this.mousedown = true;
        OpenLayers.Event.stop(evt, true);
    },

    /** 
     * Method: onmousemove
     * If the drag was started within the control div, then 
     *   do not propagate the mousemove (but do so safely
     *   so that user can select text inside)
     * 
     * Parameters:
     * evt - {Event} 
     */
    onmousemove: function (evt) {
        if (this.mousedown) {
            OpenLayers.Event.stop(evt, true);
        }
    },

    /** 
     * Method: onmouseup
     * When mouse comes up within the control div, after going down 
     *   in it, reset the flag, and then (once again) do not 
     *   propagate the event, but do so safely so that user can 
     *   select text inside
     * 
     * Parameters:
     * evt - {Event} 
     */
    onmouseup: function (evt) {
        if (this.mousedown) {
            this.mousedown = false;
            OpenLayers.Event.stop(evt, true);
        }
    },

    /**
     * Method: onclick
     * Ignore clicks, but allowing default browser handling
     * 
     * Parameters:
     * evt - {Event} 
     */
    onclick: function (evt) {
        OpenLayers.Event.stop(evt, true);
    },

    /** 
     * Method: onmouseout
     * When mouse goes out of the control div set the flag to false so that
     *   if they let go and then drag back in, we won't be confused.
     * 
     * Parameters:
     * evt - {Event} 
     */
    onmouseout: function (evt) {
        this.mousedown = false;
    },
    
    /** 
     * Method: ondblclick
     * Ignore double-clicks, but allowing default browser handling
     * 
     * Parameters:
     * evt - {Event} 
     */
    ondblclick: function (evt) {
        OpenLayers.Event.stop(evt, true);
    },

    /** 
     * Method: stopDraggingWindow
     * 
     * Parameters:
     * evt - {Event}
     */
    stopDraggingWindow: function (evt) {
        if (!OpenLayers.Event.isLeftClick(evt) || !this.lastPixel) {
            return;
        }
        this.map.events.un({
            "mousemove": this.passEventToWindow,
            "mouseup": this.passEventToWindow,
            scope: this
        });
        delete this.lastPixel;
        OpenLayers.Event.stop(evt);
    },

    /**
     * Method: downTitleBar
     *
     * Parameters:
     * evt - {Event}
     */
    downTitleBar: function(evt) {
        if (!OpenLayers.Event.isLeftClick(evt)) {
            return;
        }
        this.map.events.on({
            "mousemove": this.passEventToWindow,
            "mouseup": this.passEventToWindow,
            scope: this
        });
        this.lastPixel = evt.xy.clone();
        OpenLayers.Event.stop(evt);
    },

    /**
     * Method: moveWindow
     * 
     * Parameters:
     * evt - {Event}
     */
    moveWindow: function(evt) {
        if (this.lastPixel) {
            this.position.x += evt.xy.x - this.lastPixel.x;
            this.position.y += evt.xy.y - this.lastPixel.y;
            this.moveTo(this.position);
            this.lastPixel = evt.xy.clone();
            OpenLayers.Event.stop(evt);
        }
    },

    /**
     * Method: passEventToWindow
     * 
     * Parameters:
     * evt - {Event}
     */
    passEventToWindow: function(evt) {
        this.dragEvents.handleBrowserEvent(evt);
    },

    CLASS_NAME: "OpenLayers.Control.NauticalRoute"
});

//In case we're using OL <= 2.8
if (!OpenLayers.Util.getFormattedLonLat) {
    OpenLayers.Util.getFormattedLonLat = function(coordinate, axis, dmsOption) {
        if (!dmsOption) {
            dmsOption = 'dms';    //default to show degree, minutes, seconds
        }
        var abscoordinate = Math.abs(coordinate)
        var coordinatedegrees = Math.floor(abscoordinate);

        var coordinateminutes = (abscoordinate - coordinatedegrees)/(1/60);
        var tempcoordinateminutes = coordinateminutes;
        coordinateminutes = Math.floor(coordinateminutes);
        var coordinateseconds = (tempcoordinateminutes - coordinateminutes)/(1/60);
        coordinateseconds =  Math.round(coordinateseconds*10);
        coordinateseconds /= 10;

        if( coordinatedegrees < 10 ) {
            coordinatedegrees = "0" + coordinatedegrees;
        }
        var str = coordinatedegrees + " ";  //get degree symbol here somehow for SVG/VML labelling

        if (dmsOption.indexOf('dm') >= 0) {
            if( coordinateminutes < 10 ) {
                coordinateminutes = "0" + coordinateminutes;
            }
            str += coordinateminutes + "'";
      
            if (dmsOption.indexOf('dms') >= 0) {
                if( coordinateseconds < 10 ) {
                    coordinateseconds = "0" + coordinateseconds;
                }
                str += coordinateseconds + '"';
            }
        }
        
        if (axis == "lon") {
            str += coordinate < 0 ? OpenLayers.i18n("W") : OpenLayers.i18n("E");
        } else {
            str += coordinate < 0 ? OpenLayers.i18n("S") : OpenLayers.i18n("N");
        }
        return str;
    };
}

OpenLayers.Lang["en"] = OpenLayers.Util.applyDefaults(OpenLayers.Lang["en"], {
    'Course and distance': 'Course and distance',
    'Start': 'Start',
    'Finish': 'Finish',
    'Course': 'Course',
    'Loxodromic distance': 'Lox. dist.',
    'Orthodromic distance': 'Orth. dist.',
    'Difference': 'Diff.',
    'Waypoint': 'Waypoint',
    'Coordinates': 'Coordinates',
    'Clear' : 'Clear',
    'Export' : 'Export',
    'Format' : 'Format',
    'CSV (spreadsheet)' : 'CSV (spreadsheet)',
    'KML (Google Earth)' : 'KML (Google Earth)',
    'GPX (GPS receiver)' : 'GPX (GPS receiver)',
    'GML (standard vector)': 'GML (standard vector)',
    'Get file' : 'Get file',
    'No route to export' : 'No route to export',
    'Which format?' : 'Which format?',
    'Preferences' : 'Preferences',
    'Unit system' : 'Unit system',
    'Nautical miles': 'Nautical miles',
    'Metric': 'Metric',
    'Coordinates format': 'Coordinates format',
    'Decimal degrees': 'Decimal degrees',
    'Degree/minute/second': 'Degree/minute/second',
    'Coordinates order': 'Coordinates order',
    'Latitude, longitude': 'Latitude, longitude',
    'Longitude, latitude': 'Longitude, latitude'
});

OpenLayers.Lang["de"] = OpenLayers.Util.applyDefaults(OpenLayers.Lang["de"], {
    'Course and distance': 'Kurs und distanz',
    'Start': 'Start',
    'Finish': 'Ziel',
    'Course': 'Kurs',
    'Loxodromic distance': 'Dist. lox.',
    'Orthodromic distance': 'Dist. orth.',
    'Difference': 'Diff.',
    'Waypoint': 'Wegpunkt',
    'Coordinates': 'Koordinate'
});

OpenLayers.Lang["fr"] = OpenLayers.Util.applyDefaults(OpenLayers.Lang["fr"], {
    'Course and distance': 'Cap et distance',
    'Start': 'Départ',
    'Finish': 'Arrivée',
    'Course': 'Cap',
    'Loxodromic distance': 'Dist. lox.',
    'Orthodromic distance': 'Dist. orth.',
    'Difference': 'Diff.',
    'Waypoint': 'Point de navigation',
    'Coordinates': 'Coordonnées',
    'Clear' : 'Effacer',
    'Export' : 'Export',
    'Format' : 'Format',
    'CSV (spreadsheet)' : 'CSV (tableur)',
    'KML (Google Earth)' : 'KML (Google Earth)',
    'GPX (GPS receiver)' : 'GPX (récépteur GPS)',
    'GML (standard vector)': 'GML (standard vectoriel)',
    'Get file' : 'Exporter',
    'No route to export' : 'Aucune route à exporter',
    'Which format?' : 'Which format?',
    'Preferences' : 'Paramètres',
    'Unit system' : 'Système d\'unités',
    'Nautical miles': 'Miles nautiques',
    'Metric': 'Métrique',
    'Coordinates format': 'Format des coordonnées',
    'Decimal degrees': 'Degrés décimaux',
    'Degree/minute/second': 'Degré/minute/seconde',
    'Coordinates order': 'Ordre des coordonnées',
    'Latitude, longitude': 'Latitude, longitude',
    'Longitude, latitude': 'Longitude, latitude'
});

/*
 * Design notes:
 *
 * About the window:
 * It might be a better idea to create a more generic class to handle the
 * window-like output. OpenLayers.Popup may fit but these are supposed to be
 * attached to a geographic position, not a viewport pixel. Should we create
 * an OpenLayers.Window class?
 *
 * About the control itself:
 * The interaction with the map should rather be encapsulated in a handler with
 * the possibility to:
 * - highlight waypoints,
 * - to pause/resume instead of activate/deactivate,
 * - to switch between loxodrome and orthodrome.
 */
