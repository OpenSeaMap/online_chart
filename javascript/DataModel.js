

function DataModel() {
	this.entry;
	this.group;
}

DataModel.meta = new Array(); //Metadaten
DataModel.meta["safe_water"] = "buoy_safe_water";
DataModel.meta["starboard"] = "buoy_lateral";
DataModel.meta["port"] = "buoy_lateral";
DataModel.meta["preferred_channel_starboard"] = "buoy_lateral";
DataModel.meta["preferred_channel_port"] = "buoy_lateral";
DataModel.meta["north"] = "buoy_cardinal";
DataModel.meta["east"] = "buoy_cardinal";
DataModel.meta["south"] = "buoy_cardinal";
DataModel.meta["west"] = "buoy_cardinal";
DataModel.meta["isolated_danger"] = "buoy_isolated_danger";
DataModel.meta["special_purpose"] = "buoy_special_purpose";

DataModel.light = new Array(); //Metadaten
DataModel.light["north"] = "Q:VQ";
DataModel.light["east"] = "VQ";
DataModel.light["south"] = "VQ";
DataModel.light["west"] = "Q:VQ";

DataModel.prototype.get = function(group, entry) {
	var Value = "-1"

	switch (group) {
		case "meta":
			Value = DataModel.meta[entry];
			break
		case "light":
			Value = DataModel.light[entry];
			break
	}
	
	return Value;
}
