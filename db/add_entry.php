<?php
include("header.php");
include("dbparser.php");
?>
<script type="text/javascript" src="lib/zip.js"></script>
<script type="text/javascript" src="lib/zip-ext.js"></script>
<script type="text/javascript" src="pako.js"></script>
<script type="text/javascript" src="self.js"></script>
<script type="text/javascript" src="sfo.js"></script>
<script type="text/javascript" src="sha256.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<script>zip.workerScriptsPath = "lib/";</script>
<script>
TYPE = "";
CONFIG_TYPE = "";
KPLUGIN = false;
SCOPE_LIST = [];

//Bye-Bye CORS.
XMLHttpRequest.prototype.openCORS = XMLHttpRequest.prototype.open;

(function() {
	var cors_api_host = 'db.cbps.xyz:88';
	var cors_api_url = 'https://' + cors_api_host + '/';
	var slice = [].slice;
	var origin = window.location.protocol + '//' + window.location.host;
	var open = XMLHttpRequest.prototype.open;
	XMLHttpRequest.prototype.open = function() {
		var args = slice.call(arguments);
		var targetOrigin = /^https?:\/\/([^\/]+)/i.exec(args[1]);
		if (targetOrigin && targetOrigin[0].toLowerCase() !== origin &&
			targetOrigin[1] !== cors_api_host) {
			args[1] = cors_api_url + args[1];
		}
		return open.apply(this, args);
	};
})();

found_icon0 = false;
found_title = false;

function do_sfo(sfo_bytes)
{
	SfoTable = parse_sfo(sfo_bytes);
	
	Title = SfoTable["TITLE"];
	if(Title !== undefined)
	{
		form_title.value = Title
		remove_check(form_title);
	}
	TitleId = SfoTable["TITLE_ID"]
	if(TitleId !== undefined)
	{
		
		form_id.value = TitleId;
		remove_check(form_id);
	}
	found_title = true;
	continue_from_release_download();
}

function continue_from_release_download()
{
	if(icon0_get_requests_needed <= icon0_get_requests_completed.length)
	{
		found_icon0 = true;
	}
	if(found_icon0)
	{
		if(found_title)
		{
			var select = document.getElementById('release_select')
			select.disabled = false;
			verify_and_submit();
		}
	}
}

function find_icon0(icon_bytes)
{
	sha256_data = sha256(icon_bytes);
	console.log("expected sha: "+sha256_data);

	var status = document.getElementById("process_status")
	status.innerText = "Locating icon0.png...";	
	
	icon_list = window.git_info.icons;
	if(icon_list === "None")
	{
		window.icon0_get_requests_needed = 0;
		window.icon0_get_requests_completed = []
		
		form_download_icon0.value = "None";
		found_icon0 = true;
		continue_from_release_download();
		return;
	}
	
	window.icon0_get_requests_needed = icon_list.length;
	window.icon0_get_requests_completed = []
	for(var i = 0; i < icon_list.length; i++)
	{
		var xhr = new XMLHttpRequest()
		xhr.open("GET", icon_list[i])
		xhr.responseType = "arraybuffer";
		xhr.i = i;
		xhr.onreadystatechange = function() {
			if(this.readyState == 4 && this.status == 200) {
				var git_icon0 = sha256(new Uint8Array(this.response));
				console.log("got: "+git_icon0);
				status.innerText = "Checking file: "+this.i;	

				if(git_icon0 === sha256_data)
				{
					status.innerText = "Found icon0.png in git repo.";
					form_download_icon0.value = icon_list[this.i];
					remove_check(form_download_icon0);
					found_icon0 = true;
					continue_from_release_download();
				}
				else if(icon0_get_requests_completed.indexOf(icon_list[this.i]) == -1)
				{
					icon0_get_requests_completed.push(icon_list[this.i])
					continue_from_release_download();
				}
			
			}
		}
		xhr.send();
	}
}

function update_progress(oEvent) {
  if (oEvent.lengthComputable) {
    var percentComplete = Math.floor(oEvent.loaded / oEvent.total * 100);
    var status = document.getElementById("process_status")
	status.innerText = "Downloading "+percentComplete+"%";

	var progress_bar = document.getElementById("release_progress_front");
	var max = document.getElementById("release_progress_back").clientWidth;
	var number_px = Math.floor(oEvent.loaded / oEvent.total * max);
	progress_bar.style.width = number_px;
	
  } else {
    var status = document.getElementById("process_status")
	status.innerText = "Downloading ";
  }
}

function process_plugin(url)
{
	form_download_icon0.value = "None";
	found_icon0 = true; // plugins have no icon0.
	remove_check(form_download_icon0);
	
	var xhr = new XMLHttpRequest();
	xhr.addEventListener("progress", update_progress);
	xhr.open("GET",url);
	xhr.responseType = "arraybuffer";
	xhr.onreadystatechange = function() {
		if(this.readyState == 4 && this.status == 200) {
			var status = document.getElementById("process_status");
			status.innerText = "Parsing SELF.";
			var SceModuleInfo = get_module_info(this.response);
			form_id.value = SceModuleInfo.name;
			remove_check(form_id);
			verify_and_submit();	
		}
	}
	xhr.send();
}

function process_vpk(url)
{
	XMLHttpRequest.prototype.openOg = XMLHttpRequest.prototype.open;
	XMLHttpRequest.prototype.open = function(method,ourl)
	{
		this.addEventListener("progress", update_progress);
		return this.openOg(method,ourl);
	}	

	var completed_sfo = false;
	var completed_icon = false;
	
	zip.createReader(new zip.HttpReader(url), function(zipReader){
		zipReader.getEntries(function(entries){
			XMLHttpRequest.prototype.open = XMLHttpRequest.prototype.openOg;
			icon0_exists = false;
				for(i = 0; i < entries.length; i++)
				{
					entry = entries[i];
					
					if(entry.filename == "sce_sys/param.sfo")
					{
						entry.getData(new zip.BlobWriter("application/octet-stream"), function(blob) {
						sfo_blob = blob
						new Response(blob).arrayBuffer().then(do_sfo);
						
						}, function(current, total) {
							var percentComplete = Math.floor(current / total * 100);
							document.getElementById("process_status").innerText = "Extracting param.sfo "+percentComplete+"%";
							
							
							var max = document.getElementById("release_progress_back").clientWidth;
							var number_px = Math.floor(current / total * max);
							document.getElementById("release_progress_front").style.width = number_px;

						});
						
					}
					else if(entry.filename == "sce_sys/icon0.png")
					{
						icon0_exists = true;
						entry.getData(new zip.BlobWriter("image/png"), function(blob) {
						new Response(blob).arrayBuffer().then(find_icon0);


						}, function(current, total) {
							var percentComplete = Math.floor(current / total * 100);
							document.getElementById("process_status").innerText = "Extracting icon0.png "+percentComplete+"%";
							
							var max = document.getElementById("release_progress_back").clientWidth;
							var number_px = Math.floor(current / total * max);
							document.getElementById("release_progress_front").style.width = number_px;
						});
						
					}					
				}

			if(icon0_exists == false)
			{
				console.log("no icon0 found");
				window.icon0_get_requests_needed = 0;
				window.icon0_get_requests_completed = []
				found_icon0 = true;
				form_download_icon0.value = "None";
				remove_check(form_download_icon0);
				continue_from_release_download();
			}
	   });
	}, onerror);
}

function set_type(type)
{
	window.TYPE = type;
	show_step(step_process_release);
	if(type == "VPK")
	{
		process_vpk(form_download_url.value)
		form_release_type.value = "VPK";
		remove_check(form_plugin_scope);
		remove_check(form_release_type);
		remove_check(form_config_type);
	}
	if(type == "PLUGIN")
	{
		remove_check(form_release_type);
		form_release_type.value = "PLUGIN";
		process_plugin(form_download_url.value);
	}
}

function set_config_type(type)
{
	window.CONFIG_TYPE = type;
	form_config_type.value = type;
	remove_check(form_config_type);
	
	if(type == "TAI")
	{
		form_plugin_scope.value = "*KERNEL";
		remove_check(form_plugin_scope);
	}
	
	verify_and_submit();
}

function select_release()
{	
	var select = document.getElementById('release_select')
	select.disabled = true;
	
	var status = document.getElementById("file_status")
	status.innerText = "Checking Release Type.";
	
	var url = document.getElementById("releases_options").value;
	form_download_url.value = url
	remove_check(form_download_url);
	
	xhr = new XMLHttpRequest();
	xhr.addEventListener("load", function(){ 
		filename = xhr.getResponseHeader("content-disposition");
		
		if(filename.toLowerCase().indexOf("vpk") != -1)
		{
			set_type("VPK");
		}
		else if(url.toLowerCase().indexOf("vpk") != -1)
		{
			set_type("VPK");
		}
		else if(filename.toLowerCase().indexOf("suprx") != -1)
		{
			CONFIG_TYPE = "TAI";
			form_config_type.value = "TAI"
			remove_check(form_config_type);
			set_type("PLUGIN");
		}
		else if(url.toLowerCase().indexOf("suprx") != -1)
		{
			CONFIG_TYPE = "TAI";
			form_config_type.value = "TAI"
			remove_check(form_config_type);
			
			set_type("PLUGIN");
		}
		else if(filename.toLowerCase().indexOf("skprx") != -1)
		{
			KPLUGIN = true;
			set_type("PLUGIN");
		}
		else if(url.toLowerCase().indexOf("skprx") != -1)
		{
			KPLUGIN = true;
			set_type("PLUGIN");
		}
		else
		{
			select_type();
		}
		
	});
	xhr.open("HEAD",url,true);
	xhr.send();
	
}

function remove_check(form)
{
	var i = check_not_none.indexOf(form)
	if(i != -1)
	{
		check_not_none.splice(i,1)
	}
}

function get_git_info()
{
	
	var git_repo = document.getElementById("git_repo");
	var status = document.getElementById("git_status");
	var next = document.getElementById("git_next");
	
	next.disabled = true;
	status.innerText = "Retreiving information from GIT...";
	
	var http = new XMLHttpRequest();
	var url = '/git/get_github_info.php';
	var params = encodeURI('github='+git_repo.value);
	
	http.open('POST', url, true);
	http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	http.send(params)
	
	http.onreadystatechange = function() {
		if(http.readyState == 4 && http.status == 200) {
			console.log(http.responseText)
			try{
				window.git_info = JSON.parse(http.responseText)
			}
			catch(e)
			{
				window.git_info = {"credits":"None","name":"None","latest_releases":"None","readme_md":"None", "icons":"None","git_uri":git_repo.value}
				form_download_src.value = git_repo.value;
				remove_check(form_download_src);
				form_download_readme.value = "None";
				form_credits.value = "None";
				file_not_listed();
				return;
			}
			git_repo.value = git_info.git_uri;
			
			var releases = git_info.latest_releases;
			document.getElementById("title_entry").value = git_info.name;
			
			if(releases !== "None")
			{
				var releases_dropdown = document.getElementById("releases_options");
				
				for(var i = 0; i < releases.length; i++)
				{
					var opt = document.createElement('option');
					opt.value = releases[i];
					opt.innerText = releases[i].substring(releases[i].lastIndexOf('/')+1);
					releases_dropdown.add(opt);
				}
				
				form_download_readme.value = git_info.readme_md
				form_download_src.value = git_info.git_uri;
				remove_check(form_download_src);
				form_credits.value = git_info.credits;
				show_step(step_select_file);
			}
			else
			{
				form_download_readme.value = git_info.readme_md
				form_download_src.value = git_info.git_uri;
				remove_check(form_download_src);
				form_credits.value = git_info.credits;
				file_not_listed();
			}
		}
	}
}

function closed_src()
{
	window.git_info = {"credits":"None","latest_releases":"None","readme_md":"None", "icons":"None","git_uri":"None"}
	form_download_readme.value = "None"
	form_download_src.value = "None";
	form_credits.value = "None";
	remove_check(form_download_src);
	file_not_listed();
}

function file_not_listed()
{
	var deactivate = document.getElementById('releases_options')
	var activate = document.getElementById('releases_options_inactive')
	
	deactivate.id = "releases_options_inactive";
	activate.id = "releases_options";
	
	var deactivate = document.getElementById('file_status')
	var activate = document.getElementById('file_status_inactive')
	
	deactivate.id = "file_status_inactive";
	activate.id = "file_status";

	var deactivate = document.getElementById('release_select')
	var activate = document.getElementById('release_select_inactive')
	
	deactivate.id = "release_select_inactive";
	activate.id = "release_select";
	
	show_step(step_enter_file);
}

function verify_and_submit()
{
	for(var i = 0; i < check_not_none.length; i++)
	{
		var form = check_not_none[i]
		console.log(form)
		if(form.value !== "None")
		{
			continue;
		}
		
		switch(form)
		{
			case form_download_src:
				show_step(step_enter_git);
				return;
			case form_download_url:
				file_not_listed();
				return;
			case form_download_icon0:
				show_step(step_enter_icon);
				return;
			case form_download_readme:
				show_step(step_enter_readme);
				return;
			case form_id:
				show_step(step_enter_title_id);
				return;
			case form_title:
				show_step(step_enter_title);
				return;
			case form_credits:
				edit_credits();
				return;
			case form_release_type:
				show_step(step_enter_type);
				return;
			case form_config_type:
				show_step(step_enter_config_type);
				return;
			case form_plugin_scope:
				if(CONFIG_TYPE == "TAI")
					show_step(step_enter_scope);
				else if(CONFIG_TYPE = "BOOT")
					show_step(step_enter_scope_bootconfig);
				return;
			default: 
				return;
		}
	}
	edit_credits();
}

function confirm_submit()
{
	var status = document.getElementById("verify_status");
	
	if(grecaptcha.getResponse() == "")
	{
		status.innerText = "Solve reCaptcha first!";
		return;
	}
	status.innerText = "Submitting...";
	
	document.getElementById("mainform").submit();
	
	window.location = "/submit.php";
}

function submit_title()
{
	form_title.value = document.getElementById("title_entry").value;
	remove_check(form_title);
	verify_and_submit();
}

function submit_title_id()
{
	var TitleId = document.getElementById("title_id_entry").value;
	TitleId = TitleId.toUpperCase();
	if(TitleId.length != 9)
	{
		
		document.getElementById("title_id_status").innerText = "Invalid Title ID";
		return;
	}
	
	form_id.value = TitleId;
	remove_check(form_id);
	verify_and_submit();
}

function submit_icon0()
{
	var icon0_url = document.getElementById("submit_icon0_preview").src;
	if(icon0_url.indexOf("/img/default.png") != -1)
	{
		document.getElementById("icon0_status").innerText = "Invalid URL";
		return;
	}
	form_download_icon0.value = icon0_url;
	remove_check(form_download_icon0);
	verify_and_submit();

}

function submit_readme()
{
	form_download_readme.value = document.getElementById("readme_url_entry").value;
	remove_check(form_download_readme);
	verify_and_submit();
}

function no_readme_exists()
{
	form_download_readme.value = "None";
	remove_check(form_download_readme);
	verify_and_submit();
}

function no_icon0_exists()
{
	form_download_icon0.value = "None";
	remove_check(form_download_icon0);
	verify_and_submit();
}

function image_load_failed()
{
	document.getElementById("submit_icon0_preview").src = "/img/default.png";
}

function update_icon0_preview()
{
	document.getElementById("submit_icon0_preview").src = document.getElementById("icon0_url_entry").value;
}

function edit_credits()
{
	var currentCredits = form_credits.value;
	if(currentCredits == "None")
	{
		currentCredits = "";
	}
	document.getElementById("credits_entry").value = currentCredits;
	show_step(step_enter_credits);
}

function submit_credits()
{
	form_credits.value = document.getElementById("credits_entry").value;
	show_step(step_please_verify);
	XMLHttpRequest.prototype.open = XMLHttpRequest.prototype.openCORS;
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
		if(scope.length == 10) // titleid 
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
		scope_dropdown.selectedOptions[0].remove();
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
	}
	else
	{
		document.getElementById("scope_status").innerHTML = "Invalid config entry,<br>Config entries start with '*' and followed by a TitleID or 'KERNEL/main'<br><font size=\"2%\">ex: *NPXS100031</font>";
		return;
	}
}

function submit_scope_bootconfig()
{
	var options_bootconfig = document.getElementById("scope_options_bootconfig")
	remove_check(form_plugin_scope);
	form_plugin_scope.value = options_bootconfig.selectedOptions[0].value + "|- load"
	verify_and_submit();
}
function tos_accept()
{
	show_step(step_enter_git);
}
function submit_scope()
{
	if(SCOPE_LIST.length == 0)
	{
		document.getElementById("scope_status").innerText = "Please add atleast 1 config entry!";
	}
	else
	{
		form_plugin_scope.value = SCOPE_LIST.join("|");
		remove_check(form_plugin_scope);
		verify_and_submit();
	}
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
	
	
	step_accept_tos = document.getElementById("terms_of_service");
	step_enter_git = document.getElementById("submit_git");
	step_select_file = document.getElementById("select_file");
	step_enter_file = document.getElementById("enter_file");
	step_enter_title = document.getElementById("enter_title");
	step_enter_title_id = document.getElementById("enter_title_id");
	step_enter_icon = document.getElementById("enter_icon0");
	step_enter_readme = document.getElementById("enter_readme");
	step_enter_credits = document.getElementById("enter_credits");
	step_enter_scope = document.getElementById("enter_scope");
	step_enter_scope_bootconfig = document.getElementById("enter_scope_bootconfig");
	step_enter_config_type = document.getElementById("enter_config_type");
	step_please_verify = document.getElementById("please_verify");
	
	step_enter_type = document.getElementById("enter_type");
	step_process_release = document.getElementById("process_release");
	
	steps = [step_accept_tos,step_enter_git,step_select_file,step_enter_file,step_enter_title,step_enter_title_id,step_please_verify,step_enter_icon,step_enter_readme,step_enter_credits,step_enter_config_type,step_enter_type,step_process_release,step_enter_scope_bootconfig,step_enter_scope];
	check_not_none = [form_id,form_title,form_download_src,form_download_url,form_download_icon0,form_download_readme,form_release_type,form_config_type,form_plugin_scope]
	
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

	show_step(step_accept_tos);
})


</script>


<div class="applist">
	<div class="submit-box">
		<b>Add Entry</b><br>
		
		<div id="terms_of_service" class="submit-form" >
			<p>CBPSDB Code of Conduct:</p>
			<p>CBPSDB encourages a welcoming environment that is inclusive to all homebrew. However, we take a stance against hate homebrew.</p>
			<p>Hate homebrew includes:</p>
			<p>Homebrew containing malicious code.</p>
			<p>Homebrew that has retail privilege (retail games or retail application) unless the intent of this subject has changed beyond recognition in that its original purpose has changed.</p>
			<p>Homebrew that are illegal in the jurisdiction of the US, exclusive of the tools used to generate the homebrew.</p>
			<p>Homebrew submission with the intent to take down CBPSDB.</p>
			<p><b>We reserve the right to ban your IP if necessary.</b></p>
			<div class="submit-inputs">
				<button onclick="tos_accept()" id="tos_next" class="submit-button">I accept</button>
			</div>
		</div>	
		
		<div id="submit_git" class="submit-form" >
			<p>Please enter the URI to download this softwares Src Code or a GIT Repository</p>
			<p>If using Github, its what you get from the "Clone or Download" button</p>
			<font size="2%"><p>ex: https://github.com/TheOfficialFloW/VitaShell.git</p></font>
			<div class="submit-inputs">
				<textarea cols="90" rows="1" id="git_repo" class="submit-textarea" value=""></textarea>
				<button onclick="get_git_info()" id="git_next" class="submit-button">Next</button>
			</div>
			
			<div id="git_status" class="submit-status">
				Waiting for input...
			</div>	
			
			<div class="submit-skip">
					<font size="2%"><a onclick="closed_src()" class="white">Closed src?</a></font>
			</div>
		</div>	
		
		<div id="select_file" class="submit-form" >
			<p>The following files where found from Git</p>
			<p>Please select which one you are trying to add to CbpsDB below:</p>
			
			<div class="submit-inputs">

				<select id="releases_options" class="submit-dropdown">

				</select>
				<button onclick="select_release()" id="release_select" class="submit-button">Select</button>

			</div>
			
			<div id="file_status" class="submit-status">
				Waiting for input...
			</div>
			
			<div class="submit-skip">
					<font size="2%"><a onclick="file_not_listed();" class="white">Not listed?</a></font>
			</div>
		</div>	
		
		<div id="enter_file" class="submit-form" >
			<p>Could not find the Release file automatically</p>
			<p>Please enter the DIRECT DOWNLOAD URL to the Release Files below:</p>
			
			<div class="submit-inputs">
		
			  <textarea cols="90" rows="1" id="releases_options_inactive" class="submit-textarea"></textarea>
			  <button onclick="select_release()" id="release_select_inactive" class="submit-button">Select</button><br>

			</div>
			
			<div id="file_status_inactive" class="submit-status">
				Waiting for input...
			</div>
		</div>	
		

		<div id="enter_title_id" class="submit-form" >
			<p>Could not find the ID automatically</p>
			<p>Please enter the TITLE ID off this application (or module_name if its a plugin) below:</p>
			
			<div class="submit-inputs">
		
			  <textarea cols="90" rows="1" maxlength="9" id="title_id_entry" class="submit-textarea"></textarea>
			  <button onclick="submit_title_id()" id="title_id_submit" class="submit-button">Set Title ID</button><br>

			</div>
			
			<div id="title_id_status" class="submit-status">
				Waiting for input...
			</div>
		</div>	
		
		<div id="enter_title" class="submit-form" >
			<p>Could not find the TITLE automatically</p>
			<p>Please enter the TITLE shown on the PSVita LiveArea Screen below.</p>
			<p>If its a plugin, enter the name of the plugin.</p>
			
			<div class="submit-inputs">
		
			  <textarea cols="90" rows="1" id="title_entry" class="submit-textarea"></textarea>
			  <button onclick="submit_title()" id="title_submit" class="submit-button">Set Title</button><br>

			</div>
			
			<div id="title_status" class="submit-status">
				Waiting for input...
			</div>
		</div>	
		
		
		<div id="enter_icon0" class="submit-form" >
			<p>Could not find the icon0.png file automatically</p>
			<p>Please enter the DIRECT URL to the icon0.png file below:</p>
			<p>icon0.png is the icon shown on the PSVita LiveArea Screen.</p>
			<img src="/img/default.png" onerror="image_load_failed()" width="128" height="128" class="bubble" id="submit_icon0_preview"><br>
	
			<div class="submit-inputs">
		
			  <textarea cols="90" rows="1" id="icon0_url_entry" oninput="update_icon0_preview()" class="submit-textarea"></textarea>
			  <button onclick="submit_icon0()" id="icon0_url_submit" class="submit-button">Set URL</button><br>

			</div>
	
			<div class="submit-skip">
					<font size="2%"><a onclick="no_icon0_exists()" class="white">No icon?</a></font>
			</div>
	
			<div id="icon0_status" class="submit-status">
				Waiting for input...
			</div>
		</div>	


		<div id="enter_scope" class="submit-form" >
			<p>Could not find where to install this plugin.</p>
			<p>This is where it is placed inside taihen config.txt</p>
			<p>Please add <b>ALL</b> the modules its suppost to run under</p>
			
			<div class="submit-inputs">

			  <select id="scope_options" class="submit-dropdown" value="None">

			  </select><br><br>


			  <textarea cols="80" rows="1" id="scope_name" class="submit-textarea"></textarea>
			  <button onclick="add_scope()" id="scope_add" class="submit-button"><img width="15" height="15" src="/img/add_icon.png"></img></button>
			  <button onclick="remove_scope()"  id="scope_remove" class="submit-button"><img width="15" height="15" src="/img/remove_icon.png"></img></button><br><br>

			  <button onclick="submit_scope()" id="release_select" class="submit-button">Submit!</button>
			</div>
	
	
			<div id="scope_status" class="submit-status">
				Waiting for input...
			</div>
		</div>	
		
		<div id="enter_scope_bootconfig" class="submit-form" >
			<p>Could not find where in BOOT_CONFIG.TXT to place this.</p>
			<p>Please select what line it should go under,</p>

			<div class="submit-inputs">

			  <select id="scope_options_bootconfig" class="submit-dropdown" value="None">
					<option value="START">Start of File</option>
					<?php
						$config_vita_lines = explode("\n",file_get_contents("vita_config.txt"));	
						for($i = 0; $i < sizeof($config_vita_lines); $i++)
						{
							echo('<option value="'.$config_vita_lines[$i].'">'.$config_vita_lines[$i]."</option>\n");
						}
					?>
			  </select><br><br>

			  <button onclick="submit_scope_bootconfig()" id="release_select" class="submit-button">Submit!</button>
			</div>
	
	
			<div id="scope__bootconfig_status" class="submit-status">
				Waiting for input...
			</div>
		</div>	
		
		<div id="enter_readme" class="submit-form" >
			<p>Could not find the readme file automatically</p>
			<p>Please enter the DIRECT URL to the readme file below:</p>

			<div class="submit-inputs">
		
			  <textarea cols="90" rows="1" id="readme_url_entry" class="submit-textarea"></textarea>
			  <button onclick="submit_readme()" id="readme_url_submit" class="submit-button">Set URL</button><br>

			</div>
	
			<div class="submit-skip">
					<font size="2%"><a onclick="no_readme_exists()" class="white">No readme?</a></font>
			</div>
	
			<div id="readme_status" class="submit-status">
				Waiting for input...
			</div>
		</div>	
		
		<div id="process_release" class="submit-form">
			<p>Downloading release files</p>
			<p>This can take awhile depending on your internet speed, And the release files size.</p>
			
			<div class="submit-progress-back" id="release_progress_back">
			  <div class="submit-progress-front" id="release_progress_front"></div>
			</div>
						
			<div id="process_status" class="submit-status">
				Waiting for input...
			</div>
			
		</div>
		
		
		<div id="enter_type" class="submit-form">

			<div class="submit-option" onclick="set_type('VPK');">
				<div class="submit-action-icon">
					<img src="/img/app.png" width="128" height="128">
				</div>
				<p>Application</p>
				<font size="2%">
					<p>This software creates a bubble on the PSVita LiveArea Screen</p>
					<p>Or is a launchable app that is independant of anything else.</p>
				</font>
			</div>
		
			<div class="submit-option" onclick="set_type('PLUGIN');">
				<div class="submit-action-icon">
					<img src="/img/tai.png" width="128" height="128">
				</div>
				<p>Plugin</p>
				<font size="2%">
					<p>This is a module that is injected into another application</p>
					<p>Or it loads into the PSVita's Kernel.</p>
				</font>
			</div>
		</div>
		
		<div id="enter_config_type" class="submit-form">
			<div class="submit-option" onclick="set_config_type('TAI');">
				<div class="submit-action-icon">
					<img src="/img/tai.png" width="128" height="128">
				</div>
				<p><b>Tai Plugin</b></p>
				<font size="2%">
					<p>Goes under *KERNEL in u(x/r)0:/tai/config.txt</p>
					<p>Runs right when taihen.skprx loads, can be skipped by holding L at boot.</p>
					<p><b>This is the most common, if you dont know which to choose, this is probably the right one.</b></p>
				</font>
			</div>
		
			<div class="submit-option" onclick="set_config_type('BOOT');">
				<div class="submit-action-icon">
					<img src="/img/boot.png" width="128" height="128">
				</div>
				<p><b>Boot Plugin</b></p>
				<font size="2%">
					<p>Plugin should be placed into ur0:/tai/boot_config.txt.</p>
					<p>For when the plugin loads early in the boot process. before taihen or henkaku loads.</p>
				</font>
			</div>
		</div>
		
		<div id="enter_credits" class="submit-form">
			<p>Its important to give proper crediting to developers</p>
			<p>So enter the name of the developer who wrote this application</p>
			<p>If there are multiple devs involved, write all there names seperated by '&'</p>
			<p>Alternatively you can use a team name.</p>
			<font size="2%"><p>ex: Team OneLUA & TheHeroGac</p></font>

			<div class="submit-inputs">
		
			  <textarea cols="90" rows="1" id="credits_entry" class="submit-textarea"></textarea>
			  <button onclick="submit_credits()" id="credits_submit" class="submit-button">Next</button><br>

			</div>
	
			<div id="credits_status" class="submit-status">
				Waiting for input...
			</div>
		</div>	



		
		<div id="please_verify" class="submit-form" >
			<div class="submit-inputs">
				<!-- Hidden Form -->
				<p>One last thing.. you arent a robot now.. are you?</p>
				<form action="/git/add_entry.php" method="post" id="mainform" target="_blank">
					<input type="text" id="download_url" name="download_url" value="None"hidden="true">
					<input type="text" id="id" name="id" value="None"hidden="true">
					<input type="text" id="title" name="title" value="None" hidden="true">
					<input type="text" id="credits" name="credits" value="None" hidden="true">
					<input type="text" id="type" name="type" value="None" hidden="true">
					<input type="text" id="scope" name="scope" value="None" hidden="true">
					<input type="text" id="config_type" name="config_type" value="None" hidden="true">
					<input type="text" id="download_src" name="download_src" value="None" hidden="true">
					<input type="text" id="download_icon0" name="download_icon0" value="None" hidden="true">
					<input type="text" id="download_readme" name="download_readme" value="None" hidden="true">
					<input type="submit" value="Submit" hidden="true">
					<div id="captcha" class="submit-captcha">
						<div class="g-recaptcha" data-sitekey="6LdT0fIUAAAAAMI8sV9fMMZdrNeeRGGEQ5s6CXf8"></div>
					</div>
				</form> 
				<button onclick="confirm_submit()" id="verify_submit" class="submit-button">Submit to CbpsDB!</button>
				
				<div id="verify_status" class="submit-status">
					Waiting for input...
				</div>
			</div>
			
		</div>	
	
		

	
	</div>	
</div>


</body>
</html>