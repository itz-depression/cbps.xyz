
var count = 0
var t = 0
var needed = getRandomInt(10,20)
if(localStorage.getItem("count") != null) {
    count = parseInt(localStorage.getItem("count"));
    needed = getRandomInt(10,20) + count;
}
function getRandomInt(min, max) {
    min = Math.ceil(min);
    max = Math.floor(max);
    return Math.floor(Math.random() * (max - min + 1)) + min;
}


function countup() {
    canunlock()
    count++
    updatecounter()
}

function updatecounter() {
    localStorage.setItem("count",count)
    document.getElementById("counter").innerText = "Lewds: " + count + "  of: " + needed
}
function makeid(length) {
    var result           = '';
    var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var charactersLength = characters.length;
    for ( var i = 0; i < length; i++ ) {
       result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
 }
function canunlock() {
    if(count + 2 > needed) {
        setrandomimg()
        needed = getRandomInt(10,20) + count
    }
}
function loadurl() {
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function() { 
        if (xmlHttp.readyState == 4 && xmlHttp.status == 200)
        document.getElementById("himg").src = xmlHttp.responseText;
    }
    xmlHttp.open("GET", "https://rdm.olebeck.com/link", false); // true for asynchronous 
    xmlHttp.send(null);
}
function setrandomimg() {
    clearTimeout(t)
    loadurl()
    
    t = setTimeout(() => document.getElementById("himg").classList.toggle("timeout",true),20000)
}
function reset() {
    count = 0;
     localStorage.setItem('count',0);
    needed = getRandomInt(10,20);
    updatecounter()
}
updatecounter()
document.getElementById("himg").addEventListener("load",function() {
    document.getElementById("himg").classList.toggle("timeout",false)
})