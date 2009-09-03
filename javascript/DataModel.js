

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
DataModel.light["light_safe_water"] = "Iso:Oc";
DataModel.light["light_starboard"] = "Fl:Fl(2):Oc(2):Oc(3):Q:IQ";
DataModel.light["light_port"] = "Fl:Fl(2):Oc(2):Oc(3):Q:IQ";
DataModel.light["light_preferred_channel_starboard"] = "Fl(2+1)";
DataModel.light["light_preferred_channel_port"] = "Fl(2+1)";
DataModel.light["light_north"] = "Q:VQ";
DataModel.light["light_east"] = "Q(3):VQ(3)";
DataModel.light["light_south"] = "Q(6):VQ(6)";
DataModel.light["light_west"] = "Q(9):VQ(9)";
DataModel.light["light_isolated_danger"] = "Fl(2)";
DataModel.light["light_special_purpose"] = "Fl:Oc(2):Oc(3)";


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
