<?php

/* Generate client code */

include('./phpsolarlib.php');
$graph = new PHPSolarLib(186, 113);

$data = array (
		'panel 1' => array ('x' => '000', 'y' => '000', 'orientation' => '000', 'output' => '000'),
		'panel 2' => array ('x' => '062', 'y' => '000', 'orientation' => '000', 'output' => '075'),
		'panel 3' => array ('x' => '124', 'y' => '000', 'orientation' => '000', 'output' => '150')
	      );

$graph->addSolarData($data);
$graph->setSolarPanelMaxOutput(150);
$graph->createSolarpanels();
