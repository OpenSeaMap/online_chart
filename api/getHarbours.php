<?
  /*
  $b=$_REQUEST["b"];
  $l=$_REQUEST["l"];
  $t=$_REQUEST["t"];
  $r=$_REQUEST["r"];

  $db_pw = "osmpass";
  $db_host = "localhost";
  $db_user = "osm";
  $db_db = "osm";
  
  $mysql = mysql_connect($db_host,$db_user,$db_pw);
  mysql_select_db($db_db,$mysql);

  $recset = mysql_query("SELECT * FROM WPI WHERE $l<lon AND lon<$r AND $b<lat AND lat<$t",$mysql);

  while($rec = mysql_fetch_object($recset))
  {
	    echo "putAJAXMarker($rec->World_Port_Index, $rec->lon, $rec->lat, '".addslashes($rec->Main_port)."', 0);\n";
  }

  $recset = mysql_query("SELECT * FROM skipperguide WHERE $l<lon AND lon<$r AND $b<lat AND lat<$t",$mysql);

  while($rec = mysql_fetch_object($recset))
  {
	    echo "putAJAXMarker($rec->id, $rec->lon, $rec->lat, '".addslashes($rec->name)."<br><a href=$rec->descr>Beschreibung auf Skipperguide</a>', 1);\n";
  }
  */
//So wuerde das fuer Rostock aussehen: (incl. Umlaute)
echo <<<DEMO
putAJAXMarker(28860, 12.133333333, 54.1, 'ROSTOCK', 0);
putAJAXMarker(334, 11.77295, 54.1535, 'Kühlungsborn - Kühlungsborn<br><a href=http://www.skipperguide.de/wiki/Kühlungsborn>Beschreibung auf Skipperguide</a>', 1);
putAJAXMarker(558, 12.133333333333, 54.083333333333, 'Rostock<br><a href=http://www.skipperguide.de/wiki/Rostock>Beschreibung auf Skipperguide</a>', 1);
putAJAXMarker(559, 12.097833333333, 54.184833333333, 'Rostock - Hohe Düne<br><a href=http://www.skipperguide.de/wiki/Rostock>Beschreibung auf Skipperguide</a>', 1);
putAJAXMarker(560, 12.088333333333, 54.18, 'Rostock - Warnemünde - Alter Strom<br><a href=http://www.skipperguide.de/wiki/Rostock>Beschreibung auf Skipperguide</a>', 1);
putAJAXMarker(561, 12.089633333333, 54.13515, 'Rostock<br><a href=http://www.skipperguide.de/wiki/Rostock>Beschreibung auf Skipperguide</a>', 1);
putAJAXMarker(562, 12.098333333333, 54.116666666667, 'Rostock - Marina Dalben 28<br><a href=http://www.skipperguide.de/wiki/Rostock>Beschreibung auf Skipperguide</a>', 1);
putAJAXMarker(563, 12.119333333333, 54.093666666667, 'Rostock - Stadtmitte<br><a href=http://www.skipperguide.de/wiki/Rostock>Beschreibung auf Skipperguide</a>', 1);
putAJAXMarker(564, 12.139, 54.093, 'Rostock - Stadtafen<br><a href=http://www.skipperguide.de/wiki/Rostock>Beschreibung auf Skipperguide</a>', 1);
putAJAXMarker(598, 12.089633333333, 54.13515, 'Schmarl<br><a href=http://www.skipperguide.de/wiki/Schmarl>Beschreibung auf Skipperguide</a>', 1);
DEMO;
?>