/*jslint plusplus:true */
function Geomag(model) {
	'use strict';
	var wmm,
		maxord = 12,
		a = 6378.137,		// WGS 1984 Equatorial axis (km)
		b = 6356.7523142,	// WGS 1984 Polar axis (km)
		re = 6371.2,
		a2 = a * a,
		b2 = b * b,
		c2 = a2 - b2,
		a4 = a2 * a2,
		b4 = b2 * b2,
		c4 = a4 - b4,
		z = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
		unnormalizedWMM;

	function parseCof(cof) {
		wmm = (function (cof) {
			var modelLines = cof.split('\n'), wmm = [], i, vals, epoch, model, modelDate;
			for (i in modelLines) {
				if (modelLines.hasOwnProperty(i)) {
					vals = modelLines[i].replace(/^\s+|\s+$/g, "").split(/\s+/);
					if (vals.length === 3) {
						epoch = parseFloat(vals[0]);
						model = vals[1];
						modelDate = vals[2];
					} else if (vals.length === 6) {
						wmm.push({
							n: parseInt(vals[0], 10),
							m: parseInt(vals[1], 10),
							gnm: parseFloat(vals[2]),
							hnm: parseFloat(vals[3]),
							dgnm: parseFloat(vals[4]),
							dhnm: parseFloat(vals[5])
						});
					}
				}
			}

			return {epoch: epoch, model: model, modelDate: modelDate, wmm: wmm};
		}(cof));
	}

	function unnormalize(wmm) {
		var i, j, m, n, D2, flnmj,
			c = [z.slice(), z.slice(), z.slice(), z.slice(), z.slice(), z.slice(),
				z.slice(), z.slice(), z.slice(), z.slice(), z.slice(), z.slice(),
				z.slice()],
			cd = [z.slice(), z.slice(), z.slice(), z.slice(), z.slice(), z.slice(),
				z.slice(), z.slice(), z.slice(), z.slice(), z.slice(), z.slice(),
				z.slice()],
			k = [z.slice(), z.slice(), z.slice(), z.slice(), z.slice(), z.slice(),
				z.slice(), z.slice(), z.slice(), z.slice(), z.slice(), z.slice(),
				z.slice()],
			snorm = [z.slice(), z.slice(), z.slice(), z.slice(), z.slice(),
				z.slice(), z.slice(), z.slice(), z.slice(), z.slice(), z.slice(),
				z.slice(), z.slice()],
			model = wmm.wmm;
		for (i in model) {
			if (model.hasOwnProperty(i)) {
				if (model[i].m <= model[i].n) {
					c[model[i].m][model[i].n] = model[i].gnm;
					cd[model[i].m][model[i].n] = model[i].dgnm;
					if (model[i].m !== 0) {
						c[model[i].n][model[i].m - 1] = model[i].hnm;
						cd[model[i].n][model[i].m - 1] = model[i].dhnm;
					}
				}
			}
		}
		/* CONVERT SCHMIDT NORMALIZED GAUSS COEFFICIENTS TO UNNORMALIZED */
		snorm[0][0] = 1;

		for (n = 1; n <= maxord; n++) {
			snorm[0][n] = snorm[0][n - 1] * (2 * n - 1) / n;
			j = 2;

			for (m = 0, D2 = (n - m + 1); D2 > 0; D2--, m++) {
				k[m][n] = (((n - 1) * (n - 1)) - (m * m)) /
					((2 * n - 1) * (2 * n - 3));
				if (m > 0) {
					flnmj = ((n - m + 1) * j) / (n + m);
					snorm[m][n] = snorm[m - 1][n] * Math.sqrt(flnmj);
					j = 1;
					c[n][m - 1] = snorm[m][n] * c[n][m - 1];
					cd[n][m - 1] = snorm[m][n] * cd[n][m - 1];
				}
				c[m][n] = snorm[m][n] * c[m][n];
				cd[m][n] = snorm[m][n] * cd[m][n];
			}
		}
		k[1][1] = 0.0;

		unnormalizedWMM = {epoch: wmm.epoch, k: k, c: c, cd: cd};
	}

	this.setCof = function (cof) {
		parseCof(cof);
		unnormalize(wmm);
	};
	this.getWmm = function () {
		return wmm;
	};
	this.setUnnorm = function (val) {
		unnormalizedWMM = val;
	};
	this.getUnnorm = function () {
		return unnormalizedWMM;
	};
	this.getEpoch = function () {
		return unnormalizedWMM.epoch;
	};
	this.setEllipsoid = function (e) {
		a = e.a;
		b = e.b;
		re = 6371.2;
		a2 = a * a;
		b2 = b * b;
		c2 = a2 - b2;
		a4 = a2 * a2;
		b4 = b2 * b2;
		c4 = a4 - b4;
	};
	this.getEllipsoid = function () {
		return {a: a, b: b};
	};
	this.calculate = function (glat, glon, h, date) {
		if (unnormalizedWMM === undefined) {
			throw new Error("A World Magnetic Model has not been set.")
		}
		if (glat === undefined || glon === undefined) {
			throw new Error("Latitude and longitude are required arguments.");
		}
		function rad2deg(rad) {
			return rad * (180 / Math.PI);
		}
		function deg2rad(deg) {
			return deg * (Math.PI / 180);
		}
		function decimalDate(date) {
			date = date || new Date();
			var year = date.getFullYear(),
				daysInYear = 365 +
					(((year % 400 === 0) || (year % 4 === 0 && (year % 100 > 0))) ? 1 : 0),
				msInYear = daysInYear * 24 * 60 * 60 * 1000;

			return date.getFullYear() + (date.valueOf() - (new Date(year, 0)).valueOf()) / msInYear;
		}

		var epoch = unnormalizedWMM.epoch,
			k = unnormalizedWMM.k,
			c = unnormalizedWMM.c,
			cd = unnormalizedWMM.cd,
			alt = (h / 3280.8399) || 0, // convert h (in feet) to kilometers (default, 0 km)
			dt = decimalDate(date) - epoch,
			rlat = deg2rad(glat),
			rlon = deg2rad(glon),
			srlon = Math.sin(rlon),
			srlat = Math.sin(rlat),
			crlon = Math.cos(rlon),
			crlat = Math.cos(rlat),
			srlat2 = srlat * srlat,
			crlat2 = crlat * crlat,
			q,
			q1,
			q2,
			ct,
			st,
			r2,
			r,
			d,
			ca,
			sa,
			aor,
			ar,
			br = 0.0,
			bt = 0.0,
			bp = 0.0,
			bpp = 0.0,
			par,
			temp1,
			temp2,
			parp,
			D4,
			m,
			n,
			fn = [0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
			fm = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
			z = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
			tc = [z.slice(), z.slice(), z.slice(), z.slice(), z.slice(), z.slice(),
				z.slice(), z.slice(), z.slice(), z.slice(), z.slice(), z.slice(),
				z.slice()],
			sp = z.slice(),
			cp = z.slice(),
			pp = z.slice(),
			p = [z.slice(), z.slice(), z.slice(), z.slice(), z.slice(), z.slice(),
				z.slice(), z.slice(), z.slice(), z.slice(), z.slice(), z.slice(),
				z.slice()],
			dp = [z.slice(), z.slice(), z.slice(), z.slice(), z.slice(), z.slice(),
				z.slice(), z.slice(), z.slice(), z.slice(), z.slice(), z.slice(),
				z.slice()],
			bx,
			by,
			bz,
			bh,
			ti,
			dec,
			dip,
			gv;
		sp[0] = 0.0;
		sp[1] = srlon;
		cp[1] = crlon;
		tc[0][0] = 0;
		cp[0] = 1.0;
		pp[0] = 1.0;
		p[0][0] = 1;

		/* CONVERT FROM GEODETIC COORDS. TO SPHERICAL COORDS. */
		q = Math.sqrt(a2 - c2 * srlat2);
		q1 = alt * q;
		q2 = ((q1 + a2) / (q1 + b2)) * ((q1 + a2) / (q1 + b2));
		ct = srlat / Math.sqrt(q2 * crlat2 + srlat2);
		st = Math.sqrt(1.0 - (ct * ct));
		r2 = (alt * alt) + 2.0 * q1 + (a4 - c4 * srlat2) / (q * q);
		r = Math.sqrt(r2);
		d = Math.sqrt(a2 * crlat2 + b2 * srlat2);
		ca = (alt + d) / r;
		sa = c2 * crlat * srlat / (r * d);

		for (m = 2; m <= maxord; m++) {
			sp[m] = sp[1] * cp[m - 1] + cp[1] * sp[m - 1];
			cp[m] = cp[1] * cp[m - 1] - sp[1] * sp[m - 1];
		}

		aor = re / r;
		ar = aor * aor;

		for (n = 1; n <= maxord; n++) {
			ar = ar * aor;
			for (m = 0, D4 = (n + m + 1); D4 > 0; D4--, m++) {

		/*
				COMPUTE UNNORMALIZED ASSOCIATED LEGENDRE POLYNOMIALS
				AND DERIVATIVES VIA RECURSION RELATIONS
		*/
				if (n === m) {
					p[m][n] = st * p[m - 1][n - 1];
					dp[m][n] = st * dp[m - 1][n - 1] + ct *
						p[m - 1][n - 1];
				} else if (n === 1 && m === 0) {
					p[m][n] = ct * p[m][n - 1];
					dp[m][n] = ct * dp[m][n - 1] - st * p[m][n - 1];
				} else if (n > 1 && n !== m) {
					if (m > n - 2) { p[m][n - 2] = 0; }
					if (m > n - 2) { dp[m][n - 2] = 0.0; }
					p[m][n] = ct * p[m][n - 1] - k[m][n] * p[m][n - 2];
					dp[m][n] = ct * dp[m][n - 1] - st * p[m][n - 1] -
						k[m][n] * dp[m][n - 2];
				}

		/*
				TIME ADJUST THE GAUSS COEFFICIENTS
		*/

				tc[m][n] = c[m][n] + dt * cd[m][n];
				if (m !== 0) {
					tc[n][m - 1] = c[n][m - 1] + dt * cd[n][m - 1];
				}

		/*
				ACCUMULATE TERMS OF THE SPHERICAL HARMONIC EXPANSIONS
		*/
				par = ar * p[m][n];
				if (m === 0) {
					temp1 = tc[m][n] * cp[m];
					temp2 = tc[m][n] * sp[m];
				} else {
					temp1 = tc[m][n] * cp[m] + tc[n][m - 1] * sp[m];
					temp2 = tc[m][n] * sp[m] - tc[n][m - 1] * cp[m];
				}
				bt = bt - ar * temp1 * dp[m][n];
				bp += (fm[m] * temp2 * par);
				br += (fn[n] * temp1 * par);
		/*
					SPECIAL CASE:  NORTH/SOUTH GEOGRAPHIC POLES
		*/
				if (st === 0.0 && m === 1) {
					if (n === 1) {
						pp[n] = pp[n - 1];
					} else {
						pp[n] = ct * pp[n - 1] - k[m][n] * pp[n - 2];
					}
					parp = ar * pp[n];
					bpp += (fm[m] * temp2 * parp);
				}
			}
		}

		bp = (st === 0.0 ? bpp : bp / st);
		/*
			ROTATE MAGNETIC VECTOR COMPONENTS FROM SPHERICAL TO
			GEODETIC COORDINATES
		*/
		bx = -bt * ca - br * sa;
		by = bp;
		bz = bt * sa - br * ca;

		/*
			COMPUTE DECLINATION (DEC), INCLINATION (DIP) AND
			TOTAL INTENSITY (TI)
		*/
		bh = Math.sqrt((bx * bx) + (by * by));
		ti = Math.sqrt((bh * bh) + (bz * bz));
		dec = rad2deg(Math.atan2(by, bx));
		dip = rad2deg(Math.atan2(bz, bh));

		/*
			COMPUTE MAGNETIC GRID VARIATION IF THE CURRENT
			GEODETIC POSITION IS IN THE ARCTIC OR ANTARCTIC
			(I.E. GLAT > +55 DEGREES OR GLAT < -55 DEGREES)
			OTHERWISE, SET MAGNETIC GRID VARIATION TO -999.0
		*/

		if (Math.abs(glat) >= 55.0) {
			if (glat > 0.0 && glon >= 0.0) {
				gv = dec - glon;
			} else if (glat > 0.0 && glon < 0.0) {
				gv = dec + Math.abs(glon);
			} else if (glat < 0.0 && glon >= 0.0) {
				gv = dec + glon;
			} else if (glat < 0.0 && glon < 0.0) {
				gv = dec - Math.abs(glon);
			}
			if (gv > 180.0) {
				gv -= 360.0;
			} else if (gv < -180.0) { gv += 360.0; }
		}

		return {dec: dec, dip: dip, ti: ti, bh: bh, bx: bx, by: by, bz: bz, lat: glat, lon: glon, gv: gv};
	};
	this.calc = this.calculate;
	this.mag = this.calculate;

	if (model !== undefined) { // initialize
		if (typeof model === 'string') { // WMM.COF file
			parseCof(model);
			unnormalize(wmm);
		} else if (typeof model === 'object') { // unnorm obj
			this.setUnnorm(model);
		} else {
			throw new Error("Invalid argument type");
		}
	}
}
