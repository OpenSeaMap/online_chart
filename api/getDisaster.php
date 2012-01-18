<?php
// -----------------------------------------------------------------------------
// Disaster Layer - OpenLayer class to display Disaster positions
//
// Written in 2012 by Dominik Fässler
//
// To the extent possible under law, the author(s) have dedicated all copyright
// and related and neighboring rights to this software to the public domain
// worldwide. This software is distributed without any warranty.
//
// You should have received a copy of the CC0 Public Domain Dedication along
// with this software. If not, see
// <http://creativecommons.org/publicdomain/zero/1.0/>.
// -----------------------------------------------------------------------------


// -----------------------------------------------------------------------------
// Description
// -----------------------------------------------------------------------------
// This script parses the Disaster positions file and generates a valid
// output format.
//
// -----------------------------------------------------------------------------


class Disaster
{

    protected $_vessel = null;
    protected $_data   = Array();

    public function fetchData()
    {
        $this->_data = Array();
        $filename = dirname(__FILE__) . '/../resources/disaster/disasters.txt';
        if (file_exists($filename) === false) {
            return;
        }
        $fh = fopen($filename, 'r');
        $line = fgets($fh, 1024);
        while ($line !== false) {
            $line = trim(strip_tags($line));
            if (strlen($line) === 0) {
                continue;
            }
            $lineArr = explode("\t", $line);
            if (count($lineArr) === 6) {
                $this->_addTrack($lineArr);
            } elseif (count($lineArr) === 2) {
                $this->_storeVessel();
                $this->_initVessel($lineArr[0], $lineArr[1]);
            }
            $line = fgets($fh, 1024);
        }
        $this->_storeVessel();
        fclose($fh);
    }

    protected function _initVessel($name, $link)
    {
        $this->_vessel = Array();
        $this->_vessel['lat']    = null;
        $this->_vessel['lon']    = null;
        $this->_vessel['name']   = $name;
        $this->_vessel['link']   = $link;
        $this->_vessel['tracks'] = Array();
    }

    protected function _storeVessel()
    {
        if ($this->_vessel === null) {
            return;
        }
        if ($this->_vessel['lat'] === null) {
            // There is no position
            return;
        }
        $this->_data[] = $this->_vessel;
        $this->_vessel = null;
    }

    protected function _addTrack($arr)
    {
        if (is_numeric($arr[0]) === false) {
            return;
        }
        if (is_numeric($arr[1]) === false) {
            return;
        }
        $lat = floatval($arr[0]); // lat
        $lon = floatval($arr[1]); // lon

        $this->_setLatLon($lat, $lon);
        $track = Array();
        $track[] = $lat;
        $track[] = $lon;
        $track[] = $arr[2]; // date
        $track[] = $arr[3]; // time
        $track[] = $arr[4]; // speed
        $track[] = $arr[5]; // course
        $this->_vessel['tracks'][] = $track;
    }

    protected function _setLatLon($lat, $lon)
    {
        if ($this->_vessel['lat'] !== null) {
            // Already set, first track is position.
            return;
        }
        $this->_vessel['lat'] = $lat;
        $this->_vessel['lon'] = $lon;
    }

    public function printByBBox($swX, $swY, $neX, $neY)
    {
        $mod = $this->_getMod($swY, $neY);
        foreach ($this->_data as $vessel) {
            $vesselName = false;
            $i = -1;
            foreach ($vessel['tracks'] as $track) {
                $i++;
                if (($i % $mod) !== 0) {
                    continue;
                }
                if ($track[0] > $swY &&
                    $track[0] < $neY &&
                    $track[1] > $swX &&
                    $track[1] < $neX) {
                    if ($vesselName === false) {
                        print $vessel['name'] . "\t" . $vessel['link'] . "\n";
                        $vesselName = true;
                    }
                    print implode("\t", $track) . "\n";
                }
            }
        }
    }

    protected function _getMod($swY, $neY)
    {
        $maxMod = 16;
        $size = $neY - $swY;
        $f = $size * 100 / 180;
        $mod = round($maxMod * $f / 100);
        if ($mod < 1) {
            $mod = 1;
        }
        return $mod;
    }

}


// -----------------------------------------------------------------------------
// Process params
// -----------------------------------------------------------------------------

$bbox = explode(',', $_REQUEST['bbox']);

if (count($bbox) !== 4) {
    return;
}

$swX = round($bbox[0] * 10000) / 10000;
$swY = round($bbox[1] * 10000) / 10000;
$neX = round($bbox[2] * 10000) / 10000;
$neY = round($bbox[3] * 10000) / 10000;

$disaster = new Disaster();
$disaster->fetchData();
$disaster->printByBBox($swX, $swY, $neX, $neY);
