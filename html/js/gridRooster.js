//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
Script met functions om het volledige rooster te bouwen
  - setGridTimetable()
    * voorbereidingen voor volledige rooster (config laden, data parsen, etc)
  - setGridTimetableBody()
    * voorbereidingen voor body (request naar server voor data). zit in aparte function om te veel nested statements te voorkomen (leesbaarheid).
  - buildGridTimetableBody()
    * functie om de head van de table te bouwen, alle dagdelen + tijden.
  - buildGridTimetableBody()
    * functie om door klassen te loopen en body te "bouwen".
      + Als afspraak bestaat op uur voor klas, dan call naar buildGridTimetableAfspraak()
      + anders buildGridTimetableInput()
  - buildGridTimetableAfspraak()
    * als een afspraak bestaat op het gekozen dagdeel, build dan een entry waar al deze informatie in staat
  - buildGridTimetableInput()
    * als er geen afspraak bestaat, build dan een entry met inputs voor afspraak
  - sortTable
    * functie om een table alphabetische te sorteren op de eerste kolom
*/
function setGridTimetable(data, modeJaarlaag = false, dataJaarlaag = null) {
  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        let conf = JSON.parse(this.responseText);
        buildGridTimetableHead(conf, data);
        setGridTimetableBody(conf, data, modeJaarlaag, dataJaarlaag);
      }
      catch (e) {
        //stop loading animatie
        load(false);
        errorMessage(e);
      }
    }
  };
  xhttp.open("GET", "/api.php?loadConfig=true", true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}

function setGridTimetableBody(conf, data, modeJaarlaag, dataJaarlaag) {
  //laad alle beschikbare docenten, klassen en lokalen
  let xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        let listAvailable = JSON.parse(this.responseText);
        let table = document.getElementsByTagName('table')[0];
        buildGridTimetableBody(conf, data, table, listAvailable, modeJaarlaag, dataJaarlaag);
      }
      catch (e) {
        //stop loading animatie
        load(false);
        errorMessage(e);
      }
    }
  };
  xhttp.open("GET", "/api.php?listAvailable=true", true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}

function buildGridTimetableHead(conf, data) {
  let main = document.getElementsByTagName('main')[0];
  let html = '<table>\n<tr>\n<th>Klas</th>\n';
  for (var i = 0; i < conf.dagen.length; i++) {
    for (var j = 0; j < conf.uren; j++) {
      //offset van 1 omdat de dagdelen vanaf 0 worden geteld maar vanaf 1 weergegeven
      html += '<th colspan="8">\n<span class="dag">' + conf.dagen[i]+(j+1) + '</span>\n<br>\n<span class="tijd">' + conf.lestijden[j] + '</span>\n</th>\n';
    }
  }
  html += '</tr>';
  html += '</table>\n';
  main.innerHTML = html;
}

function buildGridTimetableBody(conf, data, table, listAvailable, modeJaarlaag, dataJaarlaag) {
  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        let listAll = JSON.parse(this.responseText);
        let html = '';
        for (var i = 0; i < listAll.klas.length; i++) {
          let klas = listAll.klas[i]
          html += '<tr>\n<td>' + klas.jaar + klas.niveau + klas.nummer + '</td>\n';
          html += buildGridTimetableKlas(conf, data, listAvailable, klas);
          html += '</tr>\n';
        }
        table.innerHTML += html;
        sortTable();
      }
      catch (e) {
        //stop loading animatie
        load(false);
        errorMessage(e);
      }
    }
  };
  if (modeJaarlaag) {
    xhttp.open("GET", "/api.php?listJaarlaagKlassen=true&jaar="+encodeURIComponent(dataJaarlaag.jaar)+"&niveau="+encodeURIComponent(dataJaarlaag.niveau), true);
}
  else {
    xhttp.open("GET", "/api.php?listAll=true", true);
  }
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}

function buildGridTimetableKlas(conf, data, listAvailable, klas) {
  let html = '';
  for (var i = 0; i < conf.dagen.length; i++) {
    for (var j = 0; j < conf.uren; j++) {
      let heeftAfspraak = false;
      let dagdeel = conf.dagen[i]+j;
      //check of er afspraken zijn op het dagdeel
      if (typeof data[dagdeel] !== 'undefined' && data[dagdeel] !== null) {
        for (var x = 0; x < data[dagdeel].length; x++) {
          let afspraak = data[dagdeel][x];
          if (afspraak.klas[0].jaar == klas.jaar && afspraak.klas[0].niveau == klas.niveau && afspraak.klas[0].nummer == klas.nummer) {
            heeftAfspraak = true;
            html += buildGridTimetableAfspraak(afspraak);
            // break;
          }
        }
      }
      //als er geen afspraak is build dan de input
      if (!heeftAfspraak) {
        html += buildGridTimetableInput(dagdeel, klas, listAvailable);
      }
    }
  }
  return html;
}

function buildGridTimetableAfspraak(data) {
  let html = '';
  //voeg content toe
  html += '<td>'+data.docent[0]+'</td>\n';
  html += '<td>'+data.docent[1]+'</td>\n';
  html += '<td>'+data.lokaal[0]+'</td>\n';
  html += '<td>'+data.lokaal[1]+'</td>\n';
  html += '<td>'+data.laptop+'</td>\n';
  html += '<td>'+data.projectCode+'</td>\n';
  //om te voorkomen dat lange notities de table verpesten truncaten we de note als deze meer dan 7 characters is
  let note = data.note;
  if (note.length > 7) {
    note = note.substr(0, 6) + "\u2026";
  }
  html += '<td>'+note+'</td>\n';
  html += '<td data-hour=\'' + JSON.stringify(data) + '\'>';
  html += '<img src="/img/enlarge.svg" onclick="enlargeHour(this.parentElement.dataset.hour)" alt="Enlarge">\n';
  html += '<button type="button" class="SVGbutton" onclick="deleteHour(this.parentElement.dataset.hour, 1)"><img src="/img/closeBlack.svg" alt="Close"></button>';
  html += '</td>';

  return html;
}

function buildGridTimetableInput(dagdeel, klas, listAvailable) {
  let klasTitle = klas.jaar + klas.niveau + klas.nummer;
  let html = '';
  html += '<td><select name="'+dagdeel+klasTitle+'docent1">'+makeList(dagdeel, 'docent', 'Docent1', listAvailable)+'</select></td>\n';
  html += '<td><select name="'+dagdeel+klasTitle+'docent2">'+makeList(dagdeel, 'docent', 'Docent2', listAvailable)+'</select></td>\n';
  html += '<td><select name="'+dagdeel+klasTitle+'lokaal1">'+makeList(dagdeel, 'lokaal', 'Lokaal1', listAvailable)+'</select></td>\n';
  html += '<td><select name="'+dagdeel+klasTitle+'lokaal2">'+makeList(dagdeel, 'lokaal', 'Lokaal2', listAvailable)+'</select></td>\n';
  html += '<td><input type="number" name="'+dagdeel+klasTitle+'laptops" placeholder="Laptops"></td>';
  html += '<td><input type="text" name="'+dagdeel+klasTitle+'projectCode" placeholder="ProjectCode"></td>';
  html += '<td><input type="text" name="'+dagdeel+klasTitle+'note" placeholder="Note"></td>';
  //voeg een hidden input toe aan de laatste cell omdat de function anders in de war raakt
  html += '<td>';
  html += '<input type="hidden" name="'+dagdeel+klasTitle+'klas1" value="klas0" data-klas=\'{"data":['+JSON.stringify(klas)+']}\'>';
  html += '<button type="button" class="SVGbutton" onclick="sendHour(\'' + dagdeel+klasTitle + '\', \'' + dagdeel + '\', 1)"><img src="/img/save.svg" alt="Save"></button>';
  html += '</td>';

  return html;
}

//function om de table te sorten
function sortTable() {
  let table, rows, switching, i, x, y, shouldSwitch;
  table = document.getElementsByTagName("table")[0];
  switching = true;
  /* Make a loop that will continue until
  no switching has been done: */
  while (switching) {
    // Start by saying: no switching is done:
    switching = false;
    rows = table.rows;
    /* Loop through all table rows (except the
    first, which contains table headers): */
    for (i = 1; i < (rows.length - 1); i++) {
      // Start by saying there should be no switching:
      shouldSwitch = false;
      /* Get the two elements you want to compare,
      one from current row and one from the next: */
      x = rows[i].getElementsByTagName("TD")[0];
      y = rows[i + 1].getElementsByTagName("TD")[0];
      // Check if the two rows should switch place:
      // als de row met INFO begint switch dan niet
      if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase() && x.innerHTML != 'Info') {
        // If so, mark as a switch and break the loop:
        shouldSwitch = true;
        break;
      }
    }
    if (shouldSwitch) {
      /* If a switch has been marked, make the switch
      and mark that a switch has been done: */
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
    }
  }
}
