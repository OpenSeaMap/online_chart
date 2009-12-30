

function DataModel() {
	this.entry;
	this.group;
}

DataModel.meta = new Array(); //Metadata
DataModel.meta["safe_water"] = "safe_water";
DataModel.meta["starboard"] = "lateral";
DataModel.meta["port"] = "lateral";
DataModel.meta["preferred_channel_starboard"] = "lateral";
DataModel.meta["preferred_channel_port"] = "lateral";
DataModel.meta["north"] = "cardinal";
DataModel.meta["east"] = "cardinal";
DataModel.meta["south"] = "cardinal";
DataModel.meta["west"] = "cardinal";
DataModel.meta["isolated_danger"] = "isolated_danger";
DataModel.meta["special_purpose"] = "special_purpose";
DataModel.meta["shape_safe_water"] = "sphere:pillar:spar:stake";
DataModel.meta["shape_starboard"] = "conical:pillar:stake:perch";
DataModel.meta["shape_port"] = "can:pillar:spar:stake:perch";
DataModel.meta["shape_preferred_channel_starboard"] = "conical:pillar:stake";
DataModel.meta["shape_preferred_channel_port"] = "can:pillar:spar:stake";
DataModel.meta["shape_north"] = "pillar:spar:stake";
DataModel.meta["shape_east"] = "pillar:spar:stake";
DataModel.meta["shape_south"] = "pillar:spar:stake";
DataModel.meta["shape_west"] = "pillar:spar:stake";
DataModel.meta["shape_isolated_danger"] = "pillar:spar:stake";
DataModel.meta["shape_special_purpose"] = "barrel:pillar:spar:stake";

DataModel.light = new Array(); //Lights
DataModel.light["light_safe_water"] = "Iso:Oc";
DataModel.light["light_starboard"] = "Fl:Fl(2):Fl(3):Fl(4):Oc(2):Oc(3):Q:IQ";
DataModel.light["light_port"] = "Fl:Fl(2):Fl(3):Fl(4):Oc(2):Oc(3):Q:IQ";
DataModel.light["light_preferred_channel_starboard"] = "Fl(2+1)";
DataModel.light["light_preferred_channel_port"] = "Fl(2+1)";
DataModel.light["light_north"] = "Q:VQ";
DataModel.light["light_east"] = "Q(3):VQ(3)";
DataModel.light["light_south"] = "Q(6)+Lfl:VQ(6)+Lfl";
DataModel.light["light_west"] = "Q(9):VQ(9)";
DataModel.light["light_isolated_danger"] = "Fl(2)";
DataModel.light["light_special_purpose"] = "Fl:Fl(3):Fl(5):Oc(2):Oc(3)";

DataModel.trans = new Array(); //ft2oseam
DataModel.trans["safe_water"] = "buoy_safe_water";
DataModel.trans["lateral_starboard"] = "buoy_lateral";
DataModel.trans["lateral_port"] = "buoy_lateral";
DataModel.trans["lateral_preferred_channel_starboard"] = "buoy_lateral";
DataModel.trans["lateral_preferred_channel_port"] = "buoy_lateral";
DataModel.trans["cardinal_north"] = "buoy_cardinal";
DataModel.trans["cardinal_east"] = "buoy_cardinal";
DataModel.trans["cardinal_south"] = "buoy_cardinal";
DataModel.trans["cardinal_west"] = "buoy_cardinal";
DataModel.trans["isolated_danger"] = "buoy_isolated_danger";
DataModel.trans["special_purpose"] = "buoy_special_purpose";


DataModel.prototype.get = function(group, entry) {
	var value = "-1"

	switch (group) {
		case "meta":
			value = DataModel.meta[entry];
			break
		case "light":
			value = DataModel.light[entry];
			break
		case "trans":
			value = DataModel.trans[entry];
			break
	}

	return value;
}
