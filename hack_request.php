<?php
	require_once("global.php");
	require_once("modules/security_mod.php");
	require_once("modules/hack_mod.php");
	SecurityMod::login();


	HackMod::addHackRequest(v($_REQUEST, "universe"), v($_REQUEST, "method"), v($_REQUEST, "pilot"), v($_REQUEST, "faction"));

	header(sprintf(
		"Location: hacks.php?Universe=%s",
		$_REQUEST["universe"],
		$_REQUEST["method"],
		$_REQUEST["pilot"],
		$_REQUEST["faction"]
	));

?>