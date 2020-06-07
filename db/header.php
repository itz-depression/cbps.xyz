<?php
include("common.php");
update_csv();
?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="style.css">
		<script src="common.js"></script>
		<title>CbpsDB - The Unoffical source of Unoffical PSVita Software.</title>
	</head>

	<body>
	<div class="header">
		<p>
			<div class="sitename">
				<a href="/" class="image">
					<img src="img/logo.png" alt="cbpsDB" width="50" height="50">
				</a>
				CbpsDB
			</div>
			
			<div class="sitemap">				
				<div id="apps_page" onclick='open_url("/")' class="sitemap-entry"><a href="/">Apps</a></div>
				<div id="plugins_page" onclick='open_url("/plugins.php")' class="sitemap-entry"><a href="/plugins.php">Plugins</a></div>
				<div id="submit_page" onclick='open_url("/submit.php")' class="sitemap-entry"><a href="/submit.php">Submit</a></div>
			</div>
		</p>
	</div>


