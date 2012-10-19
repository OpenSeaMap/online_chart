<?
	$b=$_REQUEST["b"];
	$l=$_REQUEST["l"];
	$t=$_REQUEST["t"];
	$r=$_REQUEST["r"];

	$db_pw = "hawa11";
	$db_host = "rdbms";
	$db_user = "U986537";
	$db_db = "DB986537";
  
	$mysql = mysql_connect($db_host, $db_user, $db_pw);
	mysql_select_db($db_db, $mysql);

	$recset = mysql_query("SELECT * FROM harbours WHERE $l<lon AND lon<$r AND $b<lat AND lat<$t", $mysql);

	while($rec = mysql_fetch_object($recset)) {
	    echo "putHarbourMarker($rec->id, $rec->lon, $rec->lat, '$rec->name', '$rec->link', '$rec->type');\n";
	}
?>
