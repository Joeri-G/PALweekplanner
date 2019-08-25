//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
function modeDefault() {
  let main = document.getElementsByTagName('main')[0];
  let select = document.getElementsByClassName('select')[0];
  //start loading animatie
  load(true);
  //zorg dat er geen css rules meer in main zitten
  main.className = '';
  //zorg dat de css styling weer de default wordt
  main.style.display = 'block';
  //zorg dat de oude html weg is
  main.innerHTML = '<p class="mainMessage">Selecteer een docent of klas met de dropdown</p>';
  // //zet de footer weer beneden
  // document.getElementsByTagName('footer')[0].style.position = 'absolute';
  //zorg dat de selction weergegeven wordt
  select.style.display = 'block';
  //bouw selection dropdown
  // select.innerHTML = '<select name="displayMode" onchange="buildSelect(this.value, list);">\
  // <option value="k" selected>Klassen</option>\
  // <option value="d">docenten</option>\
  // </select>\
  // <select name="displayModeFinal" onchange="setWeekTimetable(this.value);">\
  // <option>Klas</option>\
  // </select>';

  select.innerHTML = '<div class="dropSelect">\
    <input type="button" value="Klas" onclick="toggleDrop(this)" data-title="">\
      <div class="drop">\
      <input type="hidden" name="displayMode" value="k">\
      <input type="hidden">\
      <a href="javascript:void(0)" onclick="setValue(this);buildSelect(\'k\', list)" data-value="k">Klas</a>\
      <a href="javascript:void(0)" onclick="setValue(this);buildSelect(\'d\', list)" data-value="d">Docent</a>\
      <span></span>\
    </div>\
  </div>\
  <div class="dropSelect">\
    <input type="button" value="Klas" onclick="toggleDrop(this)" data-title="klas">\
    <div class="drop" id="selectKlasDocent">\
      <input type="hidden" name="displayModeFinal" onChange="setWeekTimetable(this.value)">\
      <input type="search" placeholder="Filter..." onkeyup="filterDropdown(this)">\
      <span>Geen resultaten...</span>\
    </div>\
  </div>';


  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        list = JSON.parse(this.responseText);
        //nu we de docent/klassen data hebben kunnen we de lijst maken
        buildSelect('k', list);
        //stop loading animatie
        load(false);
      }
      catch (e) {
        //stop loading animatie
        load(false);
        errorMessage(e);
        let list = {d: [], k: []};
      }
    }
  };
  xhttp.open("GET", "/api.php?listAll=true", true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}
// Rooster Grid
function modeGrid() {
  let main = document.getElementsByTagName('main')[0];
  let select = document.getElementsByClassName('select')[0];
  //start laad animatie
  load(true);
  //voeg full class toe
  main.className = 'full';
  //haal klas/docent selection weg
  select.style.display = 'none';
  // //zet footer naar normale css
  // document.getElementsByTagName('footer')[0].style.position = 'relative';
  main.innerHTML = '';
  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        let afspraken = JSON.parse(this.responseText);
        //nu we de afspraken hebben kunnen we beginnen met de table maken
        setGridTimetable(afspraken);
        //stop loading animatie
        load(false);
      }
      catch (e) {
        //stop loading animatie
        load(false);
        errorMessage(e);
      }
    }
  };
  xhttp.open("GET", "/api.php?readAll=true", true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}


function modeJaarlaag() {
  let main = document.getElementsByTagName('main')[0];
  let select = document.getElementsByClassName('select')[0];
  load(true);
  //build jaarlaag input
  select.style.display = 'block';
  let xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        //bouw jaarlaag select
        let jaarlagen = JSON.parse(this.responseText);
        let html = '<div class="dropSelect">\
          <input type="button" value="Jaar" onclick="toggleDrop(this)" data-title="Jaar">\
          <div class="drop" id="selectKlasDocent">\
            <input type="hidden" name="selectJaarlaag" onChange="setWeekTimetable(this.value)">\
            <input type="search" placeholder="Filter..." onkeyup="filterDropdown(this)">';
        for (var i = 0; i < jaarlagen.k.length; i++) {
          html += '<a href="javascript:void(0)" onclick="setValue(this);buildJaarlaag(this.dataset.value)" data-value=' + JSON.stringify(jaarlagen.k[i]).replace(/\'/g, "&#39;") + '>'+jaarlagen.k[i].j+jaarlagen.k[i].ni+'</a>\n';
        }
        html += '<span>Geen resultaten...</span>\
          </div>\
        </div>';
        select.innerHTML = html;
        //verander main
        main.innerHTML = '<p class="mainMessage">Selecteer een jaarlaag met de dropdown</p>';
        //zorg dat er geen css rules meer in main zitten
        main.className = '';
        main.style.display = 'block';
        // //zet footer vast aan onderkant pagina
        // document.getElementsByTagName('footer')[0].style.position = 'absolute';
        load(false);
      }
      catch (e) {
        load(false);
        //stop loading animatie
        errorMessage(e);
      }
    }
  };
  xhttp.open("GET", "/api.php?listJaarlaag=true", true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}

function buildJaarlaag(input) {

  load(true);
  let main = document.getElementsByTagName('main')[0];
  main.className = 'full';
  let jaarlaag = JSON.parse(input);
  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        let afspraken = JSON.parse(this.responseText);
        //nu we de afspraken hebben kunnen we beginnen met de table maken
        setGridTimetable(afspraken, modeJaarlaag, jaarlaag);
        //stop loading animatie
        load(false);
      }
      catch (e) {
        //stop loading animatie
        load(false);
        errorMessage(e);
      }
    }
  };
  xhttp.open("GET", "/api.php?readJaarlaag=true&jaar="+encodeURIComponent(jaarlaag.j)+"&niveau="+encodeURIComponent(jaarlaag.ni), true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}
