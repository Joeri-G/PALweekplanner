//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
//GLOBALS
let mode, listAvailiable;


function modeDefault() {
  //start loading animatie
  load(true);

  //zorg dat er geen css rules meer in main zitten
  main.className = '';
  //zorg dat de oude html weg is
  main.innerHTML = '<p style="font-size:1.5em;text-align:center">Selecteer een docent of klas met de dropdown</p>';
  //bouw selection dropdown
  document.getElementsByName('displayMode')[0].innerHTML = '<option value="klas" selected>Klassen</option><option value="docent">Docenten</option>';

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
        list = {docent: [], klas: []};
      }
    }
  };
  xhttp2.open("GET", "/api.php?listAll=true", true);
  xhttp2.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp2.send();
}

modeDefault();
