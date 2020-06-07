<?php
include("common.php");
include("dbparser.php");

if(isset($_GET["id"])) {
	$csv = get_csv_entry($_GET["id"]);
	$icon = get_fallback_icon0($csv);
	echo("<a href=\"".$icon."\">Redirecting.</a>");
	header("Location: ".$icon);
}

?>