//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
function toggleProjecten(element) {
  load(true);
  let projectList = document.getElementById('projectList');
  if (element.dataset.toggle == 'hidden') {
    projectList.style.display = 'block';
    loadProjecten(projectList);
    element.dataset.toggle = 'shown';
    element.innerHTML = 'Hide';
  }
  else if (element.dataset.toggle == 'shown') {
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
      }
      catch (e) {
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
    <img src="/img/closeBlack.svg" alt="remove" onclick="deleteProject(this.parentElement.dataset.project)">\
    </div></td>\
    </tr>';
  }
  out.innerHTML = html;
}

function deleteProject(data) {
  let xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        let data = JSON.parse(this.responseText);
        //build table
        buildTable(data, out);
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
  xhttp.open("GET", "/api.php?deleteProject=true", true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
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
  let verantwoordelijke = document.getElementById('projectLeider').value;
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

//build projectleider list
let xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
  if (this.readyState == 4 && this.status == 200) {
    try {
      let data = JSON.parse(this.responseText);
      let element = document.getElementById('projectLeider');
      let html = '<option disabled selected>Projectleider</option>';
      for (var i = 0; i < data.length; i++) {
        html += '<option value="' + data[i] + '">' + data[i] + '</option>'
      }
      element.innerHTML = html;
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
xhttp.open("GET", "/api.php?listDocent=true", true);
xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
xhttp.send();
