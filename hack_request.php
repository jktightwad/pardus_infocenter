<?php
	require_once("global.php");
	require_once("modules/security_mod.php");
	require_once("modules/hack_mod.php");
	SecurityMod::login();


//	HackMod::addHackRequest(v($_REQUEST, "universe"), v($_REQUEST, "method"), v($_REQUEST, "pilot"), v($_REQUEST, "faction"));

//	HackMod::addHackRequest($universe, $method, $pilot, $faction);	
	
/* 	header(sprintf(
		"Location: hacks.php",
		$_REQUEST["universe"],
		$_REQUEST["method"],
		$_REQUEST["pilot"],
		$_REQUEST["faction"]
	)) */
	
	$universe = $_REQUEST["universe"];
	$method = $_REQUEST["method"];
	$pilot = $_REQUEST["pilot"];
	$faction = $_REQUEST["faction"];
echo "<script type='text/javascript'>alert($universe);</script>";
//echo $pilot;
	;

?>
