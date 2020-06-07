<?php
include("common.php");
include("dbparser.php");

if(isset($_GET["id"])) {
	$csv = get_csv_entry($_GET["id"]);
	$download = get_download($csv);
	echo("<a href=\"".$download."\">Redirecting.</a>");
	header("Location: ".$download);
}

?>