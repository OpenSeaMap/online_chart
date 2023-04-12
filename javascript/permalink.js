// Create a url from the current map's view.
function getPermalink(otherParams = {}) {
  let zoom = map.getView().getZoom().toFixed(1);

  // Remove the .0 from the zoom.
  if (/\.0$/.test(zoom)) {
    zoom = parseInt(map.getView().getZoom(), 10);
  }
  const [lon, lat] = ol.proj.toLonLat(map.getView().getCenter());

  const mapLayers = map.getLayers().getArray();
  const maxLayerId = mapLayers.reduce(
    (maxLayerId, layer) => Math.max(maxLayerId, layer.get("layerId")),
    0
  );
  let layers = "";
  for (var i = 1; i <= maxLayerId; i += 1) {
    const layer = mapLayers.find((l) => l.get("layerId") === i);
    if (!layer?.getVisible()) {
      layers += "F";
    } else if (layer.getVisible()) {
      layers += layer.get("isBaseLayer") ? "B" : "T";
    }
  }

  const params = new URLSearchParams({
    zoom,
    lon: lon.toFixed(5),
    lat: lat.toFixed(5),
    layers,
    ...otherParams,
  });

  return window.location.href.split("?")[0] + `?${params.toString()}`;
}

class Permalink extends ol.control.Control {
  constructor() {
    super({
      element: document.createElement("div"),
    });
    this.timeout = null;

    this.aElement_ = document.createElement("a");
    this.aElement_.innerHTML = "Permalink";
    this.element.className = "ol-permalink";
    this.element.appendChild(this.aElement_);
  }

  render() {
    // We use a timeout to avoid unnecessary updates.
    clearTimeout(this.timeout);
    this.timeout = setTimeout(() => {
      this.aElement_.href = getPermalink();
    }, 500);
  }
}
