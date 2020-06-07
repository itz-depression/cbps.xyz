<?php
$vote = $_REQUEST['vote'];

//get content of textfile
$filename = "poll_result.txt";
$content = file($filename);

//put content in array
$array = explode("||", $content[0]);
$ALL = $array[0];
$BLACK = $array[1];

if ($vote == 0) {
  $ALL = $ALL + 1;
}
if ($vote == 1) {
  $BLACK = $BLACK + 1;
}

//insert votes to txt file
$insertvote = $ALL."||".$BLACK;
$fp = fopen($filename,"w");
fputs($fp,$insertvote);
fclose($fp);
?>

<h2>Result:</h2>
<table>
<tr>
<td>ALL:</td>
<td><img src="pics/poll/poll.gif"
width='<?php echo(100*round($ALL/($BLACK+$ALL),2)); ?>'
height='20'>
<?php echo(100*round($ALL/($BLACK+$ALL),2)); ?>%
</td>
</tr>
<tr>
<td>BLACK:</td>
<td><img src="pics/poll/poll.gif"
width='<?php echo(100*round($BLACK/($BLACK+$ALL),2)); ?>'
height='20'>
<?php echo(100*round($BLACK/($BLACK+$ALL),2)); ?>%
</td>
</tr>
</table>