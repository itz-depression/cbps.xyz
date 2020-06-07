<?php
include("../common.php");
include("../dbparser.php");


if(isset($_POST["g-recaptcha-response"]))
{
	$url = 'https://www.google.com/recaptcha/api/siteverify';
	$data = array(
		'secret' => 'RECAPTCHA_SECRET',
		'response' => $_POST["g-recaptcha-response"]
	);
	$options = array(
		'http' => array (
			'method' => 'POST',
			'content' => http_build_query($data)
		)
	);
	$context  = stream_context_create($options);
	$verify = file_get_contents($url, false, $context);
	$captcha_success=json_decode($verify);
	if ($captcha_success->success==false) {
		echo "Recaptcha was not solved successfully.";
		die();
	} else if ($captcha_success->success==true) {
		if(strcmp($captcha_success->hostname,"db.cbps.xyz") !== 0)
		{
			echo("Recaptcha returned incorrect hostname.");
			die();	
		}
	}
}
else
{
	echo("No recaptcha response data sent.");
	die();
}

exec("sh get_latest.sh");

$branch_no = "add-entry-".(string)rand();

$csvdata = file_get_contents("cbps-db/cbpsdb.csv");

$id = "None";
$title = "None";
$credits = "None";
$type = "None";
$scope = "None";
$download_icon0 = "None";
$download_url = "None";
$download_readme = "None";
$download_src = "None";
$config_type = "None";


if(isset($_POST['id']))
	$id = $_POST['id'];
if(isset($_POST['title']))
	$title = $_POST['title'];
if(isset($_POST['credits']))
	$credits = $_POST['credits'];
if(isset($_POST['type']))
	$type = $_POST['type'];
if(isset($_POST['scope']))
	$scope = $_POST['scope'];
if(isset($_POST['config_type']))
	$config_type = $_POST['config_type'];
if(isset($_POST['download_icon0']))
	$download_icon0 = $_POST['download_icon0'];
if(isset($_POST['download_url']))
	$download_url = $_POST['download_url'];
if(isset($_POST['download_readme']))
	$download_readme = $_POST['download_readme'];
if(isset($_POST['download_src']))
	$download_src = $_POST['download_src'];



$existing_tids = (array)null;

$csvEntries = explode("\n",$csvdata);
foreach($csvEntries as &$Entry){
	$csv = explode(',',$Entry);
	array_push($existing_tids,$csv[ID]);
}

$warn_user_might_be_idot = false;

$i = 1;
$nId = $id;
while(in_array($nId,$existing_tids))
{
	$warn_user_might_be_idot = true;
	$nId = $id."_".sprintf("%02d", $i);
	$i += 1;
}
$id = $nId;

$csvdata .= $id.",".$title.",".$credits.",".$download_icon0.",None,".$download_url.",None,".$download_readme.",None,".$download_src.",None,".(string)(time()).",".$config_type.",".$scope.",".$type.",None,True\n";

file_put_contents("cbps-db/cbpsdb.csv",$csvdata);
sort_newest_csv("cbps-db/cbpsdb.csv");
exec("sh push_latest.sh \"".$branch_no."\"");
$prepend = "";
if($warn_user_might_be_idot)
{
	$prepend .= "Warning: titleid was found in DB allready. User is possibly an idiot. ";
}
header("Location: ".exec("python3 create_pr.py ".escapeshellarg($branch_no)." ".escapeshellarg('Add '.$title)." ".escapeshellarg($prepend." ".$id."\t".$title."   ".$credits."   ".$download_icon0."   None   ".$download_url."   None   ".$download_readme."   None   ".$download_src."   None   ".(string)(time())."   ".$config_type."   ".$scope."   ".$type."   None   True")));

?>