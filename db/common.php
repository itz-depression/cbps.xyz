<?php
function update_csv()
{
	$csvTime = filemtime("cbpsdb.csv");
	
	if(time() > ($csvTime + 600))
	{
		file_put_contents("cbpsdb.csv", file_get_contents("https://raw.githubusercontent.com/KuromeSan/cbps-db/master/cbpsdb.csv"));
	}
}


function isAvalible(string $url)
{
	$headers = get_headers($url);
	if((strstr($headers[0],"200 OK") !== False) || (strstr($headers[0],"302 Found") !== False))
	{
		return true;
	}
	else
	{
		return false;
	}
}

?>