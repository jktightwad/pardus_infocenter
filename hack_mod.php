<?php
	require_once("base_mod.php");
	require_once("level_mod.php");
	require_once("xml_helper.php");
// much of this was tweaked from original 1.6 base code without notating changes.
	class HackMod extends BaseMod {
		public static function addHack($universe, $data, $level) {
			$doc = new DOMDocument();
			$doc->loadXML($data);
			$node = XmlHelper::getChildByName($doc->documentElement, "building_positions");
			$buildingPositions = $node ? $doc->saveXML($node) : null;
			$node = XmlHelper::getChildByName($doc->documentElement, "buildings");
			$buildings = $node ? $doc->saveXML($node) : null;
			$node = XmlHelper::getChildByName($doc->documentElement, "ship_status");
			$shipStatus = $node ? $doc->saveXML($node) : null;
			$node = XmlHelper::getChildByName($doc->documentElement, "contacts");
			$contacts = $node ? $doc->saveXML($node) : null;
			$hack = XmlHelper::nodeToArray($doc);
			$date = date("Y-m-d H-i-s", $hack["date"] / 1000);
			$foes = $hack["foes"] ? join(",", $hack["foes"]) : null;
			$friends = $hack["friends"] ? join(",", $hack["friends"]) : null;
			$foeAlliances = $hack["foe_alliances"] ? join(",", $hack["foe_alliances"]) : null;
			$friendAlliances = $hack["friend_alliances"] ? join(",", $hack["friend_alliances"]) : null;
            //$contacts = $hack["contacts"] ? join("|", $hack["contacts"]) : null;
			$conn = self::getConnection();
			$sql =
				sprintf(
					"insert into ".SettingsMod::DB_TABLE_PREFIX."hack(" .
						"`date`, universe, method, location, pilotId, pilot, credits, turnover, experience, " .
						"cluster, sector, coords, shipStatus, buildingPositions, buildings, " .
						"reputation, buildingAmount, foes, friends, foeAlliances, friendAlliances, contacts, level" .
					") " .
					"values (" .
						"'%s', '%s', '%s', '%s', %d, '%s', %d, %d, '%s', '%s', '%s', " .
						"'%s', '%s', '%s', '%s', %d, %d, '%s', '%s', '%s', '%s', '%s', '%s'" .
					")",
					date("Y-m-d H-i-s", $hack["date"] / 1000),
					$universe,
					mysql_real_escape_string($hack["method"]),
					mysql_real_escape_string($hack["location"]),
					mysql_real_escape_string($hack["pilot_id"]),
					mysql_real_escape_string($hack["pilot"]),
					mysql_real_escape_string($hack["credits"]),
                    mysql_real_escape_string($hack["turnover"]),
					mysql_real_escape_string(v($hack, "experience")),
					v($hack, "position") ? mysql_real_escape_string(v($hack["position"], "cluster")) : null,
					v($hack, "position") ? mysql_real_escape_string(v($hack["position"], "sector")) : null,
					v($hack, "position") ? mysql_real_escape_string(v($hack["position"], "coords")) : null,
					mysql_real_escape_string($shipStatus),
					mysql_real_escape_string($buildingPositions),
					mysql_real_escape_string($buildings),
					mysql_real_escape_string($hack["reputation"]),
					mysql_real_escape_string($hack["building_amount"]),
					mysql_real_escape_string($foes),
					mysql_real_escape_string($friends),
					mysql_real_escape_string($foeAlliances),
					mysql_real_escape_string($friendAlliances),
                    mysql_real_escape_string($contacts),
					mysql_real_escape_string($level)
				);
//echo "<script type='text/javascript'>alert('$contacts');</script>";	
			return mysql_query($sql, $conn);
		}
		public static function getHacks($filters, $level, &$pageNumber, &$pageCount) {
			$conn = self::getConnection();
			$join = "";
			$where = sprintf("where is_deleted != 1 and universe = '%s' ", $_SESSION["account"]->getUniverse());
			if ($filters["method"])
				$where .= sprintf("and method = '%s' ", mysql_real_escape_string($filters["method"]));
			if ($filters["cluster"])
                $where .= sprintf("and cluster like '%%%%%s%%%%' ", mysql_real_escape_string($filters["cluster"]));
//			if ($filters["pilot"])
//				$where .= sprintf("and pilot = '%s' ", mysql_real_escape_string($filters["pilot"]));
            // use a like so pilot name doesn't have to be perfect
			if ($filters["pilot"])
                $where .= sprintf("and pilot like '%%%%%s%%%%' ", mysql_real_escape_string($filters["pilot"]));
			// TURNOVER FILTER METHOD
		//	if ($filters["turnoverLimits"]) && ($filters["turnover"])
		//		if ($filters["turnoverLimits"]) = '>='
		//			$where .= sprintf("and turnover >= '%s' ", mysql_real_escape_string($filters["turnover"]));
		//		else if ($filters["turnoverLimits"]) = '<='
		//			$where .= sprintf("and turnover >= '%s' ", mysql_real_escape_string($filters["turnover"]));			
		//		else $where .= sprintf("and turnover >= '%s' ", mysql_real_escape_string($filters["turnover"]));
			//OLD TURNOVER METHOD
			if ($filters["turnover"])
                $where .= sprintf("and turnover >= '%s' ", mysql_real_escape_string($filters["turnover"]));
			// get security level of account, and filter on that
			$level = LevelMod::accountClearance($_SESSION["account"]->getName());
			$join .= "join ".SettingsMod::DB_TABLE_PREFIX."level as l on l.name = h.level ";
			$where .=
				sprintf(
					"and l.level <= %d ",
					intval($level)
				);
			$sql = "select count(*) as cnt from ".SettingsMod::DB_TABLE_PREFIX."hack as h " . $join . $where;
			$result = mysql_query($sql, $conn);
			$row = mysql_fetch_assoc($result);
			$recordCount = $row["cnt"];
			$pageCount = ceil($recordCount / SettingsMod::PAGE_RECORDS_PER_PAGE);
			if ($pageNumber > $pageCount)
				$pageNumber = $pageCount;
			if ($pageNumber < $pageCount)
				$recordsPerPage = SettingsMod::PAGE_RECORDS_PER_PAGE;
			else {
				$recordsPerPage = $recordCount % SettingsMod::PAGE_RECORDS_PER_PAGE;
				if ($recordsPerPage == 0 && $recordCount > 0)
					$recordsPerPage = SettingsMod::PAGE_RECORDS_PER_PAGE;
			}
			$sql =
				sprintf(
					"select * from ( " .
						"select * from (" .
							"select h.* from ".SettingsMod::DB_TABLE_PREFIX."hack as h " .
							$join .
							$where .
							"order by `date` desc " .
							"limit 0, %d" .
						") as tmp1 " .
						"order by `date` asc " .
						"limit 0, %d " .
					") as tmp2 " .
					"order by `date` desc",
					SettingsMod::PAGE_RECORDS_PER_PAGE * $pageNumber,
					$recordsPerPage
				);
			//debug_to_console($sql);			
			$result = mysql_query($sql, $conn);
			$hacks = array();
			while ($row = mysql_fetch_assoc($result)) {
				$hacks[$row["id"]] = $row;
			}
			mysql_close($conn);
			//echo("<script>console.log('PHP: ".json_encode($hacks)."');</script>");
			return $hacks;
		}
		public static function getHack($id) {
			$conn = self::getConnection();
			$sql =
				sprintf(
					"select * from ".SettingsMod::DB_TABLE_PREFIX."hack " .
					"where universe = '%s' and id = %d",
					$_SESSION["account"]->getUniverse(),
					$id
				);
			$result = mysql_query($sql, $conn);
			if ($hack = mysql_fetch_assoc($result)) {
				if ($hack["shipStatus"])
					$hack["shipStatus"] = XmlHelper::xmlToArray($hack["shipStatus"]);
				if ($hack["buildingPositions"])
					$hack["buildingPositions"] = XmlHelper::xmlToArray($hack["buildingPositions"]);
				if ($hack["buildings"])
  					$hack["buildings"] = XmlHelper::xmlToArray($hack["buildings"]);
				if ($hack["foes"])
  					$hack["foes"] = split(",", $hack["foes"]);
  				if ($hack["friends"])
  					$hack["friends"] = split(",", $hack["friends"]);
  				if ($hack["foeAlliances"])
  					$hack["foeAlliances"] = split(",", $hack["foeAlliances"]);
  				if ($hack["friendAlliances"])
                    $hack["friendAlliances"] = split(",", $hack["friendAlliances"]);
                if ($hack["contacts"])
                    $hack["contacts"] = split(";", $hack["contacts"]);
			} else
				$hack = null;
			mysql_close($conn);
			return $hack;
		}
		//hack request additions Jan 2019
		public static function getHackRequest() {
			$conn = self::getConnection();
			$join = "";
			$join .= "left join ".SettingsMod::DB_TABLE_PREFIX."hack as h on hr.pilot = h.pilot and hr.universe = h.universe ";
			$where = sprintf("where 1 ");
			$where .= sprintf("and hr.is_deleted != 1 ");
			$where .= sprintf("and (h.pilot is null or h.date < hr.date) ");
			$where .= sprintf("and hr.universe = '%s' ", $_SESSION["account"]->getUniverse());
			$sql = "select hr.* from ".SettingsMod::DB_TABLE_PREFIX."hack_request as hr " . $join . $where. "order by hr.id "; // . $join .$where; //.
			$result = mysql_query($sql, $conn);		
			$rows = mysql_fetch_assoc($result);		
			$hack_requests = array();
			while ($rows = mysql_fetch_assoc($result)) {
				$hack_requests[$rows["id"]] = $rows;
			}
			mysql_close($conn);
//echo("<script>console.log('PHP: ".json_encode($hack_requests)."');</script>");
			return $hack_requests;
		}
		//hack request additions Jan 2019
		public static function addHackRequest($universe, $method, $pilot, $faction) {
			$conn = self::getConnection();
			$universe = $_SESSION["account"]->getUniverse();
			$pilot = $pilot;
			$method = $method;
			$faction = $faction;
/* 			$sql =
				sprintf(
					"insert into ".SettingsMod::DB_TABLE_PREFIX."hack_request(" .
						"universe, `date`, method, pilot, faction) " .
					"values (" .
						"'%s', '%s', '%s', '%s', '%s') ",
					$universe,
					//date("Y-m-d H-i-s", date() / 1000),
					date();
					$method,
					$pilot,
					$faction
				); */
			$date = date("Y-m-d H-i-s", date() / 1000);
//echo "<script type='text/javascript'>alert('$method');</script>";
			$sql = "insert into ".SettingsMod::DB_TABLE_PREFIX."hack_request (universe, method, pilot, faction) values (" ."'". $universe. "', '".  $method."', '". $pilot."', '". $faction . "') ";
			//$sql = "insert into ".SettingsMod::DB_TABLE_PREFIX."hack_request (universe, method, pilot, faction) values ('%s', '%s', '%s', '%s') ";
//echo "<script type='text/javascript'>alert($sql);</script>";
			return mysql_query($sql, $conn);
		}
		
		public static function updateLevel($id, $level) {
			$conn = self::getConnection();
			$sql = sprintf(
				"update ".SettingsMod::DB_TABLE_PREFIX."hack set level = '%s' where id = %d",
				mysql_real_escape_string($level),
				intval($id)
			);
			mysql_query($sql, $conn);
		}
		public static function deleteHack($hack) {
			$conn = self::getConnection();
			$sql = sprintf(
				"update ".SettingsMod::DB_TABLE_PREFIX."hack set is_deleted = 1 where id = %d",
				intval($hack["id"])
			);
			mysql_query($sql, $conn);
		}
		//hack request additions Jan 2019
		public static function deleteHackRequest($hackRequest) {
			$conn = self::getConnection();
//echo "<script type='text/javascript'>alert('$hackRequest');</script>";
			$sql = sprintf(
				"update ".SettingsMod::DB_TABLE_PREFIX."hack_request set is_deleted = 1 where id = %d",
				intval($hackRequest)
			);
			mysql_query($sql, $conn);
		}
		/**
		 * Send debug code to the Javascript console
		 */ 
		function debug_to_console($data) {
			if(is_array($data) || is_object($data))
			{
				echo("<script>console.log('PHP: ".json_encode($data)."');</script>");
			} else {
				echo("<script>console.log('PHP: ".$data."');</script>");
			}
		}
	}
?>
