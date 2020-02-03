//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
script met functions die door alle "viewModes" en paginas gebruikt worden
  - menu()
    * functie om menu te toggelen

  - errorMessage()
    * functie om message met error melding weer te geven

  - load()
    * functie om laad animatie weer te geven

  - message()
    * functie om message modal weer te geven met bericht

  - escapeHTML()
    * functie om html te sterilizen om javascritp injections te voorkomen

  - enlargeHour()
    * functie om modal weer te geven met alle informatie van een afspraak omdat soms lines worden afgekapt

  - deleteHour()
    * functie om request naar server te sturen om afspraak te verwijderen

  - makeList()
    * functie om een option list voor <select> element te maken op basis van een array

  - sendHour()
    * functie om een afspraak te creeren

  - sortTable()
    * functie om table alpahbatisch te sorteren

  - checkEmpty()
    * functie om te checken of een value null is

  - String Prototype replaceChar()
    * simpele functie om enkele karakters naar HTML entities te zetten

  - buildDropdown()
    * functie om de html van de dropdown te bouwen

  - toggleDrop()
    * functie om een dropdown te openen/sluiten
      + check of geselecteerde element dezelfde is als de huidige open element
        ~ als dat het geval is sluit het element
        ~ als dat niet zo is sluit dan het duidige open element, open de nieuwe en update de variable

  - filterDropdown()
    * functie om de dropdown te filteren
      + er wordt input in de search box getypt
      + filter de innerHTML van alle <a> ellements

  - setValue()
    * zet value uit <a> element (in data-value) naar een hidden input in de dropdown parent div

  - sortDropdown()
    * sorteer de dropdown op alpahbatische volgorder

  - sendReq()
    * functie om XMLHTTP GET request te sturen
    * callback

  - sendPostReq()
    * zelfde alse sendReq maar dan POST

  - updateHour()
    * functie om een item te updaten
    * doe een call om oude te deleten
    * doe een call om de nieuwe te updaten
*/

var activeDrop = false;

//function voor menu buttons
function menu(bool) {
  if (bool) {
    let menu = document.getElementsByTagName('menu')[0].style.display = 'block';
  } else if (!bool) {
    let menu = document.getElementsByTagName('menu')[0].style.display = 'none';
  }
}

//function om error modals weer te geven
function errorMessage(error) {
  console.log(error);
  message(error, false);
}

function load(mode) {
  //get image
  let img = document.getElementById('loading');
  let loadingContent = document.getElementById('loadingContent');
  //if show
  if (mode) {
    loadingContent.innerHTML = '<img src="/img/loading.svg" alt="Loading...">';
    img.style.display = 'block';
    loadingContent.setAttribute('class', 'fade-in');
    //start loading animation
  }
  //else
  else {
    setTimeout(function() {
      //fade out
      loadingContent.setAttribute('class', 'fade-out');
      //remove fadeout
      setTimeout(function() {
        img.style.display = "none";
        img.setAttribute('class', '');
        loadingContent.innerHTML = '';
      }, 200);
    }, 500);
  }
}

//function om berichten weer te geven
function message(text = '', escape = true) {
  //als de text leeg is doe dan niets
  if (text == '') {
    return null;
  }


  //declare objects
  let messageModal = document.getElementById('messageModal');
  let messageModalContent = document.getElementById('messageModalContent');
  //escape text
  if (escape) {
    text = escapeHTML(text);
  }
  //zet text
  messageModalContent.innerHTML = text;

  setTimeout(function() {
    messageModal.style.display = 'block';
    messageModalContent.setAttribute('class', 'messageModalContent fade-in');
    //haal scroll weg uit document
    document.body.style.overflow = 'hidden';
  }, 200);
}

//functie om HTML te escapen
function escapeHTML(input) {
  if (typeof input !== "string")
    return input;
  //replace less than
  let out = input.replace(/</g, '&lt;');
  //replace greater than
  out = out.replace(/>/g, '&gt;');
  //replace newline with line break
  out = out.replace(/\n/g, '<br>');
  //replace ' ' (space) with non-breaking space
  out = out.replace(/ /g, '&nbsp;');
  //return
  return out;
}

function enlargeHour(data) {
  let json = JSON.parse(data);
  let text = 'Docent1: ' + escapeHTML(json.d[0]);
  text += '\nDocent2: ' + escapeHTML(json.d[1]);
  text += '\n\nKlas: ' + escapeHTML(json.k[0].n);
  text += '\n\nLokaal1: ' + escapeHTML(json.l[0]);
  text += '\nLokaal2: ' + escapeHTML(json.l[0]);
  text += '\n\nProjectCode: ' + escapeHTML(json.p);
  text += '\n\nLaptops: ' + escapeHTML(json.la);
  text += '\nNote: ' + escapeHTML(json.no);
  message(text);
}

function deleteHour(data, mode = 0) {
  load(true);
  if (confirm('Wilt u deze afspraak verwijderen?')) {
    let xhttp = new XMLHttpRequest();
    //laad list met alle docenten en klassen
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        message(this.responseText);
        load(false);
        setTimeout(function() {
          if (mode == 0) {
            setWeekTimetable(document.getElementsByName('displayModeFinal')[0].value);
          } else if (mode == 1) {
            modeGrid();
          } else if (mode == 2) {
            let el = document.getElementsByName('selectJaarlaag')[0];
            buildJaarlaag(el.value, el.dataset.jaarlagen);
          }
        }, 1000);
      }
    };
    let id = JSON.parse(data).ID;
    xhttp.open("GET", "/api.php?delete=true&ID=" + encodeURIComponent(id), true);
    xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
    xhttp.send();
  } else {
    setTimeout(function() {
      load(false);
    }, 200);
  }
}

function makeList(dagdeel, type, lable, listAvailable, name = '', dataset = '') {
  let title = [];
  let html = '';
  let lijst = [];
  if (type == 'p') {
    lijst = listAvailable[type];
  } else {
    lijst = listAvailable[type][dagdeel];
  }
  if (type == 'k') {
    for (var i = 0; i < lijst.length; i++) {
      title.push(lijst[i].n);
    }
    html = buildDropdown(title, false, lable, name, dataset)
  } else {
    for (var i = 0; i < lijst.length; i++) {
      title.push(lijst[i]);
    }
    html = buildDropdown(title, false, lable, name, dataset)
  }
  return html;
}

function sendHour(name, dagdeel, mode = 0) {
  load(true);
  //we moeten nu de values van alle selections/input uit het parent element halen
  let inputs = ['docent1', 'docent2', 'klas1', 'lokaal1', 'lokaal2', 'laptops', 'projectCode', 'note'];
  let url = '/api.php?insert=true&daypart=' + dagdeel;
  let value;
  let klassen = 0;
  for (var i = 0; i < inputs.length; i++) {
    value = document.getElementsByName(name + inputs[i])[0].value;
    //als het veld leeg is maak er dan None van
    if (value == '') {
      value = "None"
    };
    //als de value een klas is moeten we wat meer doen om het jaar, niveau en nummer er uit te krijgen
    url += '&' + encodeURIComponent(inputs[i]) + '=' + encodeURIComponent(value);
  }
  let xhttp4 = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp4.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      message(this.responseText);
      setTimeout(function() {
        if (mode == 0) {
          setWeekTimetable(document.getElementsByName('displayModeFinal')[0].value);
        } else if (mode == 1) {
          modeGrid();
        } else if (mode == 2) {
          let el = document.getElementsByName('selectJaarlaag')[0];
          buildJaarlaag(el.value, el.dataset.jaarlagen);
        }
      }, 1000);
      return null;
    }
  };
  xhttp4.open("GET", url, true);
  xhttp4.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp4.send();
}


//function om de table te sorten
function sortTable(table, addHeaders, conf) {
  let rows, switching, i, x, y, shouldSwitch;
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
  if (!addHeaders) {
    return;
  }
  let tr = table.getElementsByTagName("tr")
  for (var j = 0; j < tr.length; j++) {
    if ( ( j + 1 ) % 9 == 0 ) {
      tr[j].insertAdjacentElement("afterend", gridDaypartHeader(conf))
    }
  }
}


function checkEmpty(input = []) {
  for (var i = 0; i < input.length; i++) {
    if (input[i].replace(/ /g, '') == '' || input[i] == null || input[i] == undefined) {
      return false;
    }
  }
  return true;
}

//functies voor custom select boxes
String.prototype.replaceChar = function(html = false) {
  let obj = {
    "\'": '&apos;',
    '&quot': '\"'
  };
  if (html) {
    obj = {
      '>': '&gt;',
      '<': ' &lt;',
      '\"': "&quot;"
    };
  }
  var retStr = this;
  for (var x in obj) {
    retStr = retStr.replace(new RegExp(x, 'g'), obj[x]);
  }
  return retStr;
};

function buildDropdown(data = [], value = false, title = 'Title', name = 'Name', dataset = '', st = '') {
  if (!value) {
    value = data;
  }
  if (value.length !== data.length) {
    errorMessage('invalid array length');
    return '';
  }
  if (st == '') {
    st = 'None';
  } else if (typeof st == "number") {
    st = st.toString();
  }

  let html = '<div class="dropSelect">\
  <input type="button" value=\'' + title.replaceChar() + '\' onclick="toggleDrop(this)">\
  <div class="drop">\
  <input type="hidden" name=\'' + name.replaceChar() + '\' value=\'' + st.replaceChar() + '\' ' + dataset + '>\
  <input type="search" placeholder="Filter..." onkeyup="filterDropdown(this)">\
  <a href="javascript:void(0)" onclick="setValue(this)" class="shown" data-value="None">Geen Selectie</a>';
  for (var i = 0; i < data.length; i++) {
    html += '<a href="javascript:void(0)" onclick="setValue(this)" data-value=\'' + value[i].replaceChar() + '\'>' + data[i].replaceChar(true) + '</a>';
  }
  html += '<span>Geen resultaten...</span>\
  </div></div>';
  return html;
}

function toggleDrop(el) {
  let drop = el.parentElement.children[1];
  let isOpen = drop.classList.contains("show");
  if (activeDrop !== false) {
    activeDrop.classList.toggle('show');
  }
  activeDrop = false;
  if (!isOpen) {
    drop.classList.toggle('show');
    drop.getElementsByTagName('input')[1].value = '';
    // //maak value leeg
    // drop.getElementsByTagName('input')[0].value = '';
    //laat alle items zien
    filterDropdown(drop.getElementsByTagName('input')[1], '');
    sortDropdown(drop);

    activeDrop = drop;
  }
}

function filterDropdown(el) {
  let txtValue;
  let parent = el.parentElement;
  let value = el.value.toUpperCase();
  let item = parent.getElementsByTagName("a");
  let hasContent = false;
  for (i = 0; i < item.length; i++) {
    txtValue = item[i].textContent || item[i].innerText;
    if (txtValue.toUpperCase().indexOf(value) > -1) {
      item[i].style.display = "";
      hasContent = true;
    } else if (item[i].classList.contains('shown')) {
      item[i].style.display = "";
    } else {
      item[i].style.display = "none";
    }
  }
  if (!hasContent) {
    parent.getElementsByTagName("span")[0].style.display = 'block';
  } else {
    parent.getElementsByTagName("span")[0].style.display = 'none';
  }
}

function setValue(el) {
  let parent = el.parentElement;
  let input = parent.getElementsByTagName('input')[0];
  let value = el.dataset.value.replaceChar();
  input.value = value;
  //zet class
  let a = el.parentElement.getElementsByTagName('a');
  for (var i = 0; i < a.length; i++) {
    a[i].classList = '';
  }
  el.classList = 'geselecteerd';

  let master = parent.parentElement;
  let button = master.getElementsByTagName('input')[0];
  button.value = el.innerHTML.replaceChar().substr(0, 12);
  toggleDrop(el.parentElement);
}

function sortDropdown(drop) {
  let items, switching, x, y, shouldSwitch;
  switching = true;
  while (switching) {
    switching = false;

    items = drop.getElementsByTagName('a');
    for (var i = 0; i < (items.length - 1); i++) {
      x = items[i];
      y = items[i + 1];
      if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase() && x.innerHTML !== 'Geen Selectie') {
        shouldSwitch = true;
        break;
      }
    }
    if (shouldSwitch) {
      switching = true;
      if (typeof items[i + 1] !== 'undefined') {
        drop.insertBefore(items[i + 1], items[i]);
      } else {
        switching = false;
      }
    }
  }
}

function sendReq(url = '/', callback = function(resp) {
  message(resp);
}, arg = {}) {
  let xhttp = new XMLHttpRequest(arg);
  xhttp.onreadystatechange = (function(xhttp, arg) {
    return function() {
      if (xhttp.readyState == 4 && xhttp.status == 200) {
        return callback(xhttp.responseText, arg);
      }
    }
  })(xhttp, arg);
  xhttp.open("GET", url, true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}

function sendPostReq(url = '/', data, callback = function(resp) {
  message(resp);
}, arg = {}) {
  let xhttp = new XMLHttpRequest(arg);
  xhttp.onreadystatechange = (function(xhttp, arg) {
    return function() {
      if (xhttp.readyState == 4 && xhttp.status == 200) {
        return callback(xhttp.responseText, arg);
      }
    }
  })(xhttp, arg);
  xhttp.open("POST", url, true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(data);
}

function notNone(inp = "None", isKlas = false) {
  let none = ["None", "none", "NONE", "", " "];
  if (none.includes(inp))
    return false;
  if (isKlas == true && !notNone(inp.n))
    return false;
  return true;
}

function editHour(json, mode = 0) {
  //haal beschikbare waardes op
  sendReq('/api.php?listAvailable=true', function(resp, arg) {
    let list = JSON.parse(resp)
    let json = arg[0]
    let mode = arg[1]
    let klas = json.k[0] //de klas
    //zorg er voor dat alles in klas een string is
    klas.n = klas.n.toString()
    list.k = list.k[json["daypart"]] //beschikbare klassen op dagdeel
    list.d = list.d[json["daypart"]] //beschikbare docenten op dagdeel
    list.l = list.l[json["daypart"]] //beschikbare lokalen op dagdeel

    list.k.push(klas)

    for (var i = 0; i < list.k.length; i++) {
      list.k[i] = list.k[i].n
    }

    let listKstring = []
    let listKname = []
    //maak laptops string
    if (typeof json.la == "string") {
      json.la = json.la.replaceChar()
    } else {
      json.la = json.la.toString()
    }
    //html
    let html = "<p>Edit</p>\
    <p>Laat de dropdown leeg om de huidige waarde te laten staan</p>\
    <span class=\"editModalDropdowns\">\
    <input type=\"hidden\" value=\"" + json["daypart"].replaceChar() + "\" name=\"updateDaypart\">"
    //docent 1 & 2
    html += buildDropdown(list.d, false, "Docent1", "updateDocent1", '', json.d[0]) +
      buildDropdown(list.d, false, "Docent2", "updateDocent2", '', json.d[1])
    //klas 1
    html += buildDropdown(list.k, false, 'Klas', "updateKlas1", '', json.k[0].n)
    //lokaal 1 & 2
    html += buildDropdown(list.l, false, "Lokaal1", "updateLokaal1", '', json.l[0]) +
      buildDropdown(list.l, false, "Lokaal2", "updateLokaal2", '', json.l[0])
    //project
    html += buildDropdown(list.p, false, 'Project', "updatePC", '', json.p)
    //laptops
    html += '<input type="number" name="updateLaptops" placeholder="Laptops" value="' + json.la.replaceChar() + '">';
    //note
    html += '<input type="text" name="updateNote" placeholder="Note" value="' + json.no.replaceChar() + '">\n';

    html += '</span><button type="button" class="button" onclick="updateHour(\'' + json.ID + '\', ' + mode + ')">Go!</button>';

    //set content
    document.getElementById("editModalContent").innerHTML = html
    //fadein
    setTimeout(function() {
      let editModal = document.getElementById("editModal");
      let editModalContent = document.getElementById("editModalContent");

      editModal.style.display = 'block';
      editModalContent.setAttribute('class', 'messageModalContent fade-in');
    }, 200);
  }, [ //dit zijn arguments die aan de callback worden doorgegeven
    JSON.parse(json),
    mode
  ])
}


function updateHour(id = "-1", mode = 0) {
  load(true)
  let url = "/api.php?edit=true" +
    "&id=" + id +
    "&daypart=" + val("updateDaypart") +

    "&docent1=" + val("updateDocent1") +
    "&docent2=" + val("updateDocent2") +

    "&lokaal1=" + val("updateLokaal1") +
    "&lokaal2=" + val("updateLokaal2") +

    "&laptops=" + val("updateLaptops") +
    "&projectCode=" + val("updatePC") +
    "&klas=" + val("updateKlas1")

  if (val("updateNote") == "") {
    url += "&note=" + "None";
  } else {
    url += "&note=" + val("updateNote");
  }
  //stuur request, haal de edit modal weg en geef wanneer nodig een error message
  sendReq(url, function(resp) {
    let editModal = document.getElementById("editModal");
    let editModalContent = document.getElementById('editModalContent');
    //fade out
    editModalContent.setAttribute('class', 'messageModalContent fade-out');
    //remove fadeout
    setTimeout(function() {
      let editModal = document.getElementById('editModal');
      editModal.style.display = "none";
      editModal.setAttribute('class', 'messageModal');
    }, 200);

    load(false);
    message(resp);
    //refresh afspraken
    setTimeout(function() {
      if (mode == 0) {
        setWeekTimetable(document.getElementsByName('displayModeFinal')[0].value);
      } else if (mode == 1) {
        modeGrid();
      } else if (mode == 2) {
        let el = document.getElementsByName('selectJaarlaag')[0];
        buildJaarlaag(el.value, el.dataset.jaarlagen);
      }
    }, 1000);
  })
}

function val(el) {
  return encodeURIComponent(document.getElementsByName(el)[0].value);
}

//JS voor message modal
//wanneer er buiten de modal geklikt wordt, sluit de modal
let messageModal = document.getElementById("messageModal");
let messageModalContent = document.getElementById('messageModalContent');

let editModal = document.getElementById("editModal");

window.onclick = function(event) {
  if (event.target == messageModal) {
    //fade out
    messageModalContent.setAttribute('class', 'messageModalContent fade-out');
    //remove fadeout
    setTimeout(function() {
      //haal no-scroll weg uit HTML
      document.body.style.overflow = '';
      let messageModal = document.getElementById('messageModal');
      messageModal.style.display = "none";
      messageModal.setAttribute('class', 'messageModal');
    }, 200);
  }
  //add dropdown
  //als de target niet de dropdown is, niet de dropdown toggle button en de dropdown gezet is, haal dan de dropdown weg
  else if (event.target.parentElement !== activeDrop && activeDrop !== false && event.target !== activeDrop.parentElement.children[0]) {
    let dropBtn = activeDrop.parentElement.children[0];
    toggleDrop(dropBtn)
  }
  //check of edit modal bestaat
  else if (editModal != "undefined" && editModal != null && event.target == editModal) {

    let editModalContent = document.getElementById('editModalContent');
    //fade out
    editModalContent.setAttribute('class', 'messageModalContent fade-out');
    //remove fadeout
    setTimeout(function() {
      let editModal = document.getElementById('editModal');
      editModal.style.display = "none";
      editModal.setAttribute('class', 'messageModal');
    }, 200);
  }
}
