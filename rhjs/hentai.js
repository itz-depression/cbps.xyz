var konachan = ""
var backgone = 0
if(localStorage.getItem("imghistory") != null) {
    imghistory = localStorage.getItem("imghistory").split(";")
    imghistory = imghistory.filter(function(value, index, arr){

        return value != "";
    
    });
} else {
    var imghistory = new Array
}
imghistory.push = function (){
    if (this.length >= 100) {
        this.shift();
    }
    return Array.prototype.push.apply(this,arguments);
}
var sw = false
loaded = false
lastms = Date.now()
inter = 0
let params = new URLSearchParams(this.window.location.search);
let autoplayinterval = parseInt(params.get("autoplay"));
if(autoplayinterval >= 1 ) {sw = true} else {autoplayinterval = 5000}
window.addEventListener("load",function() {
    
        var imgisloaded = true
        this.document.getElementById("btn").innerHTML = sw ? "Autoplay on" : "Autoplay off"
    this.document.body.appendChild(backdiv)
    this.document.body.appendChild(maindiv)

    
    this.console.log(autoplayinterval)
    loadnew()
})

document.onkeydown = function (e) {
    e = e || window.event;
    // use e.keyCode
	
	var key = e.keyCode;
	if(key == 37){
		back();
	}else if(key == 39 || key == 32){
		next();
	}else{
		console.log("key : " + key);
	}
	console.log("index : " + backgone);
};

async function loadnew() {
    backgone = 0
    loadurl()
}
async function loadurl() {
    konachan = await fetch("https://rdm.olebeck.com/link").then(resp => {
        imghistory.push(konachan)
        imghistory = imghistory.filter(function(value, index, arr){

            return value != "";
        
        });
        localStorage.setItem("imghistory",imghistory.join(";"))
        
        return resp.text()
    })
    setimages(konachan)
}
function setimages(imgurl = konachan) {
    loaded = false
    document.getElementById("mainimg").src = imgurl
    document.getElementById("backdiv").style.backgroundImage = "url(" + imgurl + ")" 
}
async function autoplayswitch(){
    if(sw == true) {
        sw = false
    } else { sw = true}
    console.log("changed autoplay to" , sw)
    this.document.getElementById("btn").innerHTML = sw ? "Autoplay on" : "Autoplay off"
    if(sw == false) {
        clearInterval(inter)
    }
}
function back() {
    backgone++
    setimages(imghistory[imghistory.length - backgone])
}
function next() {
    if(backgone > 0) {
    backgone = backgone - 1
    
    setimages(imghistory[imghistory.length - backgone])
    } else {loadurl()}
    
}
function imgisloadesd() {
    lastms = Date.now()
loaded = true
}
setInterval(function(){
    if(loaded == true) {
        
        //console.log("loaded")
        if(sw == true) {
            //console.log("sw = true")
            currms = Date.now()
            
                //console.log("diff:",currms - lastms, "needed: ",autoplayinterval)
                diff = currms - lastms
                if(diff >= autoplayinterval-1) {
                    lastms = Date.now()
                    this.document.getElementById("btn").innerHTML = sw ? "Autoplay on" : "Autoplay off"
                    loadnew()
                }
            }
        }
    },200);

    