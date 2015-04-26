<?php

/* Generate client code */

include('./phpsolarlib.php');
$graph = new PHPSolarLib(60, 113);

$data = array (
		'panel 1' => array ('x' => '000', 'y' => '000', 'orientation' => '000', 'output' => '150')
	      );

$graph->addSolarData($data);
$graph->createSolarpanels();
