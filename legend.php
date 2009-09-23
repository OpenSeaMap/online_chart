<?php
include("../classes/Pages.php");
include("../classes/Translation.php");

$p = new Pages($_GET['page']);
$p->addPage("harbour",$t->tr("Hafen"),"harbour.php");
$p->addPage("seamark",$t->tr("Seezeichen"),"seamark.php");
$p->addPage("light",$t->tr("Leuchtfeuer"),"light.php");
$p->addPage("lock",$t->tr("BrückenSchleusen"),"lock.php");
$p->setDefaultPage("harbour");

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
	<title>OpenSeaMap: <?=$t->tr("Legende")?> - <?=$p->getCurrentPageName()?></title>
		<meta name="AUTHOR" content="Olaf Hannemann" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<meta http-equiv="content-language" content="<?= $t->getCurrentLanguage() ?>" />
		<link rel="stylesheet" type="text/css" href="map-legend.css">
	</head>

	<body>
		<div id="header">
			<?=$p->makePageLinks("legend.php?lang=".$t->getCurrentLanguage()."&amp;")?>
		</div>
		<div id="content">
			<br>
			<?php include($p->makeIncludePage()); ?>
			<br>
		</div>
	</body>

</html>

