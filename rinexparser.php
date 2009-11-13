<?php
/**
 Copyright (c) <2009> <Roman Ozana, ozana@omdesign.cz>

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 *
 * GPS RINEX 2.1 NAVIGATION MESSAGE FILE PARSER
 *
 * (http://gps.wva.net/html.common/rinex.html)
 * @author Roman Ozana, ozana@omdesign.cz
 * @url www.omdesign.cz
 * @version 1.2
 */

/*
Use example :

require_once('rinexparser.php');

$rinex = new rinexparser();

$rinex->makelist('./data28');

if ($_GET['load']) // is file load
{
  $data = $rinex->load_file($_GET['load']); // add data
  $rinex->parse($data); // parse data
  $rinex->print_menu(); // printing menu
}

for ($i=0;$i<$rinex->counter;$i++) // printing RINEX File Navigation data
{
  $rinex->print_navi($i);
}
*/

class rinexparser {
  public $output = '';
  public $counter = 0;

  public function load_file($file) {
    $file_text = file_get_content($file); //load file
    return $file_text;
  }

  public function makelist($dir = "") {
    $handle = opendir($dir); // open dir handle
    while ( ($file = readdir($handle)) != false ) {
      if ( ($file != '..') && ($file != '.') && (substr($file, -1, 1) == 'N') ) {
        $files[] = $file;
      }
    }
    if (!empty($files)) {
      sort($files);
      echo '<ol>';
      for($i=0;$i<count($files);$i++) {
        echo '<li><a href="'.$_SERVER['PHP_SELF'].'?load='.$dir.'/'.$files[$i].'">'.$files[$i].'</a></li>';
      }
      echo '</ol>';
    }
  }


  public function parse($data = '') {
    $this->output = '';
    $data = ereg_replace("D([+|-])", "E\\1", $data);
    $data = spliti("\n",$data);

    $header_end = false;

    foreach ($data as $row) {
      if ($header_end == true and !empty($row)) // read only data
      {
        $vars = preg_split("/[\s]+/", $row,-1, PREG_SPLIT_NO_EMPTY); // parse text file
        //echo '<pre>';print_r($vars);echo '</pre>';
        switch ($brodcast_counter) {
          case 0:
            $this->output[$this->counter]['prn'] = $vars[0];
            $this->output[$this->counter]['year'] = $vars[1];
            $this->output[$this->counter]['month'] = $vars[2];
            $this->output[$this->counter]['day'] = $vars[3];
            $this->output[$this->counter]['hour'] = $vars[4];
            $this->output[$this->counter]['minute'] = $vars[5];
            $this->output[$this->counter]['second'] = $vars[6];
            $this->output[$this->counter]['SV_clock_bias'] = $vars[7];
            $this->output[$this->counter]['SV_clock_drift'] = $vars[8];
            $this->output[$this->counter]['SV_clock_drift_rate'] = $vars[9];
            break;
          case 1: // BRODCAST ORBIT 1
            $this->output[$this->counter]['IODE'] = $vars[0];
            $this->output[$this->counter]['Crs'] = $vars[1];
            $this->output[$this->counter]['Delta_n'] = $vars[2];
            $this->output[$this->counter]['M0'] = $vars[3];
            break;
          case 2: // BRODCAST ORBIT 2
            $this->output[$this->counter]['Cuc'] = $vars[0];
            $this->output[$this->counter]['e'] = $vars[1];
            $this->output[$this->counter]['Cus'] = $vars[2];
            $this->output[$this->counter]['sqrtA'] = $vars[3];
            break;
          case 3: // BRODCAST ORBIT 3
            $this->output[$this->counter]['Toe'] = $vars[0];
            $this->output[$this->counter]['Cic'] = $vars[1];
            $this->output[$this->counter]['OMEGA'] = $vars[2];
            $this->output[$this->counter]['CIS'] = $vars[3];
            break;
          case 4: // BRODCAST ORBIT 4
            $this->output[$this->counter]['i0'] = $vars[0];
            $this->output[$this->counter]['Crc'] = $vars[1];
            $this->output[$this->counter]['omega'] = $vars[2]; // normal is omega
            $this->output[$this->counter]['OMEGA_DOT'] = $vars[3];
            break;
          case 5: // BRODCAST ORBIT 5
            $this->output[$this->counter]['IDOT'] = $vars[0];
            $this->output[$this->counter]['Codes_on_L2_ch'] = $vars[1];
            $this->output[$this->counter]['GPS_week']= $vars[2];
            $this->output[$this->counter]['L2_p_data_flag'] = $vars[3];
            break;
          case 6: // BRODCAST ORBIT 6
            $this->output[$this->counter]['SV_accuracy'] = $vars[0];
            $this->output[$this->counter]['SV_health'] = $vars[1];
            $this->output[$this->counter]['TGD'] = $vars[2];
            $this->output[$this->counter]['IODC'] = $vars[3];
            break;
          case 7: // BRODCAST ORBIT 6
            $this->output[$this->counter]['Transmission_time'] = $vars[0];
            $this->output[$this->counter]['Fit_interval'] = $vars[1];
            $this->output[$this->counter]['spare_01'] = $vars[2];
            $this->output[$this->counter]['spare_02'] = $vars[3];
            $this->counter++;
            $brodcast_counter = -1;	 // don't forget clear counter
            break;
          default:
            break;
        }
        $brodcast_counter++; // number of brodcast
      } // is after head ?? - read data
      if (stristr($row,'END OF HEADER')) {
        $header_end = true; // end of header
        $brodcast_counter = 0;
      }
    }
    // echo '<pre>';print_r($this->output);echo '</pre>';
    return $this->output;
  }

  public function print_menu() {
    echo '<ol>';
    for($i=0;$i<$this->counter;$i++) {
      $gps = $this->output[$i];
      echo '<li><a href="#gps'.$i.$gps['prn'].'">GPS '.$gps['prn'].'</a> - '.$gps['day'].'.'.$gps['month'].'.'.$gps['year'].' v '.$gps['hour'].':'.$gps['minute'].'</li>';
    }
    echo '</ol>';
  }

  public function print_navi($number = 0) {
    if (count($this->output) < $number) {
      return;
    }
    $gps = $this->output[$number];

    echo '<h2><a name="gps'.$number.$gps['prn'].'"></a>Navigacni informace GPS : '.$gps['prn'].'</h2>';
    echo 	'<table border="1" cellspacing="0" cellpadding="7" width="80%">';
    // PRN info
    $this->add_lin('Promenna','Hodnota','Jednotky','th');
    $this->add_lin('GPS',$gps['prn']);
    $this->add_lin('Epocha : Datum a cas',$gps['day'].'.'.$gps['month'].'.'.$gps['year'].' v '.$gps['hour'].':'.$gps['minute']);
    $this->add_lin('SV clock bias',$gps['SV_clock_bias'],'sec');
    $this->add_lin('SV clock drift',$gps['SV_clock_drift'],'sec/sec');
    $this->add_lin('SV clock drift rate',$gps['SV_clock_drift_rate'],'sec/sec<sup>2</sup>');
    // BRODCAST 01
    $this->add_lin('','&nbsp;','');
    $this->add_lin('IODE Issue of data',$gps['IODE']);
    $this->add_lin('C<sub>rs</sub>',$gps['Crs'],'m');
    $this->add_lin('Delta n',$gps['Delta_n'],'radiany/sec');
    $this->add_lin('M<sub>0</sub>',$gps['M0'],'radiany');
    // BRODCAST 02
    $this->add_lin('','&nbsp;','');
    $this->add_lin('C<sub>uc</sub>',$gps['Cuc'],'radiany');
    $this->add_lin('Excentricita e',$gps['e'],'-');
    $this->add_lin('C<sub>us</sub>',$gps['Cus'],'radiany');
    $this->add_lin('sqrt(A)',$gps['sqrtA'],'sqrt(m)');
    // BRODCAST 03
    $this->add_lin('','&nbsp;','');
    $this->add_lin('T<sub>oe</sub>',$gps['Toe'],'sec of GPS week');
    $this->add_lin('C<sub>ic</sub>',$gps['Cic'],'radiany');
    $this->add_lin('OMEGA',$gps['OMEGA'],'radiany');
    $this->add_lin('CIS',$gps['CIS'],'radiany');
    // BRODCAST 04
    $this->add_lin('','&nbsp;','');
    $this->add_lin('i<sub>0</sub>',$gps['i0'],'radiany');
    $this->add_lin('C<sub>rc</sub>',$gps['Crc'],'radiany');
    $this->add_lin('omega',$gps['omega'],'radiany');
    $this->add_lin('OMEGA DOT',$gps['OMEGA_DOT'],'radiany/sec');
    // BRODCAST 05
    $this->add_lin('','&nbsp;','');
    $this->add_lin('IDOT',$gps['IDOT'],'radiany/sec');
    $this->add_lin('Codes on L2 channel',$gps['Codes_on_L2_ch']);
    $this->add_lin('GPS week',$gps['GPS_week']);
    $this->add_lin('L2 p data flag',$gps['L2_p_data_flag']);
    // BRODCAST 06
    $this->add_lin('','&nbsp;','');
    $this->add_lin('SV accuracy',$gps['SV_accuracy'],'metry');
    $this->add_lin('SV health',$gps['SV_health']);
    $this->add_lin('TGD',$gps['TGD'],'sec');
    $this->add_lin('IODC Issue of Data, Clock',$gps['IODC']);
    // BRODCAST 07
    $this->add_lin('','&nbsp;','');
    $this->add_lin('Transmission time of message',$gps['Transmission_time'],'radiany');
    $this->add_lin('Fit interval',$gps['Fit_interval'],'hodin');
    $this->add_lin('Spare',$gps['spare_01']);
    $this->add_lin('Spare',$gps['spare_02']);
    echo '</table>';
  }

  public function add_lin($coment, $value, $unit = '',$td = 'td') {
    if (!empty($unit) and $td == 'td') {
      $unit = '('.$unit.')';
    }
    echo "<tr><$td style=&qout;text-align:right;&qout;>$coment</$td><$td>$value</$td><$td>$unit</$td></tr>\n";
  }
		/*
		RINEX file upload not work yet - some errors i don't know
		function storefile($var, $location, $filename=NULL, $maxfilesize=NULL) {
			$ok = false;

			// Check file
			$mime = $_FILES[$var]["type"];
			if($mime=="image/jpeg" || $mime=="image/pjpeg") {
				// Mime type is correct
				// Check extension
				$name  = $_FILES[$var]["name"];
				$array = explode(".", $name);
				$nr    = count($array);
				$ext  = $array[$nr-1];
				if($ext=="jpg" || $ext=="jpeg") {
					$ok = true;
				}
			}

			if(isset($maxfilesize)) {
				if($_FILES[$var]["size"] > $maxfilesize) {
					$ok = false;
				}
			}

			if($ok==true) {
				$tempname = $_FILES[$var]['tmp_name'];
				if(isset($filename)) {
					$uploadpath = $location.$filename;
				} else {
					$uploadpath = $location.$_FILES[$var]['name'];
				}
				if(is_uploaded_file($_FILES[$var]['tmp_name'])) {
					while(move_uploaded_file($tempname, $uploadpath)) {
						// Wait for the script to finish its upload
					}
				}
				return true;
			} else {
				return false;
			}
		}
		*/

}