//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
script met functions die voor de projecten pagina nodig zijn
  - toggleProjecten()
     * functie om te kijken of projecten geladen moeten worden of juist weggehaald

  - loadProjecten()
    * functie om projecten te laden

  - buildTable()
    * functie om table te maken met alle projecten

  - deleteProject()
    * functie om geselecteerd project te verwijderen

  - enlargeProject()
    * functie om als er op het oogje geklikt wordt een modal weer te geven met alle project informatie

  - addProject()
    * functie om project toe te voegen

  - editProject()
    * functie om projectspecificaties aan te passen

  - updateProject()
    * functie om aangepaste projectspecificaties naar de server te sturen

  - script om lijst met projectleiders te bouwen

*/
function toggleProjecten(element) {
  load(true);
  let projectList = document.getElementById('projectList');
  if (element.dataset.toggle == 'hidden') {
    projectList.style.display = 'block';
    loadProjecten(projectList);
    element.dataset.toggle = 'shown';
    element.innerHTML = 'Hide';
  } else if (element.dataset.toggle == 'shown') {
    projectList.style.display = 'none';
    element.dataset.toggle = 'hidden';
    element.innerHTML = 'Show';
    load(false);
  }
}

function loadProjecten(out) {
  let xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        let data = JSON.parse(this.responseText);
        //build table
        buildTable(data, out);
        //stop loading animatie
        load(false);
      } catch (e) {
        //stop loading animatie
        load(false);
        errorMessage(e);
      }
    }
  };
  xhttp.open("GET", "/api.php?listProjects=true", true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}

function buildTable(data, out) {
  let html = '<table id="projectenTable">\
  <tr>\
  <th>Titel</th> <th>Afkorting</th> <th>Verantwoordelijke</th> <th>Beschrijving</th> <th>Instructie</th> <th>Actie</th>\
  </tr>';
  for (var i = 0; i < data.length; i++) {
    html += '<tr>\
    <td>' + data[i].title.substr(0, 10) + '</td>\
    <td>' + data[i].code.substr(0, 10) + '</td>\
    <td>' + data[i].verantwoordelijke.substr(0, 10) + '</td>\
    <td>' + data[i].beschrijving.substr(0, 10) + '</td>\
    <td>' + data[i].instructie.substr(0, 10) + '</td>\
    <td><div class="actions" data-project=\'' + JSON.stringify(data[i]).replace(/\'/g, "&#39;") + '\'>\
    <img src="/img/enlarge.svg" onclick="enlargeProject(this.parentElement.dataset.project)" alt="Enlarge">\
    <img src="/img/pencil-edit-button.svg" onclick="editProject(this.parentElement.dataset.project)" alt="Edit">\
    <img src="/img/closeBlack.svg" alt="remove" onclick="deleteProject(this.parentElement.dataset.project)">\
    </div></td>\
    </tr>';
  }
  out.innerHTML = html;
}

function deleteProject(data) {
  load(true)
  let id = JSON.parse(data).ID
  let url = "/api.php?deleteProject=true&id="+encodeURIComponent(id)
  sendReq(url, function(resp) {
    load(false)
    message(resp)
    loadProjecten(document.getElementById("projectList"))
  })
}

function enlargeProject(data) {
  let json = JSON.parse(data);
  let text = 'Titel: ' + json.title +
    '\nAfkorting: ' + json.code +
    '\n\nVerantwoordelijke: ' + json.verantwoordelijke +
    '\n\nBeschrijving: ' + json.beschrijving +
    '\n\nInstructie: ' + json.instructie;
  message(text);
}


function addProject() {
  load(true);

  let title = document.getElementById('projectTitel').value;
  let afkorting = document.getElementById('projectAfkorting').value;
  let verantwoordelijke = document.getElementsByName('projectLeider')[0].value;
  let beschrijving = document.getElementById('projectBeschrijving').value;
  let instructie = document.getElementById('projectInstructie').value;

  let POST = 'title=' + encodeURIComponent(title) +
    '&afkorting=' + encodeURIComponent(afkorting) +
    '&verantwoordelijke=' + encodeURIComponent(verantwoordelijke) +
    '&beschrijving=' + encodeURIComponent(beschrijving) +
    '&instructie=' + encodeURIComponent(instructie);

  //check of wel in ieder veld wat is ingevuld
  if (!checkEmpty([title, afkorting, verantwoordelijke, beschrijving, instructie])) {
    load(false);
    message('Niet alle velden zijn ingevuld');
  }

  let xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      message(this.responseText);
      load(false);
    }
  };
  xhttp.open("POST", "/api.php?addProject=true", true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(POST);
}

function editProject(data) {
  data = JSON.parse(data)

  let html = "<p>Edit</p>\
  <p>Laat de dropdown leeg om de huidige waarde te laten staan</p>\
  <div class=\"editInput\">\
  <span class=\"col_3\">\
  <span><input type=\"text\" placeholder=\"Titel\" id=\"editProjectTitle\" value=\"" + data.title.replaceChar(true) + "\"></span>\
  <span><input type=\"text\" placeholder=\"Afkorting\" id=\"editProjectAfkorting\" value=\"" + data.code.replaceChar(true) + "\"></span>\
  <span id=\"editProjectLeider\">" + document.getElementById("projectLeider").innerHTML + "</span>\
  </span>\
  <textarea placeholder=\"Beschrijving van project\" id=\"editProjectBeschrijving\">" + data.beschrijving.replaceChar(true) + "</textarea>\
  <textarea placeholder=\"Instructies voor leerlingen\" id=\"editProjectInstructie\">" + data.instructie.replaceChar(true) + "</textarea>\
  <button type=\"button\" class=\"button\" onclick=\"updateProject('" + data.ID + "')\">Go!</button>\
  </div>"
  //voor de dropdown wordt de data van de andere dropdown gecloned. De docenten veranderen (meestal) niet
  //waarde kan geselecteerd worden met document.getElementsByName("projectLeider")[1].value

  //set content
  document.getElementById("editModalContent").innerHTML = html
  //fadein
  setTimeout(function() {
    let editModal = document.getElementById("editModal");
    let editModalContent = document.getElementById("editModalContent");

    editModal.style.display = 'block';
    editModalContent.setAttribute('class', 'messageModalContent fade-in');
  }, 200);
  //zet verantwoordelijke
  document.getElementsByName("projectLeider")[1].value = data.verantwoordelijke
}

function updateProject(ID = "-1") {
  //haal modal weg
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

  load(true)
  let title = document.getElementById('editProjectTitle').value
  let afkorting = document.getElementById('editProjectAfkorting').value
  let verantwoordelijke = document.getElementsByName('projectLeider')[1].value
  let beschrijving = document.getElementById('editProjectBeschrijving').value
  let instructie = document.getElementById('editProjectInstructie').value

  let POST = 'title=' + encodeURIComponent(title) +
    '&afkorting=' + encodeURIComponent(afkorting) +
    '&verantwoordelijke=' + encodeURIComponent(verantwoordelijke) +
    '&beschrijving=' + encodeURIComponent(beschrijving) +
    '&instructie=' + encodeURIComponent(instructie) +
    '&id=' + encodeURIComponent(ID)

  let xhttp = new XMLHttpRequest()
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      message(this.responseText)
      //update de projecten list
      let projectList = document.getElementById('projectList')
      loadProjecten(projectList)
      load(false)
    }
  };
  xhttp.open("POST", "/api.php?updateProject=true", true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(POST);
}

//build projectleider list
let xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
  if (this.readyState == 4 && this.status == 200) {
    try {
      let data = JSON.parse(this.responseText);
      let el = document.getElementById('projectLeider');
      let input = {
        d: {
          a: data
        }
      };
      let html = makeList('a', 'd', 'Projectleider', input, 'projectLeider');
      el.innerHTML = html;
      //stop loading animatie
      load(false);
    } catch (e) {
      //stop loading animatie
      load(false);
      errorMessage(e);
    }
  }
};
xhttp.open("GET", "/api.php?listDocent=true", true);
xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
xhttp.send();
