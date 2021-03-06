//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
script met fumctions om het weekrooster te bouwen
  - buildSelect()
    * functie om klas / docent selectie te maken
  - setWeekTimetable()
    * functie om alle data voor de timetable te fetchen en het build process aan te sturen
  - listAvailable()
    * functie om alle beschikbare docenten te laden
  - buildWeekTimetable()
    * functie om de daadwerkelijke dagen te plaatsen
  - buildDay()
    * functie om een dag in te vullen
  - buildHour()
    * functie om uur te bouwen
      + de inputs hangen af van de "mode" (docent/klas)
*/
function buildSelect(type = 'klas', list = {
  d: [],
  k: []
}) {
  let title = {
    d: 'docent',
    k: 'klas',
    jl: 'jaarlaag'
  };
  let button = document.getElementById('selectKlasDocent').parentElement.children[0];
  let html = '<input type="hidden" name="displayModeFinal" onChange="" value="">\
  <input type="search" placeholder="Filter..." onkeyup="filterDropdown(this)" onkeypress="enterSelectValue(event, this)">\
  <span>Geen resultaten...</span>'
  // de select moet anders gedaan worden afhangend van de type
  //select voor klas
  if (type == 'k') {
    for (var i = 0; i < list.k.length; i++) {
      //maak option met klas en als value klas object
      html += '<a href="javascript:void(0)" onclick="setValue(this);setWeekTimetable(this.dataset.value)" data-value=\'' + JSON.stringify(list[type][i]).replaceChar() + '\'>' + list[type][i].n + '</a>';
    }
    button.value = 'Selecteer een klas';
    button.dataset.title = 'Selecteer een klas';
  }
  //select voor docent
  else if (type == 'd') {
    //maak een option met docent en als value docent object
    for (var i = 0; i < list.d.length; i++) {
      html += '<a href="javascript:void(0)" onclick="setValue(this);setWeekTimetable(this.dataset.value)" data-value=\'' + JSON.stringify(list[type][i]).replaceChar() + '\'>' + list[type][i].username + '</a>';
    }
    button.value = 'Selecteer een docent';
    button.dataset.title = 'Selecteer een docent';
  }
  //selectt voor jaarlagen
  else if (type == 'jl') {
    for (var i = 0; i < list.jl.length; i++) {
      //maak option met klas en als value klas object
      html += '<a href="javascript:void(0)" onclick="setValue(this);setWeekTimetable(this.dataset.value)"' + JSON.stringify(list[type][i]).replaceChar() + '\'>' + list[type][i].j + list[type][i].ni + '</a>';
    }
    button.value = 'Selecteer een jaarglaag';
    button.dataset.title = 'Selecteer een jaarglaag';
  }
  document.getElementById('selectKlasDocent').innerHTML = html;
  button.style.display = "";
}

function setWeekTimetable(input) {
  load(true);
  let main = document.getElementsByTagName('main')[0];
  let mode = document.getElementsByName('displayMode')[0].value;
  let inputObj = JSON.parse(input);
  //klas
  if (mode == 'k') {
    url = '/api.php?readKlas=true&klas=' + encodeURIComponent(inputObj.n);
  }
  //docent
  else if (mode == 'd') {
    url = '/api.php?readDocent=true&docent=' + encodeURIComponent(inputObj.username);
    availability = inputObj.availability
  } else {
    load(false);
    message('Invalid mode');
    return 0;
  }
  //laad afspraken
  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        let response = JSON.parse(this.responseText);
        //fix main css
        main.style.display = '';
        main.className = 'noP';
        //fix footer
        document.getElementsByTagName('footer')[0].style.position = 'relative';
        buildWeekTimetable(response, mode, inputObj);
        load(false);
      } catch (e) {
        load(false);
        errorMessage(e);
      }
    }
  };
  xhttp.open("GET", url, true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}

//function om het rooster te "bouwen"
function buildWeekTimetable(data, mode, obj) {
  let main = document.getElementsByTagName('main')[0];
  //laad config (dagen, aantal lesuren, lestijden)
  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        let config = JSON.parse(this.responseText);
        listAvailable(config, data, mode, obj);
      } catch (e) {
        load(false);
        errorMessage(e);
      }
    }
  };
  xhttp.open("GET", "/api.php?loadConfig=true", true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}

function listAvailable(config, data, mode, obj) {
  let main = document.getElementsByTagName('main')[0];
  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        let list = JSON.parse(this.responseText);
        let html = '';
        for (var i = 0; i < config.dagen.length; i++) {
          //voeg dag toe aan html
          html += buildDay(config, data, mode, obj, i, list);
        }
        //haal noP weg
        main.className = '';
        main.innerHTML = html;
      } catch (e) {
        load(false);
        errorMessage(e);
      }
    }
  };
  xhttp.open("GET", "/api.php?listAvailable=true", true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}

//functie om dag de bouwen, 1 row in de main
function buildDay(conf, data, mode, obj, nmr, listAvailable) {
  let html = '<section>';
  let dag = conf.dagen[nmr];
  html += '<p>' + dag + '</p>';
  //check of docent wel op school is
  // - obj is object met docent/klas data
  // - in availability staat wanneer een docent op school is
  // - als obj[docent].availability[dag+i] gezet is
  // - als value FALSE is is docent niet op school
  if (mode == 'd' && typeof obj.availability[dag] !== 'undefined' && obj.availability[dag] !== null && !obj.availability[dag]) {
    return buildUnavailable(conf.uren, dag);
  }
  for (var i = 0; i < conf.uren; i++) {
    html += buildHour(conf, data, mode, obj, dag + i, i, listAvailable);
    //voeg pauze toe als het niet het laatste uur is
    if (i < (conf.uren - 1)) {
      html += '<div class="pauze"></div>';
    }
  }
  html += '</section>';
  return html;
}

//functie om uur te bouwen. 1 div in een row
function buildHour(conf, data, mode, obj, dagdeel, uur, listAvailable) {
  //check of er een afspraak gezet is in het huidige dagdeel
  if (typeof data[dagdeel] !== 'undefined' && data[dagdeel] !== null) {
    //voeg daypart aan data toe
    data[dagdeel].daypart = dagdeel;
    //geef afspraak view
    let html = '<div class="uur afspraak" data-hour=\'' + JSON.stringify(data[dagdeel]).replace(/\'/g, "&#39;") + '\'>\n';
    //roostertijden
    //delete
    html += '<button type="button" class="SVGbutton" onclick="deleteHour(this.parentElement.dataset.hour)"><img src="/img/closeBlack.svg"></button>\n';
    //edit
    html += '<button type="button" class="SVGbutton" onclick="editHour(this.parentElement.dataset.hour)"><img src="/img/pencil-edit-button.svg"></button>\n';
    //enlarge
    html += '<button type="button" class="SVGbutton" onclick="enlargeHour(this.parentElement.dataset.hour)"><img src="/img/enlarge.svg"></button>';

    html += '<p>' + conf.lestijden[uur] + '</p>\n';
    //remove button en afspraak data
    html += '<div class="menu list">'
    //docenten
    html += '<span>Docent1:</span><span>' + escapeHTML(data[dagdeel].d[0]) + '</span>\n';
    html += '<span>Docent2:</span><span>' + escapeHTML(data[dagdeel].d[1]) + '</span>\n';
    //klassen
    html += '<span>Klas:</span><span>' + escapeHTML(data[dagdeel].k[0].n) + '</span>';
    //lokalen
    html += '<span>Lokaal1:</span><span>' + escapeHTML(data[dagdeel].l[0]) + '</span>\n';
    html += '<span>Lokaal2:</span><span>' + escapeHTML(data[dagdeel].l[1]) + '</span>\n';
    //overig
    html += '<span>Laptops:</span><span>' + escapeHTML(data[dagdeel].la) + '</span>\n';
    html += '<span class="L1">Project&#xfeff;:</span><span class="projectCode">' + data[dagdeel].p + '</span>\n';
    html += '<span>Note:</span><span class="note">' + escapeHTML(data[dagdeel].no) + '</span>\n';

    html += '</div>\n</div>\n'
    return html;
  }
  let html = '<div class="uur input">\n<p>' + conf.lestijden[uur] + '</p>\n<div class="menu">\n';
  //input js
  //de bovenste row hangt af van de geselecterde mode
  if (mode == 'k') {
    //klas input
    html += '<input type="hidden" name="' + dagdeel + 'klas1" value="'+obj.n+'">\n';
    //build select en voeg options toe
    html += makeList(dagdeel, 'd', 'Docent1', listAvailable, dagdeel + 'docent1');
    html += makeList(dagdeel, 'd', 'Docent2', listAvailable, dagdeel + 'docent2');
  } else if (mode == 'd') {
    //docent input
    html += '<input type="hidden" name="' + dagdeel + 'docent1" value="' + obj.username + '">\n';
    html += makeList(dagdeel, 'd', 'Docent2', listAvailable, dagdeel + 'docent2');
    html += makeList(dagdeel, 'k', 'Klas', listAvailable, dagdeel + 'klas1');
  } else {
    message('Mode Error');
    load(false);
    return '<h1>Mode Error</h1>';
  }
  //lokaal1 & lokaal2
  html += makeList(dagdeel, 'l', 'Lokaal1', listAvailable, dagdeel + 'lokaal1');
  html += makeList(dagdeel, 'l', 'Lokaal2', listAvailable, dagdeel + 'lokaal2');
  //laptops
  html += '<div>\
  <input type="number" name="' + dagdeel + 'laptops" placeholder="Laptops">\
  </div>';
  //project code
  html += makeList(dagdeel, 'p', 'Project', listAvailable, dagdeel + 'projectCode');
  html += '</div>\n';
  //note
  html += '<input type="text" name="' + dagdeel + 'note" placeholder="Note">\n';
  html += '<button type="button" onclick="sendHour(\'' + dagdeel + '\', \'' + dagdeel + '\')">Go</button>\n';
  html += '</div>\n';
  return html;
}

function buildUnavailable(uren, dag) {
  let html = '<section>\n<p>' + dag + '</p>\n';
  for (var i = 0; i < uren; i++) {
    html += '<div class="uur afwezig">\n<p>Docent is niet op school</p>\n</div>\n';
    if (i < (uren - 1)) {
      html += '<div class="pauze"></div>\n'
    }
  }
  html += '</section>\n';
  return html;
}
