<?

	$b=$_REQUEST["b"];
	$l=$_REQUEST["l"];
	$t=$_REQUEST["t"];
	$r=$_REQUEST["r"];
	$maxSize=$_REQUEST["maxSize"];

	$db_pw = "password";
	$db_host = "127.0.0.1";
	$db_user = "user";
	$db_db = "database";
  
	$mysql = mysql_connect($db_host, $db_user, $db_pw);
	mysql_select_db($db_db, $mysql);
	if($maxSize>=5){
	  $recset = mysql_query("SELECT *,RAND() as rand FROM tanta_sg1 WHERE $l<lon AND lon<$r AND $b<lat AND lat<$t ORDER BY rand LIMIT 100", $mysql);

	  while($rec = mysql_fetch_object($recset)) {
	      echo "putAJAXMarker($rec->id, $rec->lon, $rec->lat, '".addslashes($rec->name)."', '$rec->descr', -1);\n";
	  }
	}

	$recset = mysql_query("SELECT *,RAND() as rand FROM WPI WHERE $l<lon AND lon<$r AND $b<lat AND lat<$t AND size<>\"\" ORDER BY size, rand LIMIT 100", $mysql);

	while($rec = mysql_fetch_object($recset)) {
	    echo "putAJAXMarker($rec->World_Port_Index, $rec->lon, $rec->lat, '".addslashes($rec->Main_port)."', '', $rec->size);\n";
	}
?>
