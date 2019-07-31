//function om error modals weer te geven
function errorMessage(error) {
  console.log(error);
}

function buildSelect(value) {
  mode = value;
  if (value != 'klas' && value != 'docent') {
    errorMessage('INVALID MODE');
    return false;
  }
  let html = buildList(list[mode]);
  //selecteer de dropdown en vervang de HTML
  let drop = document.getElementsByName('displayModeFinal')[0];
  drop.innerHTML = html;
}

//functie om alle opties voor de lijst te maken
function buildList(input) {
  let html = '\t<option selected disabled>'+mode+'</option>\n';
  if (mode == 'docent') {
    for (var i = 0; i < input.length; i++) {
      html += '\t<option value="'+i+'">'+input[i].username+'</option>\n';
    }
  }
  else if (mode == 'klas') {
    for (var i = 0; i < input.length; i++) {
      html += '\t<option value="'+i+'">'+input[i].jaar+input[i].niveau+input[i].nummer+'</option>\n';
    }
  }
  return html;
}

function setTimetable(selected) {
  //de values van de selectie lijst wijzen naar array entries dus de selectie dus hiermee halen we die waarde op uit de array
  selector = list[mode][selected];
  let xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        let data = JSON.parse(this.responseText);
        buildTimetable(data);
      }
      catch (e) {
        errorMessage(e);
      }
    }
  };
  //als de mode docent is haal dan de data van de klassen pagina
  if (mode == 'klas') {
    xhttp.open("GET", "/api.php?readKlas=true&jaar="+encodeURIComponent(selector.jaar)+"&niveau="+encodeURIComponent(selector.niveau)+"&nummer="+selector.nummer);
  }
  //anders haal de data van de docenten pagina
  else {
    xhttp.open("GET", "/api.php?readDocent=true&docent="+encodeURIComponent(selector.username), true);
  }
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}

function buildTimetable(data) {
  let html = '';
  for (var i = 0; i < dagen.length; i++) {
    //functie om door dagen te loopen
    html += buildDay(data, dagen[i]);
  }
  //plaats de HTML het document
  main.innerHTML = html;
}

function buildDay(data, dag) {
  let html = '<section>\n';
  for (var i = 0; i < uren; i++) {
    dagdata = data[dag+i];
    //fucntie om uur te maken
    html += buildHour(data, dag, i);
  }
  html += '</section>\n';
  return html;
}

function buildHour(data, dag, uur) {
  let html = '<div class="hour">';
  //selection om te bepalen of er een afspraak weergegeven moet worden of een input voor een nieuwe
  if (typeof data[dag+uur] !== 'undefined' && data[dag+uur] !== null) {
    let inside = {};
    nu = data[dag+uur];
    for (var i = 0; i < nu.klas.length; i++) {
      if (nu.klas[i].niveau == 'None') {
        nu.klas[i].jaar = '';
        nu.klas[i].nummer = '';
      }
    }
    inside.docent1 = '<tr>\n<td>Docent1</td>\n<td>'+nu.docent[0]+'</td>\n</tr>\n';
    inside.docent2 = '<tr>\n<td>Docent2</td>\n<td>'+nu.docent[1]+'</td>\n</tr>\n';

    inside.klas1 = '<tr>\n<td>Klas1</td>\n<td>'+nu.klas[0].jaar+nu.klas[0].niveau+nu.klas[0].nummer+'</td>\n</tr>\n';
    inside.klas2 = '<tr>\n<td>Klas2</td>\n<td>'+nu.klas[1].jaar+nu.klas[1].niveau+nu.klas[1].nummer+'</td>\n</tr>\n';

    inside.lokaal1 = '<tr>\n<td>Lokaal1</td>\n<td>'+nu.lokaal[0]+'</td>\n</tr>\n';
    inside.lokaal2 = '<tr>\n<td>Lokaal2</td>\n<td>'+nu.lokaal[1]+'</td>\n</tr>\n';

    // inside.projectCode = '<tr>\n<td>Project Code</td>\n<td>'+nu.projectCode+'</td>\n</tr>';
    inside.note = '<tr>\n<td>Note</td>\n<td>'+nu.note+'</td>\n</tr>\n';

    html += '<button type="button" class="close" onclick="deleteHour(this.parentElement.dataset.hour)">&#10799;</button>\n<table>\n'+
            inside.docent1+
            inside.docent2+
            inside.klas1+
            inside.klas2+
            inside.lokaal1+
            inside.lokaal2+
            // inside.projectCode+
            inside.note+
            '</table>\n';
  }
  else {
    html += '<table>';
    html += makeList();
    html += '</table>';
  }
  html += '</div>\n';
  if (uur < uren - 1) {
    html += '<div class="pause"></div>';
  }
  return html;
}

function makeList() {
  let html = '';
  for (var i = 0; i < 4; i++) {
    html += '<td>\n<tr>\n'+makeSelect(i)+makeSelect(i+4)+'\n</td>\n</tr>';
  }
  return html;
}

function makeSelect(nmbr) {
  console.log(nmbr);
  return '<td>Menu</td>';
}




function deleteHour(data) {
  confirm('Delete?\n(work in progress...)');
}
