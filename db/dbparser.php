<?php
define("ID",0);
define("TITLE",1);
define("CREDITS",2);
define("DOWNLOAD_ICON0",3);
define("DOWNLOAD_ICON0_MIRROR",4);
define("DOWNLOAD_URL",5);
define("DOWNLOAD_URL_MIRROR",6);
define("DOWNLOAD_README",7);
define("DOWNLOAD_README_MIRROR",8);
define("DOWNLOAD_SRC",9);
define("DOWNLOAD_SRC_MIRROR",10);
define("TIME_ADDED",11);
define("CONFIG_TYPE",12);
define("OPTIONS",13);
define("TYPE",14);
define("DEPENDS",15);
define("VISIBLE",16);

define("ORDER_AS_IS",0);
define("ORDER_NEWEST",1);

function remove_entry($csvFile, $id)
{
	$CbpsCsv = explode("\n",file_get_contents($csvFile));	
	for($i = 0; $i < sizeof($CbpsCsv); $i++){
			$csv = explode(",",$CbpsCsv[$i]);
			if(strcmp($csv[ID],$id) == 0)
			{
				unset($CbpsCsv[$i]);
			}
	}
	
	file_put_contents($csvFile,implode("\n",$CbpsCsv));
}

function sort_newest_csv($csvFile)
{
	$csv = array_map('str_getcsv', file($csvFile));
	array_walk($csv, function(&$a) use ($csv) {
	  $a = array_combine($csv[0], $a);
	});
	array_shift($csv); 

	usort($csv, function ($a, $b){
		return $b['time_added'] - $a['time_added'];
	});

	$newCsv = "id,title,credits,download_icon0,download_icon0_mirror,download_url,download_url_mirror,download_readme,download_readme_mirror,download_src,download_src_mirror,time_added,config_type,options,type,depends,visible\n";
	foreach($csv as &$entry)
	{
		$newCsv .= $entry['id'].",".$entry['title'].",".$entry['credits'].",".$entry['download_icon0'].",".$entry['download_icon0_mirror'].",".$entry['download_url'].",".$entry['download_url_mirror'].",".$entry['download_readme'].",".$entry['download_readme_mirror'].",".$entry['download_src'].",".$entry['download_src_mirror'].",".$entry['time_added'].",".$entry['config_type'].",".$entry['options'].",".$entry['type'].",".$entry['depends'].','.$entry['visible']."\n";
	}
	file_put_contents($csvFile,$newCsv);

}

function get_list(string $list){
	$listStr = str_replace("||",".-!-PIPE-!-.",$list);
	$listContents = explode("|",$list);
	for($i = 0; $i >= count($listContents); $i++) {
		$listContents[$i] = str_replace(".-!-PIPE-!-.","|",$listContents[$i]);
	}
	return $listContents;
}

function get_csv_entry(string $id)
{
	$CbpsCsv = explode("\n",file_get_contents("cbpsdb.csv"));	
	foreach($CbpsCsv as &$Entry){
			$csv = explode(",",$Entry);
			
			if(strcmp($csv[ID],$id) == 0)
			{
				return $csv;
			}
	}
}

function get_fallback_icon0(array $entry){
	//Get fallback.
	
	$arr = get_list($entry[DOWNLOAD_ICON0_MIRROR]);
	foreach($arr as &$itm)
	{
		if(isAvalible($itm) == true)
		{
			return $itm;
		}
	}
	if(strcmp($entry[TYPE],"VPK") == 0)
		return "/img/default.png";
	else
		return "/img/plugin_default.png";
}

function get_readme(array $entry){
	
	if(isAvalible($entry[DOWNLOAD_README]))
	{
		return $entry[DOWNLOAD_README];
	}
	else
	{
		$arr = get_list($entry[DOWNLOAD_README_MIRROR]);
		foreach($arr as &$itm)
		{
			if(isAvalible($itm) == true)
			{
				return $itm;
			}
		}
	}
}

function get_download(array $entry){
	
	if(isAvalible($entry[DOWNLOAD_URL]))
	{
		return $entry[DOWNLOAD_URL];
	}
	else
	{
		$arr = get_list($entry[DOWNLOAD_URL_MIRROR]);
		foreach($arr as &$itm)
		{
			if(isAvalible($itm) == true)
			{
				return $itm;
			}
		}
	}
}
?>