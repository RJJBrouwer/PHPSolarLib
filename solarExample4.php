<?php

/* Generate client code */

include('./phpsolarlib.php');
$graph = new PHPSolarLib(275, 540);


/* my default data array

	'inverterId' -> 'x' 'y' location of panel on graph, 'orientation' of panel in degrees (-180..180), 'output' panel output value
*/

$data = array (
		'100055460' => array ('x' => '010', 'y' => '010', 'orientation' => '000', 'output' => '0'),
		'100055314' => array ('x' => '074', 'y' => '010', 'orientation' => '000', 'output' => '20'),
		'100055334' => array ('x' => '138', 'y' => '010', 'orientation' => '000', 'output' => '30'),
		'100055388' => array ('x' => '202', 'y' => '010', 'orientation' => '000', 'output' => '40'),
		'100043401' => array ('x' => '074', 'y' => '125', 'orientation' => '000', 'output' => '50'),
		'100055408' => array ('x' => '138', 'y' => '125', 'orientation' => '000', 'output' => '60'),
		'100055282' => array ('x' => '202', 'y' => '125', 'orientation' => '000', 'output' => '70'),
		'100055400' => array ('x' => '138', 'y' => '240', 'orientation' => '000', 'output' => '80'),
		'110005466' => array ('x' => '202', 'y' => '240', 'orientation' => '000', 'output' => '90'),
		'100055331' => array ('x' => '010', 'y' => '400', 'orientation' => '-20', 'output' => '100'),
		'100055332' => array ('x' => '069', 'y' => '378', 'orientation' => '-20', 'output' => '150'),
		'100043433' => array ('x' => '126', 'y' => '356', 'orientation' => '+20', 'output' => '250')
	      );

/*

 add here your SQL query's to obtain your data to populate the $data array

*/


/*
 add data to the graphical area
*/

$graph->setBackgroundColor('0,0,0');

$graph->addSolarData($data);
$graph->setSolarpanelSize(10,7,6,14);
$graph->setSolarpanelMaxOutput(250);
$graph->setSolarpanelFrameColor('120,120,120');
$graph->setSolarpanelIdTextColor('180,180,180');
$graph->setSolarpanelOutputPowerTextColor('yellow');
$graph->setSolarpanelGlowColor(200);
$graph->createSolarpanels();
