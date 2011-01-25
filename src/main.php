<?php

require_once 'vhostcreator.class.php';

try {
	$xhost = new VhostCreator();
	$xhost->getEntries();
	$xhost->writeVhost();
}catch (Exception $e) {
	print $e->getMessage();
}
