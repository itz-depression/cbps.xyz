<?php

function list_entries(string $itemName)
{
$old_timestamp = microtime();
$count = 0;
$plugins = false;
$edit_mode = false;

if(strcmp($itemName,"PLUGIN") == 0)
{
	$plugins = true;
}
else if(strcmp($itemName,"APP") == 0)
{
	$plugins = false;
}
else if(strcmp($itemName,"EDIT") == 0)
{
	$edit_mode = true;
}

echo('<div class="applist">');

	$CbpsCsv = explode("\n",file_get_contents("cbpsdb.csv"));	

	foreach($CbpsCsv as &$Entry){
		if(strcmp($Entry,"") == 0)
		{
			continue;
		}
		
		$csv = explode(",",$Entry);
		if(!$edit_mode)
		{
			if(strcmp($csv[VISIBLE],"False") == 0)
			{
				continue;
			}
			if($plugins == false)
			{
				if(strcmp($csv[TYPE],"PLUGIN") == 0)
				{
					continue;
				}
			}
			else
			{
				if(strcmp($csv[TYPE],"VPK") == 0)
				{
					continue;
				}	
			}
		}
		if(strcmp($csv[ID],"id") == 0)
		{
			continue;
		}
		
		$DEFAULT_ICON0 = $csv[DOWNLOAD_ICON0];
		if(strcmp($csv[DOWNLOAD_ICON0],"None") == 0)
		{
			if($plugins == false)
				$DEFAULT_ICON0 = "/img/default.png";
			else
				$DEFAULT_ICON0 = "/img/plugin_default.png";
		}
		
		$APP_TITLE = $csv[TITLE];
		
		$hasExtraData = strcmp($csv[DEPENDS],"None") !== 0;
		
		if(!isset($_GET["id"]))
		{
			echo('<a href="?id='.htmlspecialchars($csv[ID],ENT_QUOTES).'" class="nostyle">

						<div class="vita-app" onclick="open_url(\'?id='.htmlspecialchars($csv[ID],ENT_QUOTES).'\');">
							<div class="vita-app-icon">
								<b>'.htmlspecialchars($APP_TITLE,ENT_QUOTES).'</b><br>
								<img src="'.htmlspecialchars($DEFAULT_ICON0,ENT_QUOTES).'" loading="lazy" onerror="on_error(event.target,\''.htmlspecialchars($csv[ID],ENT_QUOTES).'\','.(string)$plugins.');" width="128" height="128" class="bubble">
							</div><br>
							
							<div class="to-bottom">
									<div class="vita-src-download">');
										if(strcmp($csv[DOWNLOAD_SRC],"None") == 0)
										{
											echo('<b>CLOSED SRC</b>');
										}
										else
										{
											echo('<a href="'.htmlspecialchars($csv[DOWNLOAD_SRC],ENT_QUOTES).'">'.htmlspecialchars($csv[DOWNLOAD_SRC],ENT_QUOTES).'</a>');
										}
								echo('</div>
							</div>
							<div class="vita-app-info">
								<font size=1%>'.htmlspecialchars($csv[CREDITS],ENT_QUOTES).'</font>');
								
							
								if($hasExtraData && !$edit_mode)
								{
									echo('<div class="quick-download-area">
											<a href="getdownload.php?id='.htmlspecialchars($csv[ID],ENT_QUOTES).'">
												<div class="download-button" class="nostyle">
													Download '.$itemName.'
												</div>
											</a>

											<a href="getdownload.php?id='.htmlspecialchars($csv[DEPENDS],ENT_QUOTES).'">
												<div class="download-button" class="nostyle">
													Download DATA
												</div>
											</a>
										</div>');
								}
								else if(!$edit_mode)
								{
									echo('<div class="quick-download-area">
											<a href="getdownload.php?id='.htmlspecialchars($csv[ID],ENT_QUOTES).'">
												<div class="download-button" class="nostyle">
													Download '.$itemName.'
												</div>
											</a>
										</div>');
								}
								else
								{
									echo('<div class="quick-download-area">
											<a href="/edit_entry.php?id='.htmlspecialchars($csv[ID],ENT_QUOTES).'">
												<div class="download-button" class="nostyle">
													Edit '.htmlspecialchars($csv[ID],ENT_QUOTES).'
												</div>
											</a>
										</div>');
								}
							echo('
							</div>
						</div>
					</a>');
		}
		else
		{
			if(strcmp($csv[ID],$_GET['id']) !== 0)
			{
				continue;
			}
			
			echo('<div class="vita-app-page">
					
					<div class="to-top">
						<div class="vita-edit-icon">
							<a href="/edit_entry.php?id='.htmlspecialchars($csv[ID],ENT_QUOTES).'"><img src="/img/edit_icon.png" width="32" height="32"></img></a>
						</div>
					</div>
					
					<div class="vita-app-page-icon">
						<img src="'.htmlspecialchars($DEFAULT_ICON0,ENT_QUOTES).'" loading="lazy" onerror="on_error(event.target,\''.htmlspecialchars($csv[ID],ENT_QUOTES).'\');" width="128" height="128" class="bubble">
					</div>
					
					<div class="vita-app-page-title">
						<b>'.htmlspecialchars($APP_TITLE,ENT_QUOTES).' ('.$csv[ID].')'.'</b><br>
					</div>
					<b>'.htmlspecialchars($csv[CREDITS],ENT_QUOTES).'</b>');
					
					echo('<div class="vita-entry-info">');
						if($plugins == true)
						{
							if(strcmp($csv[CONFIG_TYPE],"TAI") == 0)
							{
								$options = get_list($csv[OPTIONS]);
								echo('<div class="vita-app-page-readme">
										'.nl2br(htmlspecialchars(implode("\n",$options))).'
									</div>');
							}
						}
						if(strcmp($csv[DOWNLOAD_README],"None") != 0)
						{
							echo('<div class="vita-app-page-readme">
									'.nl2br(htmlspecialchars(file_get_contents(get_readme($csv)))).'
								</div>');
						}
						else
						{
							echo('<div class="vita-app-page-readme">
									No readme file found.
								</div>');
						}
						if($hasExtraData)
						{
							echo('<div class="quick-download-area">
									<a href=getdownload.php?id='.htmlspecialchars($csv[ID],ENT_QUOTES).'>
										<div class="download-button" class="nostyle">
											Download '.$itemName.'
										</div>
									</a>

									<a href=getdownload.php?id='.htmlspecialchars($csv[DEPENDS],ENT_QUOTES).'>
										<div class="download-button" class="nostyle">
											Download DATA
										</div>
									</a>
								</div>');
						}
						else
						{
							echo('<div class="quick-download-area">
									<a href=getdownload.php?id='.htmlspecialchars($csv[ID],ENT_QUOTES).'>
										<div class="download-button" class="nostyle">
											Download '.$itemName.'
										</div>
									</a>
								</div>');
						}
					echo('</div>');
				echo('</div>');
		}
		$count += 1;
	}
$time_taken = (microtime() - $old_timestamp);
echo('<br><br><div class="item-count"<b>Indexed '.$count." entries in ".$time_taken." seconds</b></div>");
echo("</div>");
echo("</body>");
echo("</html>");	
}
?>