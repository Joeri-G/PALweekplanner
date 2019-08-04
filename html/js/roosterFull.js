//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
function modeFull() {
  //voeg full class toe
  //start laad animatie
  load(true);
  //voeg de css class toe aan main
  main.className = 'full';
  let allData;
  //xhhtp2 omdat we niet het oude object willen overwriten
  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        let allData = JSON.parse(this.responseText);
        x(allData);
      }
      catch (e) {
        //stop loading animatie
        setTimeout(function() {load(false);}, 500);
        errorMessage(e);
        let allData = {};
      }
    }
  };
  xhttp.open("GET", "/api.php?readAll=true", true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();

//temp fix
function x(allData) {
  //laad alle klassen en docenten
  let list = {docent: [], klas: []};
  let xhttp2 = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp2.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        list = JSON.parse(this.responseText);
        //nu we de docent/klassen data hebben kunnen we de lijst maken
        buildFull(allData, list);
        load(false);
        //stop loading animatie
      }
      catch (e) {
        //stop loading animatie
        errorMessage(e);
      }
    }
  };
  xhttp2.open("GET", "/api.php?listAll=true", true);
  xhttp2.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp2.send();
}
}
