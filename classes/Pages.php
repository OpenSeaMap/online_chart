<?php
class Pages {
    var $pages;
    var $currentPage;
    var $defaultPage;
    var $defaultPageLink;

    function Pages($currentPage) {
        $this->setCurrentPage($currentPage);
    }
    function addPage($id,$name,$file,$headerFile = "") {
        $this->pages[$id] = array("name"=>$name,"file"=>$file,"headerFile"=>$headerFile);
    }
    function makeIncludePage($path = "") {
        $file = $path.$this->pages[$this->getCurrentPage()]['file'];
        if (file_exists($file))
            return $file;
        else
            return $path."error.php";
    }
    function makeIncludePageHeader($path = "") {
        $file = $this->pages[$this->getCurrentPage()]['headerFile'];
        if ($file == "")
            return false;
        $file = $path.$file;
        if (file_exists($file))
            return $file;
        else
            return false;
    }
    function includePage() {
        include($this->makeIncludePage());
    }

    function setCurrentPage($id) {
        $this->currentPage = $id;
    }
    function setDefaultPage($id) {
        $this->defaultPage = $id;
    }
    function setDefaultPageLink($link) {
        $this->defaultPageLink = $link;
    }
    function getCurrentPage($emptyIfCurrentIsDefault = false) {
        if ($emptyIfCurrentIsDefault && $this->currentPage == $this->defaultPage) {
            return "";
        }
        if ($this->pages[$this->currentPage] != "") {
            return $this->currentPage;
        }
        return $this->defaultPage;
    }
    function getCurrentPageName() {
        return $this->pages[$this->getCurrentPage()]['name'];
    }


    function makePageLinks($link = "") {
        if ($link == "")
            $link = $this->defaultPageLink;
        $text = '<ul>'."\n";
        foreach ($this->pages as $key => $value) {
            $text .= '<li '.($this->getCurrentPage() == $key ? "id=\"selected\"" : "").'><a href="'.$link.'page='.$key.'" >'.$value['name'].'</a></li>'."\n";
        }
        $text .= '</ul>'."\n";
        return $text;
    }
    function makePageLink($id,$link = "") {
        if ($link == "")
            $link = $this->defaultPageLink;
        if (!isset($this->pages[$id]))
            return;
        $v = $this->pages[$id];
        $text = '<a href="'.$link.'page='.$id.'">'.$v['name'].'</a>';
        return $text;
    }
}
?>
