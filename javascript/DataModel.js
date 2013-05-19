

function DataModel() {
    this.entry;
    this.group;
}

DataModel.meta = new Array(); //Metadata***********************************************************
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
// Buoy shape -------------------------------------------------------------------------------------
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
// Seamark type -----------------------------------------------------------------------------------
DataModel.meta["type_sphere"] = "buoy_";
DataModel.meta["type_pillar"] = "buoy_";
DataModel.meta["type_spar"] = "buoy_";
DataModel.meta["type_can"] = "buoy_";
DataModel.meta["type_conical"] = "buoy_";
DataModel.meta["type_barrel"] = "buoy_";
DataModel.meta["type_stake"] = "beacon_";
DataModel.meta["type_perch"] = "beacon_";
// Buoy colour ------------------------------------------------------------------------------------
DataModel.meta["colour_safe_water"] = "white;red";
DataModel.meta["colour_starboard"] = "green";
DataModel.meta["colour_port"] = "red";
DataModel.meta["colour_preferred_channel_starboard"] = "green;red;green";
DataModel.meta["colour_preferred_channel_port"] = "red;green;red";
DataModel.meta["colour_north"] = "black;yellow";
DataModel.meta["colour_east"] = "black;yellow;black";
DataModel.meta["colour_south"] = "yellow;black";
DataModel.meta["colour_west"] = "yellow;black;yellow";
DataModel.meta["colour_isolated_danger"] = "black;red;black";
DataModel.meta["colour_special_purpose"] = "yellow";
// Buoy colour pattern ----------------------------------------------------------------------------
DataModel.meta["colour_pattern_safe_water"] = "vertical_stripes";
DataModel.meta["colour_pattern_starboard"] = "unichrome";
DataModel.meta["colour_pattern_port"] = "unichrome";
DataModel.meta["colour_pattern_preferred_channel_starboard"] = "horizontal_stripes";
DataModel.meta["colour_pattern_preferred_channel_port"] = "horizontal_stripes";
DataModel.meta["colour_pattern_north"] = "horizontal_stripes";
DataModel.meta["colour_pattern_east"] = "horizontal_stripes";
DataModel.meta["colour_pattern_south"] = "horizontal_stripes";
DataModel.meta["colour_pattern_west"] = "horizontal_stripes";
DataModel.meta["colour_pattern_isolated_danger"] = "horizontal_stripes";
DataModel.meta["colour_pattern_special_purpose"] = "unichrome";
// Topmark shape ----------------------------------------------------------------------------------
DataModel.meta["topmark_shape_safe_water"] = "sphere";
DataModel.meta["topmark_shape_starboard"] = "cone";
DataModel.meta["topmark_shape_port"] = "cylinder";
DataModel.meta["topmark_shape_preferred_channel_starboard"] = "cone";
DataModel.meta["topmark_shape_preferred_channel_port"] = "cylinder";
DataModel.meta["topmark_shape_north"] = "2_cones_up";
DataModel.meta["topmark_shape_east"] = "2_cones_base_together";
DataModel.meta["topmark_shape_south"] = "2_cones_down";
DataModel.meta["topmark_shape_west"] = "2_cones_point_together";
DataModel.meta["topmark_shape_isolated_danger"] = "2_spheres";
DataModel.meta["topmark_shape_special_purpose"] = "x-shape";
// Topmark colour ---------------------------------------------------------------------------------
DataModel.meta["topmark_colour_safe_water"] = "red";
DataModel.meta["topmark_colour_starboard"] = "green";
DataModel.meta["topmark_colour_port"] = "red";
DataModel.meta["topmark_colour_preferred_channel_starboard"] = "green";
DataModel.meta["topmark_colour_preferred_channel_port"] = "red";
DataModel.meta["topmark_colour_north"] = "black";
DataModel.meta["topmark_colour_east"] = "black";
DataModel.meta["topmark_colour_south"] = "black";
DataModel.meta["topmark_colour_west"] = "black";
DataModel.meta["topmark_colour_isolated_danger"] = "black";
DataModel.meta["topmark_colour_special_purpose"] = "yellow";


DataModel.light = new Array(); //Lights************************************************************
DataModel.light["light_safe_water"] = "Iso:Oc:LFl";
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
// Light colour -----------------------------------------------------------------------------------
DataModel.light["light_colour_safe_water"] = "white";
DataModel.light["light_colour_starboard"] = "green";
DataModel.light["light_colour_port"] = "red";
DataModel.light["light_colour_preferred_channel_starboard"] = "green";
DataModel.light["light_colour_preferred_channel_port"] = "red";
DataModel.light["light_colour_north"] = "white";
DataModel.light["light_colour_east"] = "white";
DataModel.light["light_colour_south"] = "white";
DataModel.light["light_colour_west"] = "white";
DataModel.light["light_colour_isolated_danger"] = "white";
DataModel.light["light_colour_special_purpose"] = "yellow";


DataModel.trans = new Array(); //ft2oseam**********************************************************
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
