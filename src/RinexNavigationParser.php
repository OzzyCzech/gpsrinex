<?php

namespace om;

/**
 * RINEX Navigation Parser
 *
 * @copyright Roman Ožana <roman@ozana.cz>
 * @license MIT
 */
class RinexNavigationParser {

	/** @var int */
	private $boradcast = 1;

	/**
	 * @param sting $file
	 * @param callable $callback
	 * @return string|mixed
	 * @throws \RuntimeException
	 */
	public function parseFile($file, $callback = null) {
		$body = false; // each file starts with Header
		$output = []; // output data
		$this->boradcast = 1;
		$counter = 0;

		if (!$handle = fopen($file, 'r')) {
			throw new \RuntimeException('Can\'t open file' . $file . ' for reading');
		}

		while (!feof($handle)) {
			$row = fgets($handle, 4096); // read line from file

			if ($body && !empty($row)) {
				$partial = $this->parseRow($row); // read line

				if ($callback) {
					call_user_func($callback, $partial, $counter, $this->boradcast); // call user function for processing line
				} else {
					$output[$counter][] = $partial; // return whole data
				}

				if ($this->boradcast === 1) $counter++; // count
			}

			if (preg_match('/END OF HEADER/', $row)) $body = true; // end of header
		}

		fclose($handle);
		return ($callback) ? null : $output;
	}

	/**
	 * @param string $row
	 * @return string
	 */
	public function parseRow($row = '') {
		$row = preg_replace('/D([+|-])/', 'E\1', $row);
		$data = preg_split("/[\s]+/", $row, -1, PREG_SPLIT_NO_EMPTY);

		switch ($this->boradcast++) {
			case 1:
				return [
					'PRN' => $data[0],
					'year' => $data[1],
					'month' => $data[2],
					'day' => $data[3],
					'hour' => $data[4],
					'minute' => $data[5],
					'second' => $data[6],
					'SV_clock_bias' => $data[7],
					'SV_clock_drift' => $data[8],
					'SV_clock_drift_rate' => $data[9],
				];
			case 2: // BRODCAST ORBIT 1
				return [
					'IODE' => $data[0],
					'Crs' => $data[1],
					'Delta_n' => $data[2],
					'M0' => $data[3],
				];
			case 3: // BRODCAST ORBIT 2
				return [
					'Cuc' => $data[0],
					'e' => $data[1],
					'Cus' => $data[2],
					'sqrtA' => $data[3],
				];
			case 4: // BRODCAST ORBIT 3
				return [
					'Toe' => $data[0],
					'Cic' => $data[1],
					'OMEGA' => $data[2],
					'CIS' => $data[3],
				];
			case 5: // BRODCAST ORBIT 4
				return [
					'i0' => $data[0],
					'Crc' => $data[1],
					'omega' => $data[2], // normal is omega
					'OMEGA_DOT' => $data[3],
				];
			case 6: // BRODCAST ORBIT 5
				return [
					'IDOT' => $data[0],
					'Codes_on_L2_ch' => $data[1],
					'GPS_week' => $data[2],
					'L2_p_data_flag' => $data[3],
				];
			case 7: // BRODCAST ORBIT 6
				return [
					'SV_accuracy' => $data[0],
					'SV_health' => $data[1],
					'TGD' => $data[2],
					'IODC' => $data[3],
				];
			case 8: // BRODCAST ORBIT 6
				$this->boradcast = 1;
				return [
					'Transmission_time' => $data[0],
					'Fit_interval' => $data[1],
					'spare_01' => $data[2],
					'spare_02' => $data[3],
				];
		}
	}
}
