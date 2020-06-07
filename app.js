var color = window.getComputedStyle(document.body, null).backgroundColor;
    var cbpsapi = "https://db.olebeck.com"
    var page, element;
    var pclist,pllist,hblist = []

    var val = "";
    function openPage(pageName, elmnt) {
      page = pageName;
      element = elmnt;
      var i, tablinks;
      tablinks = document.getElementsByClassName("tablink");
      for (i = 0; i < tablinks.length; i++) {
        tablinks[i].style.backgroundColor = "";
      }
      elmnt.style.backgroundColor = "rgb(116, 116, 116)";

      tabcontent = document.getElementsByClassName("tabcontent")
      for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
      }
        if(elmnt.id == "buttonH") {
            document.getElementById("Homebrews").style.display = "block"
        }
        if(elmnt.id == "buttonP") {
            document.getElementById("Plugins").style.display = "block"
        }
        if(elmnt.id == "buttonT") {
            document.getElementById("PCTools").style.display = "block"
        }
    }
    
    function listChange(order, elmnt) {
      var i, tablinks2;
      tablinks2 = document.getElementsByClassName("tablink2");
      for (i = 0; i < tablinks2.length; i++) {
        tablinks2[i].style.backgroundColor = "";
      } 
      choosemode = document.getElementsByClassName("chooselistmode");
      for (i = 0; i < choosemode.length; i++) choosemode[i].classList.toggle("clicked",false)
      if(order == "updatedate") {
        for(data in dataset) dataset[data] = dataset[data].sort(dynamicSort("-date"));
      } else if(order == "alphabetic") {
        for(data in dataset) dataset[data] = dataset[data].sort(dynamicSort("name"));
      }
      recreateLists(dataset.lb1json.filter(check),dataset.lb2json.filter(check),dataset.lb3json.filter(check))
      elmnt.classList.toggle("clicked",true);
    }
    
    function search(event) {
        val = event.target.value
        recreateLists(dataset.lb1json.filter(check),dataset.lb2json.filter(check),dataset.lb3json.filter(check))
    }
        
    function check(id) {
        titleid = id.name
        return titleid.includes(val)
    }
    
    async function getData() {
      var address = cbpsapi + "/api/vita/getall/json";
        let response = await 
        fetch(address)
        data = await response.json()
        return data;
    }
    
    let dataset;
    var searcharray = [];
    
    async function main() {
        dataset = await getData();
      document.getElementById("buttonH").click();
      document.getElementById("buttonR").click();
    }
    
    function dynamicSort(property) {
        var sortOrder = 1;
    
        if(property[0] === "-") {
            sortOrder = -1;
            property = property.substr(1);
        }
    
        return function (a,b) {
            if(sortOrder == -1){
                return b[property].localeCompare(a[property]);
            } else{
                return a[property].localeCompare(b[property]);
            }        
        }
    }



    async function recreateLists(hbdata,pldata,pcdata) {
      
        document.getElementById("Homebrews").innerHTML = "";
        document.getElementById("Plugins").innerHTML = "";
        document.getElementById("PCTools").innerHTML = "";
        for(var i = 0; i < hbdata.length; i++) {
            hblistlink = document.createElement('a');
            hblistlink.title = hbdata[i].name;
            hblistlink.innerHTML = hbdata[i].name
            hblistlink.href = hbdata[i].url;
            hblistlinkdiv = document.createElement("div");
            hblistlinkdiv.appendChild(hblistlink);
            hblistlinkdiv.classList.add("hblinklist")
            hblistlink.classList.add('tablink2');
            hbimg = document.createElement('img');
            hbimg.classList.add('icon');
            hbimg.loading = "lazy"
            hbimg.src = cbpsapi + "/api/vita/icons/" + hbdata[i].icon
            hblist[i] = document.createElement('div');
            hblist[i].classList.add('tablink2div');
            hblist[i].id = hbdata[i].titleid
            hblist[i].appendChild(hbimg);
            hblist[i].appendChild(hblistlinkdiv);
            document.getElementById("Homebrews").appendChild(hblist[i]);
        }
        for(var i = 0; i < pldata.length; i++) {
            hblistlink = document.createElement('a');
            hblistlink.title = pldata[i].name;
            hblistlink.innerHTML = pldata[i].name
            hblistlink.href = hbdata[i].url;
            hblistlinkdiv = document.createElement("div");
            hblistlinkdiv.appendChild(hblistlink);
            hblistlinkdiv.classList.add("hblinklist")
            hblistlink.classList.add('tablink2');
            hblist[i] = document.createElement('div');
            hblist[i].classList.add('tablink2div');
            hblist[i].id = pldata[i].titleid
            hblist[i].appendChild(hblistlinkdiv);
            document.getElementById("Plugins").appendChild(hblist[i]);
        }
        for(var i = 0; i < pcdata.length; i++) {
            hblistlink = document.createElement('a');
            hblistlink.title = pcdata[i].name;
            hblistlink.innerHTML = pcdata[i].name
            hblistlink.href = pcdata[i].url;
            hblistlinkdiv = document.createElement("div");
            hblistlinkdiv.appendChild(hblistlink);
            hblistlinkdiv.classList.add("hblinklist")
            hblistlink.classList.add('tablink2');
            //hbimg = document.createElement('img');
            //hbimg.classList.add('icon');
            //hbimg.loading = "lazy"
            //hbimg.src = cbpsapi + "/icons/" + hbdata[i].icon
            hblist[i] = document.createElement('div');
            hblist[i].classList.add('tablink2div');
            hblist[i].id = hbdata[i].titleid
            //hblist[i].appendChild(hbimg);
            hblist[i].appendChild(hblistlinkdiv);
            document.getElementById("PCTools").appendChild(hblist[i]);
        }
    }
    
    main();