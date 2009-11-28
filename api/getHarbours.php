<?

/*  $b=$_REQUEST["b"];
  $l=$_REQUEST["l"];
  $t=$_REQUEST["t"];
  $r=$_REQUEST["r"];

  $db_pw = "";
  $db_host = "127.0.0.1";
  $db_user = "root";
  $db_db = "osm";
  
	$mysql = mysql_connect($db_host,$db_user,$db_pw);
    mysql_select_db($db_db,$mysql);

	$recset = mysql_query("SELECT * FROM WPI WHERE $l<lon AND lon<$r AND $b<lat AND lat<$t",$mysql);

  while($rec = mysql_fetch_object($recset))
  {
	    echo "putAJAXMarker($rec->World_Port_Index, $rec->lon, $rec->lat, '".addslashes($rec->Main_port)."', 0);\n";
  }

  $recset = mysql_query("SELECT * FROM skipperguide", $mysql);
 	//echo "putAJAXMarker(1, 2, 3, 4, 5)";
  while($rec = mysql_fetch_object($recset))
  {
	    echo "putAJAXMarker($rec->id, $rec->lon, $rec->lat, '".addslashes($rec->name)."<br><a href=$rec->descr>Beschreibung auf Skipperguide</a>', 1);\n";
  }*/


//So wuerde das fuer Rostock aussehen: (incl. Umlaute)
echo <<<DEMO
putAJAXMarker(28860, 12.13333, 54.1, 'ROSTOCK', '', 0);
putAJAXMarker(334, 11.77295, 54.1535, 'Kühlungsborn', 'http://www.skipperguide.de/wiki/Kühlungsborn', 1);
putAJAXMarker(559, 12.09918, 54.18202, 'Rostock - Hohe Düne', 'http://www.skipperguide.de/wiki/Rostock#Hohe_D.C3.BCne', 1);
putAJAXMarker(560, 12.08833, 54.18, 'Rostock - Warnemünde - Alter Strom', 'http://www.skipperguide.de/wiki/Rostock#Warnem.C3.BCnde_.2F_Alter_Strom', 1);
putAJAXMarker(561, 12.08963, 54.13515, 'Rostock', 'http://www.skipperguide.de/wiki/Rostock', 1);
putAJAXMarker(562, 12.09833, 54.11666, 'Rostock - Marina Dalben 28', 'http://www.skipperguide.de/wiki/Rostock#Marina_Dalben_28', 1);
putAJAXMarker(563, 12.11933, 54.09366, 'Rostock - Stadtmitte', 'http://www.skipperguide.de/wiki/Rostock#Stadtmitte', 1);
putAJAXMarker(564, 12.139, 54.093, 'Rostock - Stadtafen', 'http://www.skipperguide.de/wiki/Rostock#Marina_im_Stadthafen', 1);
putAJAXMarker(598, 12.08963, 54.13515, 'Rostock - Schmarl', 'http://www.skipperguide.de/wiki/Schmarl', 1);
DEMO;
?>