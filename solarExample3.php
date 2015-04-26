<?php

/* Generate client code */

include('./phpsolarlib.php');
$graph = new PHPSolarLib(210, 170);

$data = array (
		'100055460' => array ('x' => '000', 'y' => '040', 'orientation' => '-20', 'output' => '020'),
		'100055314' => array ('x' => '056', 'y' => '020', 'orientation' => '-20', 'output' => '100'),
		'100055334' => array ('x' => '112', 'y' => '000', 'orientation' => '-20', 'output' => '230')
	      );

$graph->addSolarData($data);
$graph->setSolarPanelMaxOutput(250);
$graph->setSolarpanelGlowColor(300);

$graph->createSolarpanels();
