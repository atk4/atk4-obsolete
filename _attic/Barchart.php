<?php

/**
 * Barchart class
 * AModules adoption of the class created by Kirill Chernyshov (chk@adevel.com)
 *
 * Use this class to init chart: define data and chart properties.
 *
 * To get the output use separate php module such as Page.
 *
 * Created on 18.10.2007 by *Camper* (camper@adevel.com)
 */
class Barchart extends AbstractModel {
	protected $data = array ();
	protected $img;
	protected $resimg;
	protected $msg = '';

	protected $af = 2; // anialias factor

	protected $width = 400;
	protected $height = 200;
	protected $margin = 5;
	protected $background = '#FFFFFF';

	protected $current_item = false;

	protected $max_yaxis_caption;
	protected $max_xaxis_caption;

	protected $extremums = array ();
	//protected $min_value=array();	// array($x_value,$y_value)
	//protected $max_value=array();	// array($x_value,$y_value)
	protected $total = 0;

	#############################################
	#################  Setters  #################
	/**
	 * Sets the chart width
	 */
	function setWidth($width) {
		$this->width = $width;
		return $this;
	}
	function setHeight($height) {
		$this->height = $height;
		return $this;
	}
	function setBgColor($color) {
		$this->background = $color;
		return $this;
	}
	function setData($data) {
		$this->data = $data;
		return $this;
	}
	#############################################
	#################  Getters  #################
	/**
	 * Returns the resulting image
	 */
	function getImage() {
		return $this->resimg;
	}
	/**
	 * Returns the specified extremum.
	 * @param $type - 'min', 'max'
	 * @param $ord - if true, returns the ordinate (Y) value, if false - returns the absciss (X) value
	 */
	function getExtremum($type, $ord = true) {
		return $this->extremums[$type][($ord ? 1 : 0)];
	}
	function getTotalValue() {
		return $this->total;
	}
	#############################################
	function colorAlloc($color, $k = 1) {
		$color = trim($color);
		if ((strlen($color) != 7) || (substr($color, 0, 1) != '#'))
			return false;

		$r = hexdec(substr($color, 1, 2)) * $k;
		$g = hexdec(substr($color, 3, 2)) * $k;
		$b = hexdec(substr($color, 5, 2)) * $k;

		return imagecolorallocatealpha($this->img, $r, $g, $b, 0);
	}

	function validate($d, $w, $h, $m, $b) {
		if (empty ($d)) {
			$this->msg = 'no data to build the chart';
			return false;
		}

		return true;
	}

	// save object to session
	function save() {
		$chart_array = $this->api->recall('BARCHART_OBJECTS', array ());
		$chart_array[$this->short_name] = $this;
		// saving
		$this->api->memorize('BARCHART_OBJECTS', $chart_array);
		return $this;
	}
	/**
	 * Calculates extremums, totals and xaxis values
	 */
	function calcExtremums(& $min = null, & $max = null, & $xaxis = null) {
		// setting default extremums
		$this->extremums = array (
			'min' => array (
				0,
				1024 * 1024 * 1024 * 1024 * 1024 * 1024
			),
			'max' => array (
				0,
				0
			)
		);
		foreach ($this->data as $key => $val) {
			foreach ($val['value'] as $k => $v) {
				if ($this->getExtremum('min') > $v) {
					if (!is_null($min))
						$min = $v;
					$this->extremums['min'] = array (
						$k,
						$v
					);
				}
				if ($this->getExtremum('max') < $v) {
					if (!is_null($max))
						$max = $v;
					$this->extremums['max'] = array (
						$k,
						$v
					);
				}
				$this->total += $v;
			}
			if (!is_null($xaxis))
				$xaxis = array_merge($xaxis, $val['value']);
		}

		return $this;
	}
	/**
	 * Creates the resulting image.
	 * You need to call this method when preparing image
	 */
	function draw() {
		if (!$this->validate($this->data, $this->width, $this->height, $this->margin, $this->background))
			return false;
		//! calc min, max values and the X-Axis values
		$min = false;
		$max = false;
		$xaxis = array ();

		//die('<pre>'.print_r($this->data,true).'</pre>');
		// Calculating total, min and max values
		$this->calcExtremums($min, $max, $xaxis);
		$xaxis = array_keys($xaxis);

		//die('<pre>'.print_r($xaxis,true).'</pre>');

		//! base image creation
		$tw = $this->width * $this->af; //! x$this->af cuz a further antialias by size reduction
		$th = $this->height * $this->af;
		$this->img = imagecreatetruecolor($tw, $th);

		//! detect scale factor (Y axis)
		$tmp = 1;
		if ($max == 0) {
			$steps = 1;
			$scale_factor = 1;
			$top_point = 1;
		} else {
			$mm = ceil($max * 1.05);
			while (strlen(round($mm)) < 2) {
				$mm *= 10;
				$tmp *= 10;
			}
			$steps = 5;
			$w0 = $mm / $steps;
			$pre = strlen(round($w0));
			$pre = $pre -1;
			$w1 = round($w0 * 2, - $pre) / 2;
			while ($w1 * $steps <= $mm)
				$steps++;
			$scale_factor = $w1 / $tmp;
			$top_point = $steps * $scale_factor;
		}

		// calculate decimal counts for  Y-axis captions
		if ($scale_factor < 1) {
			if ($steps <= 10)
				$y_decimals = 2;
			else
				if ($steps <= 100)
					$y_decimals = 3;
				else
					$y_decimals = 4;
		} else
			$y_decimals = 0;

		if (empty ($top_point))
			return false;

		$fontpath = dirname(__FILE__) . '/chart/tahoma.ttf';
		$fontsize = 16;

		$ycap_w = 0; // initialize

		if (!empty ($this->max_yaxis_caption)) {
			$bbox = imagettfbbox($fontsize, 0, $fontpath, number_format($this->max_yaxis_caption, $y_decimals, '.', ' '));
			$ycap_w = $bbox[2] - $bbox[0] + 8 * $this->af;
		} else {
			//! Y-axis caption sizes
			$ycap_w = 0; // initialize
			for ($i = 1; $i < $steps; $i++) {
				$bbox = imagettfbbox($fontsize, 0, $fontpath, number_format($i * $scale_factor, $y_decimals, '.', ' '));

				if ($ycap_w < $bbox[2] - $bbox[0])
					$ycap_w = $bbox[2] - $bbox[0];
			}
			$ycap_w += 2 * $this->af; //! extra margin
		}

		//! detect the X-axis scale factor, based on the caption size
		$xcap_w = 0;
		$xcap_h = 0;

		if (!empty ($this->max_xaxis_caption)) {
			$bbox = imagettfbbox($fontsize, 90, $fontpath, $this->max_xaxis_caption);
			$xcap_w = ($bbox[0] - $bbox[6]) * 2;
			$xcap_h = $bbox[1] - $bbox[3];
		} else {
			foreach ($xaxis as $xval) {
				$bbox = imagettfbbox($fontsize, 90, $fontpath, $xval);
				if (($bbox[0] - $bbox[6]) * 2 > $xcap_w)
					$xcap_w = ($bbox[0] - $bbox[6]) * 2;
				if (($bbox[1] - $bbox[3]) > $xcap_h)
					$xcap_h = $bbox[1] - $bbox[3];
			}
		}
		$xcap_h += 2 * $this->af; //! extra margin

		//die('yw:'.$ycap_w.', xh:'.$xcap_h);

		//! chart width & height, origin position
		$cw = $tw - $this->margin * $this->af - $ycap_w;
		$ch = $th - $this->margin * $this->af - $xcap_h;
		$x_origin = $this->margin + $ycap_w;
		$y_origin = $this->margin + $ch;

		//! bar width
		$bar_space = $cw / count($xaxis);
		$bar_width = $bar_space * 0.7;

		//! allocate colors
		$bkg = $this->colorAlloc($this->background);
		$shadow = $this->colorAlloc($this->background, 0.7);
		$def_color = $this->colorAlloc('#000000');
		$axis_color = $this->colorAlloc('#000000');
		$grid_color = $this->colorAlloc('#AAAAAA');
		foreach ($this->data as $key => $val)
			$this->data[$key]['chartcolor'] = (isset ($val['color'])) ? $this->colorAlloc($val['color']) : $def_color;

		//! set background
		imagefilledrectangle($this->img, 0, 0, $tw -1, $th -1, $bkg);

		//! draw grid
		for ($i = 1; $i < $steps; $i++)
			imageline($this->img, $x_origin, $y_origin - $i * ($ch / $steps), $x_origin + $cw -2, $y_origin - $i * ($ch / $steps), $grid_color);

		$i = 1;
		$p = 0;
		while ($p < $cw) {
			$p = round($i * $bar_space / 2);
			imageline($this->img, $x_origin + $p, $y_origin - $ch, $x_origin + $p, $y_origin -2, $grid_color);
			$i++;
		}

		//! draw axis
		imagerectangle($this->img, $x_origin, $y_origin - $ch, $x_origin + $cw, $y_origin, $axis_color);

		//! write Y-axis captions
		for ($i = 1; $i < $steps; $i++) {
			$val = $i * $scale_factor;
			$val = number_format($val, $y_decimals, '.', ' ');
			$bbox = imagettfbbox($fontsize, 0, $fontpath, $val);

			imagettftext($this->img, $fontsize, 0, $x_origin - ($bbox[2] - $bbox[0]) - 8, $y_origin - ($i * ($ch / $steps)) + ($bbox[3] - $bbox[5]) / 2, $axis_color, $fontpath, $val);
		}

		//! write X-axis captions
		$xsf = ceil($xcap_w / $bar_space);
		for ($i = 0; $i < count($xaxis); $i += $xsf) { // lather <= / error fix by mvs
			$bbox = imagettfbbox($fontsize, 90, $fontpath, $xaxis[$i]);

			if (($this->current_item === false) or ($i < $this->current_item))
				$c_axis_color = $axis_color;
			else
				$c_axis_color = $this->colorAlloc('#BEBEBE');

			imagettftext($this->img, $fontsize, 90, round($x_origin + $i * $bar_space + $xcap_w / 2 + $bar_space / 2), $y_origin + ($bbox[1] - $bbox[3]) + 4 * $this->af, $c_axis_color, $fontpath, $xaxis[$i]); // $this->colorAlloc('#8E1C1C')
		}

		//!draw the chart
		$bar_origin = $x_origin +round(($bar_space - $bar_width) / 2) + $this->af;
		$i = 0;
		$ttttt = '';
		foreach ($xaxis as $xval) {
			$i++;
			foreach ($this->data as $key => $val) {
				$bar_h = round($ch * $val['value'][$xval] / $top_point);

				$k = (($this->current_item === false) || ($i <= $this->current_item)) ? .9 : .2;

				imagefilledrectangle($this->img, $bar_origin, $y_origin - $bar_h, $bar_origin + $bar_width, $y_origin -1, $this->alphaColor($val['chartcolor'], $k));

			}
			$bar_origin += $bar_space;
		}

		$this->resimg = ImageCreateTrueColor($this->width, $this->height);
		imagecopyResampled($this->resimg, $this->img, 0, 0, 0, 0, $this->width, $this->height, $tw, $th);
		return $this;
	}

	function alphaColor($fore_color, $k) {
		return $fore_color + (round(127 - ($k * 127)) * 0x1000000);
	}
}
