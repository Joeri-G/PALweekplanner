//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
script met functions die voor alle "viewModes" gebruikt worden
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
*/

var activeDrop = false;

//function voor menu buttons
function menu(bool) {
  if (bool) {
    let menu = document.getElementsByTagName('menu')[0].style.display = 'block';
  }
  else if (!bool) {
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
      setTimeout(function(){
        img.style.display = "none"; img.setAttribute('class', '');
        loadingContent.innerHTML = '';
      }, 200);
    }, 500);
  }
}

//function om berichten weer te geven
function message(text = '', escape = true) {
  //declare objects
  let messageModal = document.getElementById('messageModal');
  let messageModalContent = document.getElementById('messageModalContent');
  //escape text
  if (escape) {
    text = escapeHTML(text);
  }
  //zet text
  messageModalContent.innerHTML = text;

  setTimeout(function(){
    messageModal.style.display = 'block';
    messageModalContent.setAttribute('class', 'fade-in');
    //haal scroll weg uit document
    document.body.style.overflow = 'hidden';
  }, 200);
}

//functie om HTML te escapen
function escapeHTML(input) {
  //replace less than
  let out = input.replace(/</g, '&lt;');
  //replace greater than
  out = out.replace(/>/g,'&gt;');
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
  text += '\n\nKlas: ' + escapeHTML(json.k[0].j + json.k[0].ni + json.k[0].nu);
  text += '\n\nLokaal1: ' + escapeHTML(json.l[0]);
  text += '\nLokaal2: ' + escapeHTML(json.l[0]);
  text += '\n\nProjectCode: ' + escapeHTML(json.p);
  text += '\n\nLaptops: ' + escapeHTML(json.la);
  text += '\nNote: ' + escapeHTML(json.no);
  message(text);
}

function deleteHour(data, mode = 0) {
  load(true);
  if(confirm('Wilt u deze afspraak verwijderen?')) {
    let xhttp= new XMLHttpRequest();
    //laad list met alle docenten en klassen
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        message(this.responseText);
        load(false);
        setTimeout(function() {
          if (mode == 0) {
            setWeekTimetable(document.getElementsByName('displayModeFinal')[0].value);
          }
          else if (mode == 1) {
            modeGrid();
          }
          else if (mode == 2) {
            let el = document.getElementsByName('selectJaarlaag')[0];
            buildJaarlaag(el.value, el.dataset.jaarlagen);
          }
        }, 1000);
      }
    };
    let id = JSON.parse(data).ID;
    xhttp.open("GET", "/api.php?delete=true&ID="+encodeURIComponent(id), true);
    xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
    xhttp.send();
  }
  else {
    setTimeout(function() {load(false);}, 200);
  }
}

function makeList(dagdeel, type, lable, listAvailable, name = '', dataset = '') {
  let title = [];
  let value = [];
  let html = '';
  let lijst = [];
  if (type == 'p') {
    lijst = listAvailable[type];
  }
  else {
    lijst = listAvailable[type][dagdeel];
  }
  if (type == 'k') {
    for (var i = 0; i < lijst.length; i++) {
      title.push(lijst[i].j+lijst[i].ni+lijst[i].nu);
      value.push('klas'+i);
    }
    html = buildDropdown(title, value, lable, name, dataset)
  }
  else {
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
  let inputs = ['docent1', 'docent2', 'klas1',/* 'klas2',*/ 'lokaal1', 'lokaal2', 'laptops', 'projectCode', 'note'];
  let url = '/api.php?insert=true&daypart='+dagdeel;
  let value;
  let klassen = 0;
  for (var i = 0; i < inputs.length; i++) {
    value = document.getElementsByName(name+inputs[i])[0].value;
    //als het veld leeg is maak er dan None van
    if (value == '') { value = "None"};
    //als de value een klas is moeten we wat meer doen om het jaar, niveau en nummer er uit te krijgen
    if (inputs[i].substring(0,4) == 'klas' ) {
      klassen++;
      if (value !=='None') {
        let pos = Number(value.substring(4, 5));
        let json = JSON.parse(document.getElementsByName(name+inputs[i])[0].dataset.k);
        url += '&klas'+klassen+'jaar='+json.data[pos].j;
        url += '&klas'+klassen+'niveau='+json.data[pos].ni;
        url += '&klas'+klassen+'nummer='+json.data[pos].nu;
      }
      else {
        url += '&klas'+klassen+'jaar=None';
        url += '&klas'+klassen+'niveau=None';
        url += '&klas'+klassen+'nummer=None';
      }
    }
    else {
      //voeg de key en value toe aan de url
      url += '&'+encodeURIComponent(inputs[i])+'='+encodeURIComponent(value);
    }
  }
  let xhttp4= new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp4.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      message(this.responseText);
      setTimeout(function() {
        if (mode == 0) {
          setWeekTimetable(document.getElementsByName('displayModeFinal')[0].value);
        }
        else if (mode == 1) {
          modeGrid();
        }
        else if (mode == 2) {
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
function sortTable(table) {
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
}


function checkEmpty(input = []) {
  for (var i = 0; i < input.length; i++) {
    if (input[i].replace(/ /g, '') == '' || input[i] == null || input[i] == undefined) {
      return false;
    }
  }
  return true;
}

//
//
// //makeProjectList
// function makeProjectList(type, lable, listAvailable) {
//   //haal de data uit de lijst met beschikbare docenten, klassen, etc
//   let lijst = listAvailable[type];
//   let html = '<option selected disabled value="None">'+lable+'</option>\n';
//   html += '<option value="None">Geen</option>';
//   for (var i = 0; i < lijst.length; i++) {
//     html += '<option value="'+lijst[i]+'">'+lijst[i]+'</option>';
//   }
//   return html;
// }


//functies voor custom select boxes
String.prototype.replaceChar = function(html = false) {
  let obj = {'\"' : '\"', '&quot' : '\"'};
  if (html) {
    obj = {'>' : '&gt;', '<' : ' &lt;', '\"' : "&quot;"};
  }
  var retStr = this;
  for (var x in obj) {
      retStr = retStr.replace(new RegExp(x, 'g'), obj[x]);
  }
  return retStr;
};

function buildDropdown(data = [], value = false, title = 'Title', name = 'Name', dataset = '') {
  if (!value) {
    value = data;
  }
  if (value.length !== data.length) {
    errorMessage('invalid array length');
    return '';
  }
  let html = '<div class="dropSelect">\
  <input type="button" value="' + title.replaceChar() + '" onclick="toggleDrop(this)" data-title="' + title.replaceChar() + '">\
  <div class="drop">\
  <input type="hidden" name="' + name.replaceChar() + '" value="None" ' + dataset + '>\
  <input type="search" placeholder="Filter..." onkeyup="filterDropdown(this)">\
  <a href="javascript:void(0)" onclick="setValue(this)" class="shown" data-value="None">Geen Selectie</a>';
  for (var i = 0; i < data.length; i++) {
    html += '<a href="javascript:void(0)" onclick="setValue(this)" data-value="' + value[i].replaceChar() + '">' + data[i].replaceChar(true) + '</a>';
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
    }
    else if (item[i].classList.contains('shown')) {
      item[i].style.display = "";
    }
    else {
      item[i].style.display = "none";
    }
  }
  if (!hasContent) {
    parent.getElementsByTagName("span")[0].style.display = 'block';
  }
  else {
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
  //fix de title

  let master = parent.parentElement;
  let button = master.getElementsByTagName('input')[0];
  let defaultTitle = button.dataset.title;
  button.value = (defaultTitle + ' | ' + el.innerHTML.replaceChar()).substr(0, 12);
  //als er geen title is zet dan de title naar alleen de value
  if (defaultTitle == '') {
    button.value = el.innerHTML.replaceChar().substr(0, 12);
  }
  toggleDrop(el.parentElement);
}



//JS voor message modal
//wanneer er buiten de modal geklikt wordt, sluit de modal
let modalBox = document.getElementById("messageModal");
window.onclick = function(event) {
  if (event.target == modalBox) {
    //haal no-scroll weg uit HTML
    let messageModal = document.getElementById('messageModal');
    let messageModalContent = document.getElementById('messageModalContent');
    //fade out
    messageModalContent.setAttribute('class', 'fade-out');
    //remove fadeout
    setTimeout(function() {
      document.body.style.overflow = '';
      messageModal.style.display = "none";
      messageModal.setAttribute('class', '');
    }, 200);
  }
  //add dropdown
  //als de target niet de dropdown is, niet de dropdown toggle button en de dropdown gezet is, haal dan de dropdown weg
  else if (event.target.parentElement !== activeDrop && activeDrop !== false && event.target !== activeDrop.parentElement.children[0]) {
    let dropBtn = activeDrop.parentElement.children[0];
    toggleDrop(dropBtn)
  }
}
