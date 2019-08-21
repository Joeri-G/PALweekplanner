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
function buildSelect(type = 'klas', list = {d: [], k: []}) {
  let title = {
    d: 'docent',
    k: 'klas',
    jl: 'jaarlaag'
  };

  let html = '<option selected disabled>' + title[type] + '</option>'
  // de select moet anders gedaan worden afhangend van de type
  //select voor klas
  if (type == 'k') {
    for (var i = 0; i < list.k.length; i++) {
      //maak option met klas en als value klas object
      html += '<option value=\'' + JSON.stringify(list[type][i]) + '\'>' + list[type][i].j + list[type][i].ni + list[type][i].nu + '</option>';
    }
  }
  //select voor docent
  else if (type == 'd') {
    //maak een option met docent en als value docent object
    for (var i = 0; i < list.d.length; i++) {
      html += '<option value=\'' + JSON.stringify(list[type][i]) + '\'>' + list[type][i].username + '</option>';
    }
  }
  //selectt voor jaarlagen
  else if (type == 'jl') {
    for (var i = 0; i < list.jl.length; i++) {
      //maak option met klas en als value klas object
      html += '<option value=\'' + JSON.stringify(list[type][i]) + '\'>' + list[type][i].j + list[type][i].ni + '</option>';
    }
  }
  document.getElementsByName('displayModeFinal')[0].innerHTML = html;
}

function setWeekTimetable(input) {
  load(true);
  let main = document.getElementsByTagName('main')[0];
  let mode = document.getElementsByName('displayMode')[0].value;
  let inputObj = JSON.parse(input);
  //klas
  if (mode == 'k') {
    url = '/api.php?readKlas=true&jaar='+encodeURIComponent(inputObj.j)+'&niveau='+encodeURIComponent(inputObj.ni)+'&nummer='+encodeURIComponent(inputObj.nu);
  }
  //docent
  else if (mode == 'd') {
    url = '/api.php?readDocent=true&docent='+encodeURIComponent(inputObj.username);
    availability = inputObj.availability
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
      }
      catch (e) {
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
      }
      catch (e) {
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
      }
      catch (e) {
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
  if (mode == 'docent' && typeof obj.availability[dag] !== 'undefined' && obj.availability[dag] !== null && !obj.availability[dag]) {
    return buildUnavailable(conf.uren, dag);
  }
  for (var i = 0; i < conf.uren; i++) {
    html += buildHour(conf, data, mode, obj, dag+i, i, listAvailable);
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
    //geef afspraak view
    let html = '<div class="uur afspraak" data-hour=\'' + JSON.stringify(data[dagdeel]).replace(/\'/g, "&#39;") + '\'>\n';
    //roostertijden
    html += '<button type="button" class="SVGbutton" onclick="deleteHour(this.parentElement.dataset.hour)"><img src="/img/closeBlack.svg"></button>\n';
    html += '<p>' + conf.lestijden[uur] + '</p>\n';
    //remove button en afspraak data
    html += '<div class="menu list" onclick="enlargeHour(this.parentElement.dataset.hour)">'
    //docenten
    html += '<span>Docent1:</span><span>' + data[dagdeel].d[0] + '</span>\n';
    html += '<span>Docent2:</span><span>' + data[dagdeel].d[1] + '</span>\n';
    //klassen
    // html += '<span>Klas1:</span><span>' + data[dagdeel].klas[0].jaar + data[dagdeel].klas[0].niveau + data[dagdeel].klas[0].nummer + '</span>';
    // html += '<span>Klas2:</span><span>' + data[dagdeel].klas[1].jaar + data[dagdeel].klas[1].niveau + data[dagdeel].klas[1].nummer + '</span>';
    html += '<span>Klas:</span><span>' + data[dagdeel].k[0].j + data[dagdeel].k[0].ni + data[dagdeel].k[0].nu + '</span>';
    //lokalen
    html += '<span>Lokaal1:</span><span>' + data[dagdeel].l[0] + '</span>\n';
    html += '<span>Lokaal2:</span><span>' + data[dagdeel].l[1] + '</span>\n';
    //overig
    html += '<span>Laptops:</span><span>' + data[dagdeel].la + '</span>\n';
    html += '<span class="L1">ProjectCode&#xfeff;:</span><span class="projectCode">' + data[dagdeel].p + '</span>\n';
    html += '<span>Note:</span><span class="note">' + data[dagdeel].no + '</span>\n';

    html += '</div>\n</div>\n'
    return html;
  }
  let html = '<div class="uur input">\n<p>' + conf.lestijden[uur] + '</p>\n<div class="menu">\n';
  //input js
  //de bovenste row hangt af van de geselecterde mode
  if (mode == 'k') {
    //klas input
    html += '<input type="hidden" name="' + dagdeel + 'klas1" value="klas0" data-k=\'{"data":['+JSON.stringify(obj)+']}\'>\n';
    //build select en voeg options toe
    // html += '<select name="' + dagdeel + 'docent1">' + makeList(dagdeel, 'd', 'Docent1', listAvailable) + '</select>\n';
    // html += '<select name="' + dagdeel + 'docent2">' + makeList(dagdeel, 'd', 'Docent2', listAvailable) + '</select>\n';
    html += makeList(dagdeel, 'd', 'Docent1', listAvailable, dagdeel + 'docent1', 1);
    html += makeList(dagdeel, 'd', 'Docent2', listAvailable, dagdeel + 'docent2', 1);
  }
  else if (mode == 'd') {
    //docent input
    html += '<input type="hidden" name="' + dagdeel + 'docent1" value="' + obj.username + '">\n';
    // html += '<select name="' + dagdeel + 'docent2">' + makeList(dagdeel, 'd', 'Docent2', listAvailable) + '</select>\n';
    html += makeList(dagdeel, 'd', 'Docent2', listAvailable, dagdeel + 'docent2', 1);
    html += '<select name="' + dagdeel + 'klas1" data-k=\'{"data":'+JSON.stringify(listAvailable.k[dagdeel])+'}\'>' + makeList(dagdeel, 'k', 'klas', listAvailable) + '</select>\n';
  }
  else {
    message('Mode Error');
    load(false);
    return '<h1>Mode Error</h1>';
  }
  // html += '<select name="' + dagdeel + 'klas2" data-k=\'{"data":'+JSON.stringify(listAvailable.klas[dagdeel])+'}\'>' + makeList(dagdeel, 'klas', 'klas2', listAvailable) + '</select>\n';
  //lokaal1 & lokaal2
  // html += '<select name="' + dagdeel + 'lokaal1">' + makeList(dagdeel, 'l', 'Lokaal1', listAvailable) + '</select>\n';
  // html += '<select name="' + dagdeel + 'lokaal2">' + makeList(dagdeel, 'l', 'Lokaal2', listAvailable) + '</select>\n';
  html += makeList(dagdeel, 'l', 'Lokaal1', listAvailable, dagdeel + 'lokaal1');
  html += makeList(dagdeel, 'l', 'Lokaal2', listAvailable, dagdeel + 'lokaal2');
  //laptops
  html += '<div>\
  <input type="number" name="' + dagdeel + 'laptops" min="0" max="1000" placeholder="Laptops">\
  </div>';
  //project code
  // html += '<select name="' + dagdeel + 'projectCode">' + makeProjectList('p', 'Project Code', listAvailable) + '</select>';
  html += makeList(dagdeel, 'p', 'Afk.', listAvailable, dagdeel + 'projectCode');
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
