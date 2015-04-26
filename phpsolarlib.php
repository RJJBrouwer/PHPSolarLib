<?php

/*

PHPSolarLib Graphic Library V1.0
--------------------------------

This PHPSolarLib Grahical Library was written in 2015 by Ramon Brouwer
to support displaying remote monitoring of solar panels easily


The MIT License (MIT)

Copyright (c) 2015 Ramon Brouwer

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/

class PHPSolarLib {

	// fixed values

	const PANEL_ID_TEXT_SIZE = 2;		// Textsize of panel ID number on panel (range 1.5)
	const PANEL_WATT_TEXT_SIZE = 5;		// Textsize of output wattage on panel (range 1..5)

	// default values
	protected $panel_x_elements = 4;	// Total number of horizontal solar cells per panel
	protected $panel_y_elements = 7;	// Total number of vertical solar cells per panel
	protected $panel_element_width = 14;	// Width in pixels of a solar cell
	protected $panel_element_height = 14;	// Height in pixels of a solar cell

	protected $width = 100;			// Default Image Width in case not defined
	protected $height = 100;		// Default Image Height in case not defined
	protected $solarpanel_glow_color = 180;	// Default Solarpanel cell glow color (HUE value)
	protected $solarpanel_max_output = 250;	// Default Solarpanel Maximum Power Output in watt

	// color values
	protected $background_color;			// Image canvas background color
	protected $solarpanel_frame_color;		// Solarpanel frame color
	protected $solarpanel_id_text_color;		// Solarpanel InverterID text color
	protected $solarpanel_output_power_text_color;	// Solarpanel Output Power text color

	// internal values
	protected $bool_data = false;

	protected $image;
	protected $output_file;
	protected $data_array;

	protected $error;			// Array to contain possible error messages


	public function __construct($width, $height, $output_file = null)
	{
		$this->width = $width;
		$this->height = $height;
		$this->output_file = $output_file;
		$this->initialize();
	}

	protected function initialize()
	{
		if (!$this->output_file) {
			header("Content-type: image/png");
		}
		$this->image = @imagecreatetruecolor($this->width, $this->height)
			or die("Cannot create true color image");
		$this->initializeImageColors();
	}

/*
Error display function
*/
	protected function displayErrors() 
	{
		if (count($this->error) > 0) {
			$lineHeight = 14;
			$errorColor = imagecolorallocate($this->image, 0, 0, 0);
			$errorBackColor = imagecolorallocate($this->image, 255, 230, 0);
			imagefilledrectangle($this->image, 0, 0, $this->width - 1, 2 * $lineHeight,  $errorBackColor);
			imagestring($this->image, 3, 2, 0, "!---=< PHPSolarLib Error >=---!",  $errorColor);
			foreach($this->error as $key => $errorText) {
				imagefilledrectangle($this->image, 0, ($key * $lineHeight) + $lineHeight, $this->width - 1, ($key * $lineHeight) + 2 * $lineHeight,  $errorBackColor);	
				imagestring($this->image, 2, 2, ($key * $lineHeight) + $lineHeight, "[". ($key + 1) . "] ". $errorText,  $errorColor);	
			}
			$errorOutlineColor = imagecolorallocate($this->image, 255, 0, 0);
			imagerectangle($this->image, 0, 0, $this->width-1,($key * $lineHeight) + 2 * $lineHeight,  $errorOutlineColor);		
		}
	}

/* 
ColorHSLtoRGB: Function to convert a Hue Lightness Saturation value to Red Green Blue
*/
	protected function ColorHSLtoRGB($h, $s, $l)
	{
        	$r = $l; $g = $l; $b = $l;
        	$v = ($l <= 0.5) ? ($l * (1.0 + $s)) : ($l + $s - $l * $s);
        	if ($v > 0) {
	              	$m = $l + $l - $v;
        	      	$sv = ($v - $m ) / $v;
			$h *= 6.0;
              		$sextant = floor($h);
              		$fract = $h - $sextant;
              		$vsf = $v * $sv * $fract;
              		$mid1 = $m + $vsf;
              		$mid2 = $v - $vsf;

              		switch ($sextant) {
                    	 case 0:
                          $r = $v; $g = $mid1; $b = $m;
                          break;
                    	 case 1:
                          $r = $mid2; $g = $v; $b = $m;
                          break;
                    	 case 2:
                          $r = $m; $g = $v; $b = $mid1;
                          break;
                    	 case 3:
                          $r = $m; $g = $mid2; $b = $v;
                          break;
                    	 case 4:
                          $r = $mid1; $g = $m; $b = $v;
                          break;
                    	 case 5:
                          $r = $v; $g = $m; $b = $mid2;
                          break;
              		}
        	}
        return array('r' => $r * 255.0, 'g' => $g * 255.0, 'b' => $b * 255.0);
	}

/*
Initialize default colors for the image canvas
*/
	protected function initializeImageColors()
	{
		$this->background_color =			imagecolorallocate($this->image, 255, 255, 255); /* white */
		$this->solarpanel_frame_color =			imagecolorallocate($this->image, 080, 080, 080); /* light grey */
		$this->solarpanel_id_text_color =		imagecolorallocate($this->image, 020, 020, 020); /* graphite */
		$this->solarpanel_output_power_text_color =	imagecolorallocate($this->image, 255, 255, 200); /* off white */
	}
	
/*
Color functions
*/
	protected function returnColorArray($color) 
	{
		//check if color provided in decimal or hex
		if (strpos($color,',') !== false) {
			return explode(',', $color);
		} elseif (substr($color, 0, 1) == '#') {	
			if (strlen($color) == 7) {
				$hex1 = hexdec(substr($color, 1, 2));
				$hex2 = hexdec(substr($color, 3, 2));
				$hex3 = hexdec(substr($color, 5, 2));
				return array($hex1, $hex2, $hex3);
			} elseif (strlen($color) == 4) {
				$hex1 = hexdec(substr($color, 1, 1) . substr($color, 1, 1));
				$hex2 = hexdec(substr($color, 2, 1) . substr($color, 2, 1));
				$hex3 = hexdec(substr($color, 3, 1) . substr($color, 3, 1));
				return array($hex1, $hex2, $hex3);
			}			
		}
		// check if color provided by name (standard 17 HTML colors only)
		switch (strtolower($color)) {
			case 'aqua':   return array(0,255,255); break;
			case 'black':  return array(0,0,0); break;
			case 'blue':   return array(0,0,255); break;
			case 'fuscia': return array(255,0,255); break;
			case 'gray':   return array(128,128,128); break;
			case 'green':  return array(0,128,0); break;
			case 'lime':   return array(0,255,0); break;
			case 'maroon': return array(128,0,0); break;
			case 'navy':   return array(0,0,128); break;	
			case 'olive':  return array(128,128,0); break;
			case 'orange': return array(255,165,0); break;
			case 'purple': return array(128,0,128); break;
			case 'red':    return array(255,0,0); break;
			case 'silver': return array(192,192,192); break;
			case 'teal':   return array(0,128,128); break;
			case 'white':  return array(255,255,255); break;
			case 'yellow': return array(255,255,0); break;
		}
		$this->error[] = "returnColorArray: Color \"$color\" unknown.";
		return false;
	}

	protected function setGenericColor($inputColor, $var, $errorMsg)
	{
		if (!empty($inputColor) && ($arr = $this->returnColorArray($inputColor))) {
			eval($var . ' = imagecolorallocate($this->image, $arr[0], $arr[1], $arr[2]);');
			return true;	
		}
		else {
			$this->error[] = $errorMsg;
			return false;
		}
	}

/*
draw a panel with given specifications ($inverterID and $value array)
*/
	protected function drawPanel($id, $value)
	{
		$x=$value['x'];
		$y=$value['y'];

		$output = $value['output']; 

		$xsize = $this->panel_element_width;
		$xtotal = $this->panel_x_elements;
		$ysize = $this->panel_element_height;
		$ytotal = $this->panel_y_elements;

		// Create temp image to build up a Solarpanel
		$tempImage = imagecreatetruecolor ($xsize*$xtotal+1,$ysize*$ytotal+$ysize+1);

		// Determine Solarpanel glow color
		$c = self::ColorHSLtoRGB($this->solarpanel_glow_color/360, ((50/$this->solarpanel_max_output)*$output)/100, (20+((45/$this->solarpanel_max_output)*$output))/100);
		$r = $c['r']; $g = $c['g']; $b = $c['b'];
		$tempImageColor = imagecolorallocate($tempImage, $r,$g,$b);

		// Draw Solarpanel
		imagefilledrectangle ($tempImage,0,0,$xsize*$xtotal,$ysize,$this->background_color);
		imagefilledrectangle ($tempImage,0,$ysize,($xsize*$xtotal),$ysize+($ysize*$ytotal), $tempImageColor);

		imagerectangle($tempImage, 0,0,($xsize*$xtotal), $ysize, $this->solarpanel_frame_color);
		for ($j = 0; $j < $ytotal; $j++) {
			for ($i = 0; $i < $xtotal; $i++) { 
       				imagerectangle($tempImage, ($i*$xsize),$ysize+($j*$ysize),($i*$xsize)+$xsize,$ysize+($j*$ysize)+$ysize, $this->solarpanel_frame_color);
			}
		}

		// Add Solarpanel Inverter ID
		$textHorPos = round(1 + (($xsize*$xtotal) / 2) - ((strlen($id) * (self::PANEL_ID_TEXT_SIZE*3)) / 2));
		$textVerPos = round(($ysize/2)-(12/2));
		imagestring($tempImage, self::PANEL_ID_TEXT_SIZE, $textHorPos, $textVerPos,$id, $this->solarpanel_id_text_color);

		// Add Output Power to Solarpanel
		$textHorPos = round(1 + (($xsize*$xtotal) / 2) - ((strlen($output) * (self::PANEL_WATT_TEXT_SIZE*2)) / 2));
		imagestring($tempImage, self::PANEL_WATT_TEXT_SIZE, $textHorPos, ($ysize*$ytotal)/2+$ysize/2, $output , $this->solarpanel_output_power_text_color);

		// Rotate Solarpanel if necessary
		if ($value['orientation'] != 0) {
			$rotate = imagerotate($tempImage, 360-$value['orientation'], $this->background_color);
			imagecolortransparent($rotate, $this->background_color);

			imagecopymerge($this->image, $rotate, $x, $y, 0, 0, imagesx($rotate),imagesy($rotate),100);
			imagedestroy($rotate);
		}
		else {
			imagecopymerge($this->image, $tempImage, $x, $y, 0, 0, imagesx($tempImage),imagesy($tempImage),100);
		}
		imagedestroy($tempImage);
	}

/*
All public available functions
*/


/*
Set Image Canvas Background color
*/
	public function setBackgroundColor($color) 
	{
		if ($this->setGenericColor($color, '$this->background_color', "setBackgroundColor: color not set.")) {
			
		}
	}

/*
Set Solarpanel Frame color
*/
	public function setSolarpanelFrameColor($color) 
	{
		if ($this->setGenericColor($color, '$this->solarpanel_frame_color', "setSolarpanelFrameColor: color not set.")) {
			
		}
	}

/*
Set Solarpanel ID Text color
*/
	public function setSolarpanelIdTextColor($color) 
	{
		if ($this->setGenericColor($color, '$this->solarpanel_id_text_color', "setSolarpanelIdTextColor: color not set.")) {
			
		}
	}

/*
Set Solarpanel OutputPower Text color
*/
	public function setSolarpanelOutputPowerTextColor($color) 
	{
		if ($this->setGenericColor($color, '$this->solarpanel_output_power_text_color', "setSolarpanelOutputPowerTextColor: color not set.")) {
			
		}
	}

/*
Set the Solarpanel glow color as HUE value range (0..360)
*/
	public function setSolarpanelGlowColor($data)
	{
		if ($data >= 0 && $data <= 360) 
		{ 
			$this->solarpanel_glow_color = $data;
		}
		else {
			$this->error[] = "setSolarpanelGlowColor: invalid HUE value.";
		}
	}

/*
Set maximum Solarpanel output power (higher than 1)
*/
	public function setSolarpanelMaxOutput($data)
	{
		if ($data > 1) 
		{ 
			$this->solarpanel_max_output = $data;
		}
		else {
			$this->error[] = "setSolarpanelMaxOutput: invalid max output.";
		}
	}

/*
Set dimensions of a Solarpanel: #cells horizontal, #cells vertical, width and height of a cell in pixels
*/
	public function setSolarpanelSize($totalCellsHorizontal, $totalCellsVertical, $horizontalSize, $verticalSize)
	{
		if ($totalCellsHorizontal > 0 && $totalCellsVertical > 0 && $horizontalSize > 4 && $verticalSize > 4) {
			$this->panel_x_elements = $totalCellsHorizontal;
			$this->panel_y_elements = $totalCellsVertical;
			$this->panel_element_width = $horizontalSize;
			$this->panel_element_height = $verticalSize;
		}
		else {
			$this->error[] = "setSolarpanelSize: invalid size.";
		}
	}

/*
Set Solar data array with all panel information format: [panel-id][x,y,orientation,output]
*/
	public function addSolarData($data) 
	{
		if (is_array($data)) {
			$this->data_array = $data;
			$this->bool_data = true;
		}
		else {
			$this->error[] = "addSolarData: Invalid solar data added.";
			return;
		}
	}


/*
Main function to create all Solar Panels
*/
	public function createSolarpanels()
	{
		if ($this->bool_data) {
			imagefilledrectangle ($this->image, 0,0, imagesx($this->image), imagesy($this->image), $this->background_color);

			foreach ($this->data_array as $id => $value) {
				$this->drawPanel ($id, $value);
			}
		}
		else {
			$this->error[] = "createSolarpanels: No valid solar data added.";
		}

		//display errors
		$this->displayErrors();

		//output to browser
		if ($this->output_file) {
			imagepng($this->image, $this->output_file);
		} else {
			imagepng($this->image);
		}
		imagedestroy($this->image);
	}

}

