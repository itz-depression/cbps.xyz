<?php
include("header.php");
include("dbparser.php");
?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>

function confirm_submit()
{
	var status = document.getElementById("verify_status");
	
	if(grecaptcha.getResponse() == "")
	{
		status.innerText = "Solve reCaptcha first!";
		return;
	}
	
	if(form_config_type.value == "")
		form_config_type.value = "None";
	if(form_credits.value == "")
		form_credits.value = "None";
	if(form_download_icon0.value == "")
		form_download_icon0.value = "None";
	if(form_download_readme.value == "")
		form_download_readme.value = "None";
	if(form_download_src.value == "")
		form_download_src.value = "None";
	if(form_download_url.value == "")
		form_download_url.value = "None";
	if(form_id.value == "")
		form_id.value = "None";
	if(form_plugin_scope.value == "")
		form_plugin_scope.value = "None";
	if(form_release_type.value == "")
		form_release_type.value = "None";
	if(form_title.value == "")
		form_title.value = "None";
	
	status.innerText = "Submitting...";
	
	document.getElementById("mainform").submit();
	
	window.location = "/submit.php";
}


function is_valid_scope(scope)
{
	is_valid = false;
	if(scope == "*KERNEL")
	{
		is_valid = true;
	}
	else if(scope == "*main")
	{
		is_valid = true;
	}
	else if(scope == "*ALL")
	{
		is_valid = true;
	}
	else if(scope.startsWith("*"))
	{
		if(scope.length == 10) // *titleid
		{
			is_valid = true;
		}
		else
		{
			is_valid = false;
		}
	}
	if(SCOPE_LIST.indexOf(scope) != -1)
	{
		is_valid = false;
	}
	return is_valid
}
function remove_scope()
{
	if(SCOPE_LIST.length >= 1)
	{
		var scope_dropdown = document.getElementById("scope_options");
		SCOPE_LIST.pop(scope_dropdown.selectedIndex);
		scope_dropdown.selectedOptions[0].remove();;
		submit_scope();
	}
	else
	{
		document.getElementById("scope_status").innerText = "Nothing to remove!";
		return;
	}

}
function add_scope()
{
	var scope_dropdown = document.getElementById("scope_options");
	var scope_entry = document.getElementById("scope_name").value.toUpperCase();;
	
	if(scope_entry == "*MAIN")
	{
		scope_entry = "*main"; // thanks sony
	}
	if(is_valid_scope(scope_entry))
	{
		var opt = document.createElement('option');
		opt.value = scope_entry;
		opt.innerText = scope_entry;
		scope_dropdown.add(opt);
		SCOPE_LIST.push(scope_entry);
		scope_entry = document.getElementById("scope_name").value = "";
		submit_scope();
	}
	else
	{
		document.getElementById("scope_status").innerHTML = "Invalid config entry,<br>Config entries start with '*' and followed by a TitleID or 'KERNEL/main'<br><font size=\"2%\">ex: *NPXS100031</font>";
		return;
	}
}
function submit_scope()
{
	if(SCOPE_LIST.length == 0)
	{
		document.getElementById("scope_status").innerText = "Please add atleast 1 config entry!";
		return;
	}
	else
	{
		document.getElementById("scope_status").innerText = "Config entry list updated.";
		form_plugin_scope.value = SCOPE_LIST.join("|");
	}
}
function edit(what_to_edit)
{
	switch(what_to_edit)
	{
		case 'title':
			show_step(step_enter_title);
			break;
		case 'credits':
			show_step(step_enter_credits);
			break;
		case 'options':
			show_step(step_enter_scope);
			break;
		case 'download_icon0':
			show_step(step_enter_icon);
			break;
		case 'download_url':
			show_step(step_enter_file);
			break;
		case 'download_src':
			show_step(step_enter_git);
			break;
		case 'download_readme':
			show_step(step_enter_readme);
			break;
	}
}

function select_update()
{
	var select = document.getElementById("id_select");
	edit(select.selectedOptions[0].value);
}

function submit_credits()
{
	form_credits.value = document.getElementById("credits_entry").value;
}

function submit_title()
{
	form_title.value = document.getElementById("title_entry").value;
}

function submit_download()
{
	form_download_url.value = document.getElementById("download_entry").value;
}

function submit_git()
{
	form_download_src.value = document.getElementById("git_entry").value;
}

function submit_readme()
{
	form_download_readme.value = document.getElementById("readme_url_entry").value;
}

function image_load_failed()
{
	document.getElementById("submit_icon0_preview").src = "/img/default.png";
	form_download_icon0.value = "None";
}

function update_icon0_preview()
{
	document.getElementById("submit_icon0_preview").src = document.getElementById("icon0_url_entry").value;
}

function submit_icon0()
{
	var icon0_url = document.getElementById("submit_icon0_preview").src;
	if(icon0_url.indexOf("/img/default.png") != -1)
	{
		form_download_icon0.value = "None";
		return;
	}
	form_download_icon0.value = icon0_url;

}

function save()
{
	// hide controls
	document.getElementById("id_select").remove();
	document.getElementById("what_to_edit").remove();
	document.getElementById("save_changes").remove();
	show_step(step_please_verify);
}


window.addEventListener('load', function () {
	
	form_download_url = document.getElementById("download_url");
	form_id = document.getElementById("id");
	form_title = document.getElementById("title");
	form_credits = document.getElementById("credits");
	form_download_src = document.getElementById("download_src");
	form_download_icon0 = document.getElementById("download_icon0");
	form_download_readme = document.getElementById("download_readme");
	form_release_type = document.getElementById("type");
	form_config_type = document.getElementById("config_type");
	form_plugin_scope = document.getElementById("scope");


	step_enter_git = document.getElementById("enter_git");
	step_enter_file = document.getElementById("enter_file");
	step_enter_title = document.getElementById("enter_title");
	step_enter_icon = document.getElementById("enter_icon0");
	step_enter_readme = document.getElementById("enter_readme");
	step_enter_credits = document.getElementById("enter_credits");
	step_enter_scope = document.getElementById("enter_scope");
	step_please_verify = document.getElementById("please_verify");


	steps = [step_enter_git,step_enter_file,step_enter_title,step_enter_readme,step_enter_credits,step_please_verify]
	if(step_enter_scope != null)
		steps.push(step_enter_scope);
	if(step_enter_icon != null)
		steps.push(step_enter_icon);

	window.hide_all_steps = function()
	{
		for(var i = 0; i < steps.length; i++)
		{
			steps[i].style.display = 'none'
		}
	}

	window.show_step = function(step)
	{
		hide_all_steps();
		step.style.display = 'inline';
	}

	document.getElementById("save_changes").style.display = 'inline';
	document.getElementById("what_to_edit").style.display = 'block';
	document.getElementById("id_select").style.display = 'inline';
	select_update();
})

</script>

	
<?php 
if(isset($_GET['id'])) 
{
	echo('<div class="applist">');
	echo('<div class="submit-box">');
	echo('<b>Edit '.htmlspecialchars($_GET['id'],ENT_QUOTES).'</b><br>');
	echo('<p id="what_to_edit" style="display: none;">What to edit?</p>');

	$entry_id = $_GET['id'];
	$csv = get_csv_entry($entry_id);
	if($csv == NULL){
		echo("Invalid ID");
		exit();
	}

	echo('<select id="id_select" style="display: none;" class="submit-dropdown" onchange="select_update()">');
		echo('
		<option value="title">Title</option>
		<option value="credits">Developer Credits</option>');
		if(strcmp($csv[TYPE],"VPK") == 0)
		{
			echo('<option value="download_icon0">Icon File</option>');
		}
		echo('
		<option value="download_url">Download URL</option>
		<option value="download_readme">Readme File</option>
		<option value="download_src">Source Code</option>
		');
		if(strcmp($csv[TYPE],"PLUGIN") == 0)
		{
			echo('<option value="options">Config Entry</option>');
		}
	echo('</select>');
	
	echo('
	<div id="please_verify" class="submit-form" >
		<div class="submit-inputs">
			<!-- Hidden Form -->
			<p>One last thing.. you arent a robot now.. are you?</p>
			<form action="/git/edit_entry.php" method="post" id="mainform" target="_blank">
				<input type="text" id="download_url" name="download_url" value="'.htmlspecialchars($csv[DOWNLOAD_URL],ENT_QUOTES).'" hidden="true">
				<input type="text" id="id" name="id" value="'.htmlspecialchars($csv[ID],ENT_QUOTES).'" hidden="true">
				<input type="text" id="title" name="title" value="'.htmlspecialchars($csv[TITLE],ENT_QUOTES).'" hidden="true">
				<input type="text" id="credits" name="credits" value="'.htmlspecialchars($csv[CREDITS],ENT_QUOTES).'" hidden="true">
				<input type="text" id="type" name="type" value="'.htmlspecialchars($csv[TYPE],ENT_QUOTES).'" hidden="true">
				<input type="text" id="scope" name="scope" value="'.htmlspecialchars($csv[OPTIONS],ENT_QUOTES).'" hidden="true">
				<input type="text" id="config_type" name="config_type" value="'.htmlspecialchars($csv[CONFIG_TYPE],ENT_QUOTES).'" hidden="true">
				<input type="text" id="download_src" name="download_src" value="'.htmlspecialchars($csv[DOWNLOAD_SRC],ENT_QUOTES).'" hidden="true">
				<input type="text" id="download_icon0" name="download_icon0" value="'.htmlspecialchars($csv[DOWNLOAD_ICON0],ENT_QUOTES).'" hidden="true">
				<input type="text" id="download_readme" name="download_readme" value="'.htmlspecialchars($csv[DOWNLOAD_README],ENT_QUOTES).'" hidden="true">
				<input type="submit" value="Submit" hidden="true">
				<div id="captcha" class="submit-captcha">
					<div class="g-recaptcha" data-sitekey="6LdT0fIUAAAAAMI8sV9fMMZdrNeeRGGEQ5s6CXf8"></div>
				</div>
			</form>					
			<button onclick="confirm_submit()" id="verify_submit" class="submit-button">Confirm</button>

			<div id="verify_status" class="submit-status">
				Waiting for input...
			</div>
		</div>
	</div>');

	
	if(strcmp($csv[TYPE],"PLUGIN") == 0)
	{
		if(strcmp($csv[CONFIG_TYPE],"TAI") == 0)
		{
			echo('<div id="enter_scope" class="submit-form" >
				<div class="submit-inputs">

				  <select id="scope_options" class="submit-dropdown" value="None">
						');
							$options = get_list($csv[OPTIONS]);
							for($i = 0; $i < sizeof($options); $i++)
							{
								echo('<option value="'.htmlspecialchars($options[$i],ENT_QUOTES).'">'.htmlspecialchars($options[$i],ENT_QUOTES)."</option>\n");
							}
							
							echo('<script> SCOPE_LIST = [');
							for($i = 0; $i < sizeof($options); $i++)
							{
								echo('"'.htmlspecialchars($options[$i],ENT_QUOTES).'"');
								if($i+1 < sizeof($options))
								{
									echo(",");
								}
							}
							echo('];</script>');
						echo('
				  </select><br><br>


				  <textarea cols="80" rows="1" id="scope_name" class="submit-textarea"></textarea>
				  <button onclick="add_scope()" id="scope_add" class="submit-button"><img width="15" height="15" src="/img/add_icon.png"></img></button>
				  <button onclick="remove_scope()"  id="scope_remove" class="submit-button"><img width="15" height="15" src="/img/remove_icon.png"></img></button><br><br>
				</div>


				<div id="scope_status" class="submit-status">
					Waiting for input...
				</div>
			</div>');
		}
		else
		{
			echo('<div id="enter_scope" class="submit-form" >
				<div class="submit-inputs">

				  <select id="scope_options_bootconfig" class="submit-dropdown" value="None">
						<option value="START">Start of File</option>
						');
							$config_vita_lines = explode("\n",file_get_contents("vita_config.txt"));	
							for($i = 0; $i < sizeof($config_vita_lines); $i++)
							{
								echo('<option value="'.$config_vita_lines[$i].'">'.$config_vita_lines[$i]."</option>\n");
							}
						echo('
				  </select><br><br>

				  <button onclick="submit_scope_bootconfig()" id="release_select" class="submit-button">Save Changes</button>
				</div>


				<div id="scope_status_bootconfig" class="submit-status">
					Changes not saved!
				</div>
			</div>');
		}
		
	}
	else
	{
		echo('<div id="enter_icon0" class="submit-form" >
			<p>Enter DIRECT Download URL to icon0.png file:</p>
			<img src="'.htmlspecialchars($csv[DOWNLOAD_ICON0],ENT_QUOTES).'" onload="submit_icon0()" onerror="image_load_failed()" width="128" height="128" class="bubble" id="submit_icon0_preview"><br>
	
			<div class="submit-inputs">
			  <textarea cols="90" rows="1" id="icon0_url_entry" oninput="update_icon0_preview()" class="submit-textarea">'.htmlspecialchars($csv[DOWNLOAD_ICON0],ENT_QUOTES).'</textarea>
			</div>
		</div>');
	}
	echo('
		<div id="enter_title" class="submit-form">
			<p>Please enter the TITLE shown on the PSVita LiveArea Screen below.</p>
			<p>If its a plugin, enter the name of the plugin.</p>
			
			<div class="submit-inputs">
			  <textarea cols="90" rows="1" id="title_entry" class="submit-textarea" oninput="submit_title()">'.htmlspecialchars($csv[TITLE],ENT_QUOTES).'</textarea>
			</div>
		</div>	
		<div id="enter_file" class="submit-form" >
			<p>Enter DIRECT download url...</p>
			<div class="submit-inputs">
			  <textarea cols="90" rows="1" id="download_entry" class="submit-textarea" oninput="submit_download()">'.htmlspecialchars($csv[DOWNLOAD_URL],ENT_QUOTES).'</textarea>
			</div>
			
		</div>	
		<div id="enter_readme" class="submit-form" >
			<p>Please enter the DIRECT URL to the readme file below:</p>

			<div class="submit-inputs">
			  <textarea cols="90" rows="1" id="readme_url_entry" class="submit-textarea" oninput="submit_readme()">'.htmlspecialchars($csv[DOWNLOAD_README],ENT_QUOTES).'</textarea>
			</div>
		</div>	
		<div id="enter_credits" class="submit-form">
			<p>Enter the developer who wrote this application:</p>

			<div class="submit-inputs">
			  <textarea cols="90" rows="1" id="credits_entry" class="submit-textarea" oninput="submit_credits()">'.htmlspecialchars($csv[CREDITS],ENT_QUOTES).'</textarea>
			</div>
		</div>	
		
		<div id="enter_git" class="submit-form" >
			<p>Please enter the URL to download this softwares Src Code or a GIT Repository</p>
			<p>If using Github, its what you get from the "Clone or Download" button</p>
			<font size="2%"><p>ex: https://github.com/TheOfficialFloW/VitaShell.git</p></font>
			<div class="submit-inputs">
				<textarea cols="90" rows="1" id="git_entry" class="submit-textarea" oninput="submit_git()">'.htmlspecialchars($csv[DOWNLOAD_SRC],ENT_QUOTES).'</textarea>
			</div>
		</div>	
		');
	
	echo('<br><br><br>
	<button onclick="save()" id="save_changes" class="submit-button" style="display: none;">Save All Changes</button><br>');
	echo('</div>
	</div>
	</body>
	</html>');
}
else
{
	include("list_releases.php");
	list_entries("EDIT");
}
?>




