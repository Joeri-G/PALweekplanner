//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
//GLOBALS
let main = document.getElementsByTagName('main')[0];
let list = {docenten: [], klassen: []}
let mode, config, uren, dagen, listAvailiable;

//start loading animatie
load(true);

//bouw selection dropdown
document.getElementsByName('displayMode')[0].innerHTML = '<option value="klas" selected>Klassen</option><option value="docent">Docenten</option>';



//laad config file
let xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
  if (this.readyState == 4 && this.status == 200) {
   try {
    config = JSON.parse(this.responseText);
    uren = config.uren;
    dagen = config.dagen;
    lestijden = config.lestijden;
   }
   catch (e) {
     errorMessage(e);
     uren = 0;
     dagen = [];
   }
  }
};
xhttp.open("GET", "/api.php?loadconfig=true", true);
xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
xhttp.send();

//xhhtp2 omdat we niet het oude object willen overwriten
let xhttp2 = new XMLHttpRequest();
//laad list met alle docenten en klassen
xhttp2.onreadystatechange = function() {
  if (this.readyState == 4 && this.status == 200) {
   try {
     list = JSON.parse(this.responseText);
     //nu we de docent/klassen data hebben kunnen we de lijst maken
     buildSelect('klas');
     //stop loading animatie
     load(false);
   }
   catch (e) {
     //stop loading animatie
     load(false);
     errorMessage(e);
     list = {docenten: [], klassen: []};
   }
  }
};
xhttp2.open("GET", "/api.php?listAll=true", true);
xhttp2.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
xhttp2.send();
