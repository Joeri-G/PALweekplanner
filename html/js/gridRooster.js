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
      } catch (e) {
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
      } catch (e) {
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
  let html = '<table id="gridRooster">\n<tr>\n';
  for (var i = 0; i < conf.dagen.length; i++) {
    for (var j = 0; j < conf.uren; j++) {
      //offset van 1 omdat de dagdelen vanaf 0 worden geteld maar vanaf 1 weergegeven
      html += '<th colspan="9">\n<span class="dag">' + conf.dagen[i] + (j + 1) + '</span>\n<br>\n<span class="tijd">' + conf.lestijden[j] + '</span>\n</th>\n';
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
        for (var i = 0; i < listAll.k.length; i++) {
          let klas = listAll.k[i];
          html += buildGridTimetableKlas(conf, data, listAvailable, klas, modeJaarlaag);
          html += '</tr>\n';
        }
        table.innerHTML += html;
        sortTable(document.getElementById('gridRooster'));
      } catch (e) {
        //stop loading animatie
        load(false);
        errorMessage(e);
      }
    }
  };
  if (modeJaarlaag)
    xhttp.open("GET", "/api.php?listJaarlaagKlassen=true&jaar=" + encodeURIComponent(dataJaarlaag.j) + "&niveau=" + encodeURIComponent(dataJaarlaag.ni), true);
  else
    xhttp.open("GET", "/api.php?listAll=true", true);

  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}

function buildGridTimetableKlas(conf, data, listAvailable, klas, modeJaarlaag) {
  let html = '';
  for (var i = 0; i < conf.dagen.length; i++) {
    for (var j = 0; j < conf.uren; j++) {
      html += '\n<td class="klas">' + klas.j + klas.ni + klas.nu + '</td>\n';
      let heeftAfspraak = false;
      let dagdeel = conf.dagen[i] + j;
      //check of er afspraken zijn op het dagdeel
      if (typeof data[dagdeel] !== 'undefined' && data[dagdeel] !== null) {
        for (var x = 0; x < data[dagdeel].length; x++) {
          let afspraak = data[dagdeel][x];
          if (afspraak.k[0].j == klas.j && afspraak.k[0].ni == klas.ni && afspraak.k[0].nu == klas.nu) {
            heeftAfspraak = true;
            html += buildGridTimetableAfspraak(afspraak, modeJaarlaag);
            // break;
          }
        }
      }
      //als er geen afspraak is build dan de input
      if (!heeftAfspraak)
        html += buildGridTimetableInput(dagdeel, klas, listAvailable, modeJaarlaag);

    }
    //voeg nog een keer de klas toe
  }
  return html;
}

function buildGridTimetableAfspraak(data, modeJaarlaag = false) {
  let html = '';
  //voeg content toe
  html += '<td>' + data.d[0].replace(/\'/g, "&#39;") + '</td>\n';
  html += '<td>' + data.d[1].replace(/\'/g, "&#39;") + '</td>\n';
  html += '<td>' + data.l[0].replace(/\'/g, "&#39;") + '</td>\n';
  html += '<td>' + data.l[1].replace(/\'/g, "&#39;") + '</td>\n';
  html += '<td>' + data.la/*.replace(/\'/g, "&#39;")*/ + '</td>\n';
  html += '<td>' + data.p.replace(/\'/g, "&#39;") + '</td>\n';
  //om te voorkomen dat lange notities de table verpesten truncaten we de note als deze meer dan 7 characters is
  let note = data.no;
  if (note.length > 7)
    note = note.substr(0, 6) + "\u2026";

  html += '<td>' + note + '</td>\n';
  html += '<td data-hour=\'' + JSON.stringify(data).replace(/\'/g, "&#39;") + '\'>';
  html += '<img src="/img/enlarge.svg" onclick="enlargeHour(this.parentElement.dataset.hour)" alt="Enlarge" class="svgButton">\n';
  if (modeJaarlaag)
    html += '<img src="/img/closeBlack.svg" alt="Close" class="SVGbutton" onclick="deleteHour(this.parentElement.dataset.hour, 2)">';
  else
    html += '<img src="/img/closeBlack.svg" alt="Close" class="SVGbutton" onclick="deleteHour(this.parentElement.dataset.hour, 1)">';

  html += '</td>';

  return html;
}

function buildGridTimetableInput(dagdeel, klas, listAvailable, modeJaarlaag) {
  let klasTitle = klas.j + klas.ni + klas.nu;
  let html = '';
  html += '<td>' + makeList(dagdeel, 'd', 'Docent1', listAvailable, dagdeel + klasTitle + 'docent1') + '</td>\
  <td>' + makeList(dagdeel, 'd', 'Docent2', listAvailable, dagdeel + klasTitle + 'docent2') + '</td>\
  <td>' + makeList(dagdeel, 'l', 'Lokaal1', listAvailable, dagdeel + klasTitle + 'lokaal1') + '</td>\
  <td>' + makeList(dagdeel, 'l', 'Lokaal2', listAvailable, dagdeel + klasTitle + 'lokaal2') + '</td>';
  html += '<td><input type="number" min="0" max="80" name="' + dagdeel + klasTitle + 'laptops" placeholder="Laptops"></td>';
  html += '<td>' + makeList(dagdeel, 'p', 'Project', listAvailable, dagdeel + klasTitle + 'projectCode') + '</td>'
  html += '<td><input type="text" name="' + dagdeel + klasTitle + 'note" placeholder="Note"></td>';
  html += '<td>';
  //voeg een hidden input toe aan de laatste cell omdat de function anders in de war raakt
  html += '<input type="hidden" name="' + dagdeel + klasTitle + 'klas1" value="klas0" data-k=\'{"data":[' + JSON.stringify(klas).replace(/\'/g, "&#39;") + ']}\'>';
  if (modeJaarlaag)
    html += '<img src="/img/save.svg" alt="Save" onclick="sendHour(\'' + dagdeel + klasTitle + '\', \'' + dagdeel + '\', 2)" class="svgButton">';
  else
    html += '<img src="/img/save.svg" alt="Save" onclick="sendHour(\'' + dagdeel + klasTitle + '\', \'' + dagdeel + '\', 1)" class="svgButton">';

  html += '</td>';

  return html;
}
