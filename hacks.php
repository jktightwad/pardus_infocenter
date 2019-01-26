<?php
	require_once("global.php");
	require_once("modules/security_mod.php");
	require_once("modules/comment_mod.php");
	require_once("modules/hack_mod.php");
	require_once("page_navigator.php");

	SecurityMod::login();

	if (!SettingsMod::ENABLE_HACK_SHARE)
		SecurityMod::logout();
	
	$permissions = $_SESSION["account"]->getPermissions();
	if (!$permissions->has(Permissions::VIEW_HACKS))
		SecurityMod::logout();

	$level = $_SESSION["account"]->getLevel();

	$pageNumber = intval(v($_REQUEST, "page"));
	if ($pageNumber < 1)
		$pageNumber = 1;
	//$universe = $_SESSION["account"]->getUniverse();
	$filters["universe"] = $_SESSION["account"]->getUniverse();
	$filters["method"] = v($_REQUEST, "method");
	$filters["cluster"] = v($_REQUEST, "cluster");
	$filters["pilot"] = v($_REQUEST, "pilot");
	$filters["turnover"] = v($_REQUEST, "turnover");
	$hacks = HackMod::getHacks($filters, $level, $pageNumber, $pageCount);
	$hack_requests = HackMod::getHackRequest();
	$requests["universe"] = $_SESSION["account"]->getUniverse();
	$requests["method"] = v($_REQUEST, "method");
	$requests["faction"] = v($_REQUEST, "factionr");
	$requests["pilot"] = v($_REQUEST, "pilot");

	$hackMethods = array("brute", "skilled", "freak", "guru");
	$factions = array("Empire", "Federation", "Union");
	//$turnoverLimits = array(">=", "<=");

	function drawNavigator() {
		global $pageCount, $pageNumber, $filters, $requests;
		$params = "";
		foreach ($filters as $name => $filter) {
			if ($filter)
				$params .= sprintf("&%s=%s", $name, $filter);
		}
		foreach ($requests as $name => $request) {
			if ($request)
				$params .= sprintf("&%s=%s", $name, $request);
		}
		PageNavigator::draw($pageCount, $pageNumber, 17, $params, "hacks.php");
	}
?>
<html>
<head>
<title><?php echo(SettingsMod::PAGE_TITLE." :: Hacks"); ?></title>
<link rel="stylesheet" href="main.css">
<script src="main.js" type="text/javascript"></script>
<script src="info.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
	function hackDetails(hackId) {
		var leftPos = 0;
		var topPos = 0;
		if (screen) {
			leftPos = (screen.width / 2) - 375;
			topPos = (screen.height / 2) - 275;
		}
		window.open("hack_details.php?id=" + hackId, "_blank", "width=750,height=550,scrollbars=1,resizable=1,left=" + leftPos + ",top=" + topPos);
	}
</script>
</head>
<body>

	<table align="center" width="500">
	<h2 align="center">Hack Requests</h2>
	<tr>
	
		<td>
		<h2 align="center">Submit Request</h2>
				<form method="post" action="hack_request.php">
				<input type="hidden" name="hrequest" value="<?php echo $requests["universe"]; ?>" />
				<table background="<?php echo(SettingsMod::STATIC_IMAGES)?>/bgd.gif" class="messagestyle" align='center'>
				<tr>
					<td>
						<table>
						<tr>
							<td>
								<label>Pilot:&nbsp;</label>
								<input name="pilot" type="text" value="<?php echo($requests["pilot"])?>" style="width:120"/>
							</td>
							<td>
								<label>Hack Method:&nbsp;</label>
								<select name="method" style="width:120">
									<option value="">All</option>
									<?php foreach ($hackMethods as $hackMethod):?>
									<option value="<?php echo($hackMethod)?>" <?php if ($hackMethod == $requests["method"]) echo('selected="selected"')?>><?php echo($hackMethod)?></option>
									<?php endforeach?>
								</select>
							</td>
							<td>
								<label>Faction:&nbsp;</label>
								<select name="faction" style="width:120">
									<?php foreach ($factions as $faction):?>
									<option value="<?php echo($faction)?>" <?php if ($faction == $requests["faction"]) echo('selected="selected"')?>><?php echo($faction)?></option>
									<?php endforeach?>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>

					<td align="center"><input type="submit" value="Submit Request"></td>
				</tr>
				</table>
			
		<td>
		<h2 align="center">Pending Requests</h2>
			<table background="<?php echo(SettingsMod::STATIC_IMAGES)?>/bgd.gif" class="messagestyle" align="center" width="100%">
			<tr>
			</tr>
			<tr>

				<th>&nbsp;</th>
				<th><u>Request Date</u></th>
				<th><u>Universe</u></th>
				<th><u>Hack Target</u></th>
				<th><u>Faction</u></th>
				<th><u>Method</u></th>
			</tr>
			<?php
				$i = 0;
				foreach ($hack_requests as $hackrequest):
				$i++;
			?>
			<tr bgcolor='#0B0B2F' onMouseOver='chOn(this)' onMouseOut='chOut(this)' onClick='chClick(this)'>
				<td align='right' nowrap='nowrap' style='cursor:crosshair' >
					<?php echo(($pageNumber - 1) * SettingsMod::PAGE_RECORDS_PER_PAGE + $i)?>.
				</td>
				<td nowrap='nowrap' style='cursor:crosshair' >
					<script language="javascript">document.write(formatDate(<?php echo(strtotime($hackrequest["date"]) * 1000)?>))</script>
				</td>
				<td align='center' nowrap='nowrap' style='cursor:crosshair' ><?php echo($hackrequest["universe"])?></td>
				<td align='center' nowrap='nowrap' style='cursor:crosshair' ><?php echo($hackrequest["pilot"])?></td>
				<td align='center' nowrap='nowrap' style='cursor:crosshair' ><?php echo($hackrequest["faction"])?></td>
				<td align='center' nowrap='nowrap' style='cursor:crosshair' ><?php echo($hackrequest["method"])?></td>
			</tr>
			<?php endforeach; ?>
			</table>
		</td>
		</td>
	</tr>
	<tr>
	</tr>
	</table>
	
	<table align="center" width="500">
	<h2 align="center">Hack Logs</h2>
	<tr>
		<td>
			<form action="hacks.php" method="GET" style="margin-bottom:0;">
				<input type="hidden" name="universe" value="<?php echo($filters["universe"])?>"/>
				<table background="<?php echo(SettingsMod::STATIC_IMAGES)?>/bgd.gif" class="messagestyle" align='center'>
				<tr>
					<td>
						<table>
						<tr>
							<td>
								<label>Hack Method:&nbsp;</label>
								<select name="method" style="width:120">
									<option value="">All</option>
									<?php foreach ($hackMethods as $hackMethod):?>
									<option value="<?php echo($hackMethod)?>" <?php if ($hackMethod == $filters["method"]) echo('selected="selected"')?>><?php echo($hackMethod)?></option>
									<?php endforeach?>
								</select>
							</td>
							<td width="10">&nbsp;</td>
							<td>
								<label>Cluster:&nbsp;</label>
								<input name="cluster" type="text" value="<?php echo($filters["cluster"])?>" style="width:120"/>
							</td>
							<td>
								<label>Pilot:&nbsp;</label>
								<input name="pilot" type="text" value="<?php echo($filters["pilot"])?>" style="width:120"/>
							</td>
							<td>
							<td>
								<label>Min Turnover:&nbsp;</label>
								<input name="turnover" type="text" value="<?php echo($filters["turnover"])?>" style="width:120"/>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center"><input type="submit" value="Filter"></td>
				</tr>
				</table>
			</form>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td align="center">
			<table background="<?php echo(SettingsMod::STATIC_IMAGES)?>/bgd.gif" class="messagestyle" align="center" width="100%">
			<tr>
				<td colspan="10"><?php drawNavigator()?></td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<th><u>Date</u></th>
				<th><u>Universe</u></th>
				<th><u>Pilot</u></th>
				<th><u>Cluster</u></th>
				<th><u>Location</u></th>
				<th><u>XP</u></th>
				<th><u>Credits</u></th>
				<th><u>Turnover</u></th>
				<th><u>Method</u></th>
				<?php if (SettingsMod::ENABLE_COMMENTS && $permissions->has(Permissions::VIEW_COMMENTS)): ?>
				<th><u>Comments</u></th>
				<?php endif; ?>
				<th><u>Security</u></th>
			</tr>
			<?php
				$i = 0;
				foreach ($hacks as $hack):
				$i++;
			?>
			<tr bgcolor='#0B0B2F' onMouseOver='chOn(this)' onMouseOut='chOut(this)' onClick='chClick(this)'>
				<td align='right' nowrap='nowrap' style='cursor:crosshair' onClick='hackDetails(<?php echo($hack["id"])?>)'>
					<?php echo(($pageNumber - 1) * SettingsMod::PAGE_RECORDS_PER_PAGE + $i)?>.
				</td>
				<td nowrap='nowrap' style='cursor:crosshair' onClick='hackDetails(<?php echo($hack["id"])?>)'>
					<script language="javascript">document.write(formatDate(<?php echo(strtotime($hack["date"]) * 1000)?>))</script>
				</td>
				<td align='center' nowrap='nowrap' style='cursor:crosshair' onClick='hackDetails(<?php echo($hack["id"])?>)'><?php echo($hack["universe"])?></td>
				<td align='right' nowrap='nowrap' style='cursor:crosshair' onClick='hackDetails(<?php echo($hack["id"])?>)'><?php echo($hack["pilot"])?></td>
				<td align='center' nowrap='nowrap' style='cursor:crosshair' onClick='hackDetails(<?php echo($hack["id"])?>)'><?php echo($hack["cluster"])?></td>
				<td align='center' nowrap='nowrap' style='cursor:crosshair' onClick='hackDetails(<?php echo($hack["id"])?>)'><?php printf("%s %s", v($hack, "sector"), v($hack, "coords"))?></td>
				<td align='right' nowrap='nowrap' style='cursor:crosshair' onClick='hackDetails(<?php echo($hack["id"])?>)'><?php echo(number_format($hack["experience"]))?></td>
				<td align='right' nowrap='nowrap' style='cursor:crosshair' onClick='hackDetails(<?php echo($hack["id"])?>)'><?php echo(number_format($hack["credits"]))?></td>
				<td align='right' nowrap='nowrap' style='cursor:crosshair' onClick='hackDetails(<?php echo($hack["id"])?>)'><?php echo(number_format($hack["turnover"]))?></td>
				<td align='center' nowrap='nowrap' style='cursor:crosshair' onClick='hackDetails(<?php echo($hack["id"])?>)'><?php echo($hack["method"])?></td>
				<?php if (SettingsMod::ENABLE_COMMENTS && $permissions->has(Permissions::VIEW_COMMENTS)): ?>
				<td align='center' nowrap='nowrap' style='cursor:crosshair' onClick='hackDetails(<?php echo($hack["id"])?>)'><?php echo(CommentMod::getCommentCount('hack', $hack["id"]))?></td>
				<?php endif; ?>
				<td align='center' nowrap='nowrap' style='cursor:crosshair' onClick='hackDetails(<?php echo($hack["id"])?>)'><?php echo($hack["level"])?></td>
			</tr>
			<?php endforeach; ?>
			<tr>
				<td colspan="99"><?php drawNavigator()?></td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
</body>
</html>
