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
  select.innerHTML = '<select name="displayMode" onchange="buildSelect(this.value, list);">\n<option value="klas" selected>Klassen</option>\n<option value="docent">docenten</option>\n</select>\n<select name="displayModeFinal" onchange="setWeekTimetable(this.value);">\n<option>Klas</option>\n</select>';

  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        list = JSON.parse(this.responseText);
        //nu we de docent/klassen data hebben kunnen we de lijst maken
        buildSelect('klas', list);
        //stop loading animatie
        load(false);
      }
      catch (e) {
        //stop loading animatie
        load(false);
        errorMessage(e);
        let list = {docent: [], klas: []};
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
        let html = '<select name="selectJaarlaag" onchange="buildJaarlaag(this.value, this.dataset.jaarlagen)" data-jaarlagen="'+this.responseText.replace(/\n/, '')+'">\n';
        html += '\t<option selected disabled>Jaarlaag</option>\n';
        for (var i = 0; i < jaarlagen.klas.length; i++) {
          html += '<option value=' + JSON.stringify(jaarlagen.klas[i]).replace(/'/g, '\'') + '>'+jaarlagen.klas[i].jaar+jaarlagen.klas[i].niveau+'</option>\n';
        }
        //plaats html
        html += '</select>';
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
  xhttp.open("GET", "/api.php?readJaarlaag=true&jaar="+encodeURIComponent(jaarlaag.jaar)+"&niveau="+encodeURIComponent(jaarlaag.niveau), true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}
