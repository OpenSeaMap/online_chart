<?php

// Create a new instance for translation
$t = new Translation();

/* Aktuell ausgewählte Sprache soll der URL-Parameter 'lang' (...?lang=de) sein */
if (array_key_exists('lang', $_GET) === true) {
    $t->setCurrentLanguage($_GET['lang']);
} else {
    $t->setCurrentLanguage('');
}

// Define the default language
$t->setDefaultLanguage("en");

// Include an array for each language
// German
include "lg-de.php";

// English
include "lg-en.php";

// French
include "lg-fr.php";

// Italian
include "lg-it.php";

// Spanish
include "lg-es.php";

// Russian
include "lg-ru.php";

//Add languages
$t->addLanguage("de",$german,"Deutsch");
$t->addLanguage("en",$english,"English");
$t->addLanguage("es",$spanish,"Español");
$t->addLanguage("fr",$french,"Français");
$t->addLanguage("it",$italian,"Italiano");
$t->addLanguage("ru",$russian,"Русский");


class Translation {

    var $languages = array();
    var $defaultLanguage;
    var $currentLanguage;
    var $preferredLanguage;
    var $languageNames = array();

    /*
     * $name - Die Id mit der die Sprache angesprochen wird (z.B. im URL-Parameter)
     * $table - Das Array mit den eigentlichen Übersetzungen
     * $fullname (optional) - Ausgeschriebener Name der Sprache (z.B. für die Anzeige)
     */
    function addLanguage($name,$table,$fullname = "") {
        $this->languages[$name] = $table;
        $this->languageNames[$name] = $fullname == "" ? $name : $fullname;
    }
    function setDefaultLanguage($name) {
        $this->defaultLanguage = $name;
    }
    function tr($entry,$language = "") {
        // Wenn language nicht gesetzt ist, die aktuell gesetzte Sprache benutzen
        if ($language == "")
            $language = $this->getCurrentLanguage();
        $text = $this->languages[$language][$entry];
        // Wenn kein Text gefunden wurde, als Fallback die Standardsprache probieren
        if (!isset($text) && $language != $this->defaultLanguage) {
            $text = $this->languages[$this->defaultLanguage][$entry];
        }
        return $text;
    }
    function pr($entry,$language = "") {
        echo $this->tr($entry,$language);
    }
    // Aktuelle genutzte Sprache
    function setCurrentLanguage($name) {
        $this->currentLanguage = $name;
    }
    function getCurrentLanguage() {
        // Aktuelle Sprache ist entweder die manuell gesetzte oder die als bevorzugt gesetzte
        return $this->currentLanguage != "" ? $this->currentLanguage : $this->getPreferredLanguage();
    }
    function getCurrentLanguageSafe() {
        $lang = $this->getCurrentLanguage();
        if ($lang == 'de' || $lang = 'en') {
            return $lang;
        } else {
            return 'en';
        }
    }
    // Bevorzugte Sprache
    function setPreferredLanguage($name) {
        $this->preferredLanguage = $name;
    }
    function getPreferredLanguage() {
        if ($this->preferredLanguage == "") {
            return $this->getHttpPreferredLanguage();
        }
        return $this->preferredLanguage;
    }
    /*
     * Erstellt eine ungeordnete Liste der Sprachlinks
     */
    function makeLanguageLinks($link) {
        $text = '<ul>'."\n";
        foreach ($this->languageNames as $key => $value) {
            $text .= '<li><a href="'.$link.'lang='.$key.'" '.($this->getCurrentLanguage() == $key ? "class=\"currentLanguage\"" : "").'>'.$value.'</a></li>'."\n";
        }
        $text .= '</ul>'."\n";
        return $text;
    }
    /*
     * Parsed die vom Browser gesendeten bevorzugten Sprachen und gibt sie als Array mit Gewichtigkeit zurück
     */
    function getHttpPreferredLanguages() {
        $list = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $preferredLanguages = array();
        $array = explode(",",$list);
        foreach ($array as $key => $value) {
            $split = explode(";",$value);
            $langcode = $split[0];
            if (count($split) > 1) {
                $temp = explode("=",$split[1]);
            } else {
                $temp = array();
            }
            if (count($temp) > 1) {
                $quality = $temp[1] != "" ? $temp[1] : 1;
            } else {
                $quality = 1;
            }

            $langcodeArray = explode("-",$langcode);
            $langcodeLanguage = $langcodeArray[0];
            if (count($langcodeArray) > 1) {
                $langcodeCountry = $langcodeArray[1];
            } else {
                $langcodeCountry = $langcodeLanguage;
            }
            $preferredLanguages[] = array(
                "language"=>$langcodeLanguage,
                "country"=>$langcodeCountry,
                "quality"=>(float)$quality
            );

        }
        return $preferredLanguages;
    }
    /*
     * Gibt die letzte bevorzugte Sprache mit der höchsten Gewichtigkeit zurück
     */
    function getHttpPreferredLanguage() {
        $languages = $this->getHttpPreferredLanguages();
        $quality = 0;
        $code = "";
        foreach ($languages as $key => $value) {
            if ($value['quality'] > $quality) {
                $quality = $value['quality'];
                $code = $value['language'];
            }
        }
        return $code;
    }
}



?>
