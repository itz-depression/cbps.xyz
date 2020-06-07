<?php
if(isset($_POST['github'])){
	echo(exec("python3 get_github_info.py ".escapeshellarg($_POST['github'])));
}
else
{
	echo("POST paramater 'github' is not set.");
}
?>