<?php
// -----------------------------------------------------------------------------
// SatPro Layer - OpenLayer class to display SatPro positions
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
// This script parses the SatPro positions file and generates a valid
// output format. The service is very slow, so we have to get the data
// via cron job.
//
//   wget -O /tmp/satpro.dat 'http://www.satpro.org/openseamap/positions.php'
//
// If there is no 'satpro.dat' in the '/tmp' directory, nothing will be
// parsed.
// -----------------------------------------------------------------------------


class SatPro
{

    protected $_vessel = null;
    protected $_data   = Array();

    public function fetchData()
    {
        $this->_data = Array();
        if (file_exists('./satpro.dat') === false) {
            return;
        }
        $fh = fopen('./satpro.dat', 'r');
        $line = fgets($fh, 1024);
        while ($line !== false) {
            $line = trim(strip_tags($line));
            if (strlen($line) === 0) {
                continue;
            }
            $lineArr = explode("\t", $line);
            if (count($lineArr) === 36) {
                $this->_addTrack($lineArr);
            } else {
                $this->_storeVessel();
                $this->_initVessel($line);
            }
            $line = fgets($fh, 1024);
        }
        fclose($fh);
    }

    protected function _initVessel($name)
    {
        $this->_vessel = Array();
        $this->_vessel['lat']    = null;
        $this->_vessel['lon']    = null;
        $this->_vessel['name']   = $name;
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
        if (is_numeric($arr[31]) === false) {
            return;
        }
        if (is_numeric($arr[32]) === false) {
            return;
        }
        $lat = floatval($arr[31]); // lat_prec
        $lon = floatval($arr[32]); // long_prec

        $this->_setLatLon($lat, $lon);
        $track = Array();
        $track[] = $lat;
        $track[] = $lon;
        $track[] = $arr[0]; // terminal
        $track[] = $arr[1]; // datum
        $track[] = $arr[2]; // uhrzeit
        $track[] = $arr[5]; // hoehe
        $track[] = $arr[6]; // temparatur
        $track[] = $arr[7]; // batterie
        $track[] = $arr[8]; // geschwindigkeit
        $track[] = $arr[9]; // richtung
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
                        print $vessel['name'] . "\n";
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

$satpro = new SatPro();
$satpro->fetchData();
$satpro->printByBBox($swX, $swY, $neX, $neY);
