<?php
	require_once("global.php");
	require_once("modules/security_mod.php");
	require_once("modules/hack_mod.php");
	SecurityMod::login();


//	HackMod::addHackRequest(v($_REQUEST, "universe"), v($_REQUEST, "method"), v($_REQUEST, "pilot"), v($_REQUEST, "faction"));

	
/* 	header(sprintf(
		"Location: hacks.php",
		$_REQUEST["universe"],
		$_REQUEST["method"],
		$_REQUEST["pilot"],
		$_REQUEST["faction"]
	)) */

   
  // echo "Welcome ". $_REQUEST['universe']. "<br />"; */
	
	$universe = $_REQUEST["universe"];
	$pilot = $_REQUEST["pilot"];
	$method = $_REQUEST["method"];
	$faction = $_REQUEST["faction"];
	
	$huniverse = $universe;
	$hpilot = $pilot;
	$hmethod = $method;
	$hfaction = $faction;

	HackMod::addHackRequest($universe, $method, $pilot, $faction);	
	//HackMod::addHackRequest($_REQUEST["universe"], $_REQUEST["pilot"], $_REQUEST["method"], $_REQUEST["method"];	
		header(sprintf(
		"Location: hacks.php"
/* 		,
		$huniverse,
		$hmethod,
		$hpilot,
		$hfaction */
	));
	;

?>
