//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
Script met functions om het volledige rooster te bouwen
  - setFullTimetable()
    * voorbereidingen voor volledige rooster (config laden, data parsen, etc)
  - setFullTimetableBody()
    * voorbereidingen voor body (request naar server voor data). zit in aparte function om te veel nested statements te voorkomen (leesbaarheid).
  - buildFullTimetableBody()
    * functie om de head van de table te bouwen, alle dagdelen + tijden.
  - buildFullTimetableBody()
    * functie om door klassen te loopen en body te "bouwen".
      + Als afspraak bestaat op uur voor klas, dan call naar buildFullTimetableAfspraak()
      + anders buildFullTimetableInput()
  - buildFullTimetableAfspraak()
    * als een afspraak bestaat op het gekozen dagdeel, build dan een entry waar al deze informatie in staat
  - buildFullTimetableInput()
    * als er geen afspraak bestaat, build dan een entry met inputs voor afspraak
*/
function setFullTimetable(data) {
  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        let conf = JSON.parse(this.responseText);
        buildFullTimetableHead(conf, data);
        setFullTimetableBody(conf, data);
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

function setFullTimetableBody(conf, data) {
  //laad alle beschikbare docenten, klassen en lokalen
  let xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        let listAvailable = JSON.parse(this.responseText);
        let table = document.getElementsByTagName('table')[0];
        buildFullTimetableBody(conf, data, table, listAvailable);
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

function buildFullTimetableHead(conf, data) {
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

function buildFullTimetableBody(conf, data, table, listAvailable) {
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
          html += buildFullTimetableKlas(conf, data, listAvailable, klas);
          html += '</tr>\n';
        }
        table.innerHTML += html;
      }
      catch (e) {
        //stop loading animatie
        load(false);
        errorMessage(e);
      }
    }
  };
  xhttp.open("GET", "/api.php?listAll=true", true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}

function buildFullTimetableKlas(conf, data, listAvailable, klas) {
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
            html += buildFullTimetableAfspraak(afspraak);
            // break;
          }
        }
      }
      //als er geen afspraak is build dan de input
      if (!heeftAfspraak) {
        html += buildFullTimetableInput(dagdeel, klas, listAvailable);
      }
    }
  }
  return html;
}

function buildFullTimetableAfspraak(data) {
  let html = '';
  //voeg content toe
  html += '<td>'+data.docent[0]+'</td>\n';
  html += '<td>'+data.docent[1]+'</td>\n';
  html += '<td>'+data.lokaal[0]+'</td>\n';
  html += '<td>'+data.lokaal[1]+'</td>\n';
  html += '<td>'+data.laptop+'</td>\n';
  html += '<td>'+data.projectCode+'</td>\n';
  //om te voorkomen dat lange notities de table verpesten truncaten we de note als deze meer dan 7 characters is
  if (data.note.length > 7) {
    data.note = data.note.substr(0, 6) + "\u2026";
  }
  html += '<td>'+data.note+'</td>\n';
  html += '<td data-hour=\'' + JSON.stringify(data) + '\'>';
  html += '<img src="/img/enlarge.svg" onclick="enlargeHour(this.parentElement.dataset.hour)" alt="Enlarge">\n';
  html += '<button type="button" class="SVGbutton" onclick="deleteHour(this.parentElement.dataset.hour, 1)"><img src="/img/closeBlack.svg" alt="Close"></button>';
  html += '</td>';

  return html;
}

function buildFullTimetableInput(dagdeel, klas, listAvailable) {
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
