<?php
include("header.php");
include("dbparser.php");


?>
<div class="applist">
	<div class="submit-box">
		<b>So what are you doing exactly?</b><br>
		
		<a href="/edit_entry.php" class="nostyle">
			<div class="submit-option" onclick="open_url('/edit_entry.php');">
				<div class="submit-action-icon">
					<img src="/img/edit.png" width="128" height="128">
				</div>
				<p>Edit existing entry</p>
				<font size="2%">
				<p>Use this if the software is allready in CbpsDB, but it is outdated.</p>
				<p>(eg: if a new version is released)</p>
				</font>
			</div>
		</a>
		
		<a href="/add_entry.php" class="nostyle">
			<div class="submit-option" onclick="open_url('/add_entry.php');">
				<div class="submit-action-icon">
					<img src="/img/add.png" width="128" height="128">
				</div>
				<p>Add new entry</p>
				<font size="2%">
				<p>Use this for software that is not listed in CbpsDB.</p>
				<p>(eg: it was just released)</p>
				</font>
			</div>
		</a>
		
</div>

</body>
</html>