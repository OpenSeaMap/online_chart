

function DataModel() {
	this.entry;
	this.group;
}

DataModel.meta = new Array(); //Metadata
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
DataModel.meta["shape_safe_water"] = "sphere:pillar:spar";
DataModel.meta["shape_starboard"] = "conical:pillar";
DataModel.meta["shape_port"] = "can:pillar:spar";
DataModel.meta["shape_preferred_channel_starboard"] = "conical:pillar";
DataModel.meta["shape_preferred_channel_port"] = "can:pillar:spar";
DataModel.meta["shape_north"] = "pillar:spar";
DataModel.meta["shape_east"] = "pillar:spar";
DataModel.meta["shape_south"] = "pillar:spar";
DataModel.meta["shape_west"] = "pillar:spar";
DataModel.meta["shape_isolated_danger"] = "pillar:spar";
DataModel.meta["shape_special_purpose"] = "barrel:pillar:spar";

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
DataModel.light["light_special_purpose"] = "Fl:Oc(2):Oc(3)";

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
