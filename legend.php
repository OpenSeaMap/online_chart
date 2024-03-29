<?php
include("classes/Pages.php");
include("classes/Translation.php");

$p = new Pages($_GET['page']);
$p->addPage("divider1", "<hr><b>".$t->tr("Legende")."</b><hr>","");
$p->addPage("harbour",$t->tr("harbour"),"api/legend/harbour.php");
$p->addPage("seamark",$t->tr("Seezeichen"),"api/legend/seamark.php");
$p->addPage("light",$t->tr("Leuchtfeuer"),"api/legend/light.php");
$p->addPage("lock",$t->tr("BrückenSchleusen"),"api/legend/lock.php");
$p->addPage("divider2", "<hr><b>".$t->tr("help")."</b><hr>","");
$p->addPage("help-josm",$t->tr("help-josm"),"./api/help/help-josm_".$t->tr("langCode").".php");
$p->addPage("help-tidal-scale",$t->tr("help-tidal-scale"),"./api/help/help-tidal-scale_".$t->tr("langCode").".php");
$p->addPage("help-trip-planner",$t->tr("tripPlanner"),"./api/help/help-trip-planner_".$t->tr("langCode").".php");
$p->addPage("help-website",$t->tr("help-website-int"),"./api/help/help-web-integr_".$t->tr("langCode").".php");
$p->addPage("help-online",$t->tr("help-online"),"./api/help/help-online_".$t->tr("langCode").".php");
$p->addPage("divider3", "<br><hr>","");
$p->addPage("license",$t->tr("license"),"./api/help/license_".$t->tr("langCode").".php");
$p->addPage("about",$t->tr("about"),"./api/help/about_".$t->tr("langCode").".php");
$p->setDefaultPage("harbour");

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>OSW -<?=$t->tr('nautical_chart')?>: <?=$t->tr("Legende")?> - <?=$p->getCurrentPageName()?></title>
        <meta name="AUTHOR" content="Olaf Hannemann">
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
        <meta http-equiv="content-language" content="<?= $t->getCurrentLanguage()?>">
        <meta name="date" content="2012-06-02">
        <link rel="SHORTCUT ICON" href="../resources/icons/logo.png">
        <link rel="stylesheet" type="text/css" href="legend.css">
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

