window.hasErrored = []
function on_error(elem,id,plugins)
{
	if(window.hasErrored.indexOf("id") == -1)
		elem.src = "getimage.php?id="+id;
	else
		if(plugins == 0)
			elem.src = "/img/default.png";
		else
			elem.src="/img/plugin_default.png";
	
	window.hasErrored = window.hasErrored.concat(id);
}

function open_url(url)
{
	window.location = url;
}