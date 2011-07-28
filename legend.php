<?php
include("../classes/Pages.php");
include("../classes/Translation.php");

$p = new Pages($_GET['page']);
$p->addPage("divider1", "<b>".$t->tr("Legende")."</b>-----------------------","");
$p->addPage("harbour",$t->tr("harbour"),"harbour.php");
$p->addPage("seamark",$t->tr("Seezeichen"),"seamark.php");
$p->addPage("light",$t->tr("Leuchtfeuer"),"light.php");
$p->addPage("lock",$t->tr("BrückenSchleusen"),"lock.php");
$p->addPage("divider2", "<b>".$t->tr("help")."</b>------------------------------","");
$p->addPage("help-online-editor", $t->tr("help-oe"),"./map_key_pages/help-online-editor_".$t->tr("langCode").".php");
$p->addPage("help-josm",$t->tr("help-josm"),"./map_key_pages/help-josm_".$t->tr("langCode").".php");
$p->addPage("help-tidal-scale",$t->tr("help-tidal-scale"),"./map_key_pages/help-tidal-scale_".$t->tr("langCode").".php");
$p->addPage("help-website",$t->tr("help-website-int"),"./map_key_pages/help-web-integr_".$t->tr("langCode").".php");
$p->addPage("divider3", "------------------------------------","");
$p->addPage("license",$t->tr("license"),"./map_key_pages/license_".$t->tr("langCode").".php");
//$p->addPage("about",$t->tr("about"),"./map_key_pages/about_".$t->tr("langCode").".php");
$p->setDefaultPage("harbour");

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
	<title>OSW -<?=$t->tr('nautical_chart')?>: <?=$t->tr("Legende")?> - <?=$p->getCurrentPageName()?></title>
		<meta name="AUTHOR" content="Olaf Hannemann" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<meta http-equiv="content-language" content="<?= $t->getCurrentLanguage() ?>" />
		<link rel="SHORTCUT ICON" href="../resources/icons/logo.png"/>
		<link rel="stylesheet" type="text/css" href="map-legend.css">
	</head>

	<body>
		<div id="menu">
			<?=$p->makePageLinks("legend.php?lang=".$t->getCurrentLanguage()."&amp;")?>
		</div>
		<div id="content">
			<?php include($p->makeIncludePage()); ?>
		</div>
	</body>
</html>

