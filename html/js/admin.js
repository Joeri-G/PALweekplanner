//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
  - toggleUsers()
    * functie om te switchen tussen hide / show text en om het laad proces te starten
  - loadUsers()
    * functie om lijst met alle gebruikers te laden
  - buildUserList()
    * functie om de user table te 'bouwen' uit de JSON


  - toggleKlassen()
    * zelfde als toggleUsers() maar voor klassen
  - loadKlassen()
    * zelfde als loadUsers() maar voor klassen
  - buildKlassen()
    * zelfde als buildUsers() maar voor klassen


  - toggleLokalen()
    * zelfde als toggleUsers() maar voor lokalen
  - loadLokalen()
    * zelfde als loadUsers() maar voor lokalen
  - buildLokalen()
    * zelfde als buildUsers() maar voor lokalen


  - sendURL()
    * functie om request te doen, gebruikt door deleteUser(), deleteKlas() en deleteLokaal()


  - deleteUser()
    * functie om gebruikers te verwijderen uit database

  - deleteKlas()
    * zelfde als deleteUser() maar voor klassen

  - deleteLokaal()
    * zelfde als deleteUser() maar voor lokalen


  - addUser()
    * functie om user toe te voegen aan database
      + username
      + password
      + role (docent, admin, leerling, etc)
      + userLVL (waar de gebruiker toegang tot heeft. 2 voor /html/api.php, 4 voor /html/admin/api.php)
      + userAvailability (afhankelijk van dagen in /conf/conf.json)
  - addKlas()
    * zelfde als addUser() maar voor klas
      + jaar
      + niveau
      + nummer
  - addLokaal()
    * zelfde als addUser() maar voor lokalen
      + lokaal

  - deleteAll()
    * functie om alle afspraken te verwijderen

  - updateLaptops()
    * neem de input uit #lokaalList en update conf.json, nerd shit gebeurt in backend

  - addDay()
    *functie om dag aan aantal dagen toe te voegen

  - updateDays()
    * functie om het aantal dagen in de UI te updaten
*/
function toggleUsers(element, config) {
  load(true);
  let userList = document.getElementById('userList');
  if (element.dataset.toggle == 'hidden') {
    userList.style.display = 'block';
    loadUsers(userList, config);
    element.dataset.toggle = 'shown';
    element.innerHTML = 'Hide';
  } else if (element.dataset.toggle == 'shown') {
    userList.style.display = 'none';
    element.dataset.toggle = 'hidden';
    element.innerHTML = 'Show';
    load(false);
  }
}


function loadUsers(userList, config) {
  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        //stop loading animatie
        let data = JSON.parse(this.responseText);
        buildUsers(data, userList, config);
      } catch (e) {
        //stop loading animatie
        load(false);
        errorMessage(e);
      }
    }
  };
  xhttp.open("GET", "/admin/api.php?listUsers=true", true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}

function buildUsers(data, userList, config) {
  let userAvailability;
  let html = '<table id="userTable">\n';
  html += '<tr><th>Username</th><th>Password</th>\
  <th>Rechten</th>\
  <th>Delete</th></tr>';
  for (var i = 0; i < data.length; i++) {
    userAvailability = data[i].userAvailability;
    html += '<tr>\
    <td>' + data[i].username + '</td>\
    <td> *** </td>\
    <td>' + data[i].userLVL + '</td>\
    <td>\
    <div class="actions" data-user=\'' + JSON.stringify(data[i]).replace(/\'/g, "&#39;") + '\'>\
    <img src="/img/closeBlack.svg" alt="remove" onclick="deleteUser(this.parentElement.dataset.user, config)"></div>\
    </td>\
    </tr>\n';
  }
  html += '</table>\n';
  load(false);
  userList.innerHTML = html;
  //sort table
  sortTable(document.getElementById('userTable'));
}

function deleteUser(data, config) {
  load(true);
  if (!confirm("Wilt u deze gebruiker verwijderen?")) {
    load(false);
    return null;
  }
  let ID = JSON.parse(data).ID;
  let url = '/admin/api.php?deleteUser=true&ID=' + encodeURIComponent(ID);
  sendURL(url, function(response) {
    message(response);
    let userList = document.getElementById('userList');
    loadUsers(userList, config);
  });
}

function toggleKlassen(element, config) {
  load(true);
  let klasList = document.getElementById('klasList');
  if (element.dataset.toggle == 'hidden') {
    klasList.style.display = 'block';
    loadKlassen(klasList, config);
    element.dataset.toggle = 'shown';
    element.innerHTML = 'Hide';
  } else if (element.dataset.toggle == 'shown') {
    klasList.style.display = 'none';
    element.dataset.toggle = 'hidden';
    element.innerHTML = 'Show';
    load(false);
  } else {
    element.dataset.toggle = 'hidden';
  }
}

function loadKlassen(klasList, config) {
  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        //stop loading animatie
        let data = JSON.parse(this.responseText);
        buildKlassen(data, klasList, config);
        load(false);
      } catch (e) {
        //stop loading animatie
        load(false);
        errorMessage(e);
      }
    }
  };
  xhttp.open("GET", "/admin/api.php?listKlassen=true", true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}

function buildKlassen(data, klasList, config) {
  let html = '<table id="klasTable">\n';
  html += '<tr><th>Klas</th><th>Jaar</th><th>Niveau</th><th>Nummer</th><th>Delete</th></tr>\n';
  for (var i = 0; i < data.length; i++) {
    html += '<tr>\
    <td>' + data[i].jaar + data[i].niveau + data[i].nummer + '</td>\
    <td>' + data[i].jaar + '</td>\
    <td>' + data[i].niveau + '</td>\
    <td>' + data[i].nummer + '</td>\
    <td>\
    <div class="actions" data-klas=\'' + JSON.stringify(data[i]).replace(/\'/g, "&#39;") + '\'>\
    <img src="/img/closeBlack.svg" alt="remove" onclick="deleteKlas(this.parentElement.dataset.klas)">\
    </div>\
    </td>\
    </tr>\n';
  }
  klasList.innerHTML = html;
  sortTable(document.getElementById('klasTable'));
}

function deleteKlas(data) {
  load(true);
  if (!confirm("Wilt u deze klas verwijderen?")) {
    load(false);
    return null;
  }
  let ID = JSON.parse(data).ID;
  let url = '/admin/api.php?deleteKlas=true&ID=' + encodeURIComponent(ID);
  sendURL(url, function(response) {
    message(response);
    let klasList = document.getElementById('klasList');
    loadKlassen(klasList);
  });
}

function toggleLokalen(element) {
  load(true);
  let lokaalList = document.getElementById('lokaalList');
  if (element.dataset.toggle == 'hidden') {
    lokaalList.style.display = 'block';
    loadLokalen(lokaalList);
    element.dataset.toggle = 'shown';
    element.innerHTML = 'Hide';
  } else if (element.dataset.toggle == 'shown') {
    lokaalList.style.display = 'none';
    element.dataset.toggle = 'hidden';
    element.innerHTML = 'Show';
    load(false);
  } else {
    element.dataset.toggle = 'hidden';
  }
}

function loadLokalen(lokaalList) {
  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        //stop loading animatie
        let data = JSON.parse(this.responseText);
        buildLokalen(data, lokaalList);
        load(false);
      } catch (e) {
        //stop loading animatie
        load(false);
        errorMessage(e);
      }
    }
  };
  xhttp.open("GET", "/admin/api.php?listLokalen=true", true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}

function buildLokalen(data, lokaalList) {
  let html = '<table id="lokaalTable">\n';
  html += '<tr><th>Klas</th><th class="tableFilling">&nbsp;</th><th>Delete</th></tr>\n';
  for (var i = 0; i < data.length; i++) {
    html += '<tr>\
    <td>' + data[i].lokaal + '</td>\
    <td class="tableFilling">&nbsp;</td>\
    <td>\
    <div class="actions" data-lokaal=\'' + JSON.stringify(data[i]).replace(/\'/g, "&#39;") + '\'>\
    <img src="/img/closeBlack.svg" alt="remove" onclick="deleteLokaal(this.parentElement.dataset.lokaal)">\
    </div>\
    </td>\
    </tr>\n';
  }
  lokaalList.innerHTML = html;
  sortTable(document.getElementById('lokaalTable'));
}

function deleteLokaal(data) {
  load(true);
  if (!confirm("Wilt u dit lokaal verwijderen?")) {
    load(false);
    return null;
  }
  let ID = JSON.parse(data).ID;
  let url = '/admin/api.php?deleteLokaal=true&ID=' + encodeURIComponent(ID);
  sendURL(url, function(response) {
    message(response);
    let lokaalList = document.getElementById('lokaalList');
    loadLokalen(lokaalList);
  });
}

function sendURL(url, callback) {
  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      callback(this.responseText);
    }
  };
  xhttp.open("GET", url, true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}

function addUser(config) {
  load(true);
  //haal data uit textboxes
  let usernameObj = document.getElementById('adduserUsername');
  let passwordObj = document.getElementById('adduserPassword');
  let userLVLObj = document.getElementsByName('adduserUserLVL')[0];

  let username = usernameObj.value;
  let password = passwordObj.value;
  let userLVL = userLVLObj.value;

  //maak username en password velden leeg
  usernameObj.value = '';
  passwordObj.value = '';

  //check of een van de inputs leeg is
  if (username == '' || password == '' || userLVL == '') {
    load(false);
    message('Niet alle velden zijn ingevuld');
    return 0;
  }
  let POST = 'username=' + encodeURIComponent(username) +
    '&password=' + encodeURIComponent(password) +
    '&userLVL=' + encodeURIComponent(userLVL);
  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      load(false);
      message(this.responseText);
    }
  };
  xhttp.open("POST", '/admin/api.php?addUser=true', true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(POST);
}

function addKlas() {
  load(true);
  let jaarObj = document.getElementById('addklasJaar');
  let niveauObj = document.getElementById('addklasNiveau');
  let nummerObj = document.getElementById('addklasNummer');

  let jaar = jaarObj.value;
  let niveau = niveauObj.value;
  let nummer = nummerObj.value;

  jaarObj.value = '';
  niveauObj.value = '';
  nummerObj.value = '';

  //check of alle variables wel een value
  if (jaar == '' || niveau == '' || nummer == '') {
    load(false);
    message('Niet alle velden zijn ingevuld');
    return 0;
  }
  let POST = 'jaar=' + encodeURIComponent(jaar) +
    '&niveau=' + encodeURIComponent(niveau) +
    '&nummer=' + encodeURIComponent(nummer);


  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      load(false);
      message(this.responseText);
    }
  };
  xhttp.open("POST", '/admin/api.php?addKlas=true', true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(POST);
}

function addLokaal() {
  load(true);
  let lokaalObj = document.getElementById('addlokaalLokaal');
  let lokaal = lokaalObj.value;

  //check of er wel wat gezet is
  if (lokaal == '') {
    load(false);
    message('Niet alle velden zijn ingevuld');
    return 0;
  }

  lokaalObj.value = '';

  let POST = 'lokaal=' + encodeURIComponent(lokaal);
  let xhttp = new XMLHttpRequest();
  //stuur request met in body alle data
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      load(false);
      message(this.responseText);
    }
  };
  xhttp.open("POST", '/admin/api.php?addLokaal=true', true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(POST);
}

function deleteAll() {
  load(true)
  //popup modal om voor wachtwoord te vragen
  let html = "<p style=\"font-size:1.5em\">Wachtwoord</p>\
  <p>Vul uw wachtwoord in om de huidige planning leeg te maken</p>\
  <input type=\"password\" placeholder=\"password\" id=\"delPassword\" style=\"display:block;width:100%\">\
  <button onclick=\"deleteAllReq(document.getElementById('delPassword'))\" style=\"display:block;width:100%\">Verwijder</button>"
  load(false)
  message(html, false)
}

function deleteAllReq(pwd) {
  if (pwd.value == "") {

    return;
  }
  load(true)
  let postData = "password="+encodeURIComponent(pwd.value)
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      load(false);
      message(this.responseText);
    }
  }
  xhttp.open("POST", '/admin/api.php?deleteAll=true', true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(postData);
}

function addDocent() {
  load(true);
  let afkortingObj = document.getElementById('addDocentAfkorting');
  let afkorting = afkortingObj.value;

  let POST = "afkorting=" + encodeURIComponent(afkorting);

  // voeg dagdeel info aan POST toe
  for (var i = 0; i < config.dagen.length; i++) {
    if (document.getElementById('adduser' + config.dagen[i]).checked) {
      POST += '&dag' + config.dagen[i] + '=true';
    } else {
      POST += '&dag' + config.dagen[i] + '=false';
    }
  }
  //send data
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      load(false);
      message(this.responseText);
      //maak modal leeg
      afkortingObj.value = '';
    }
  };
  xhttp.open("POST", '/admin/api.php?addDocent=true', true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(POST);
}

function toggleDocenten(element) {
  load(true);
  let docentList = document.getElementById('docentList');
  if (element.dataset.toggle == 'hidden') {
    docentList.style.display = 'block';
    loadDocenten(docentList);
    element.dataset.toggle = 'shown';
    element.innerHTML = 'Hide';
  } else if (element.dataset.toggle == 'shown') {
    docentList.style.display = 'none';
    element.dataset.toggle = 'hidden';
    element.innerHTML = 'Show';
    load(false);
  } else {
    element.dataset.toggle = 'hidden';
  }
}

function loadDocenten(docentList) {
  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        //stop loading animatie
        let data = JSON.parse(this.responseText);
        buildDocenten(data, docentList, config);
      } catch (e) {
        //stop loading animatie
        load(false);
        errorMessage(e);
      }
    }
  };
  xhttp.open("GET", "/admin/api.php?listDocenten=true", true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}

function buildDocenten(data, docentList, config) {
  let html = '<table id="docentTable">\n';
  html += '<tr><th>Afkorting</th>';
  for (var i = 0; i < config.dagen.length; i++) {
    html += '<th>' + config.dagen[i] + '</th>';
  }
  html += '<th>Delete</th></tr>';
  for (var i = 0; i < data.length; i++) {
    let userAvailability = data[i].userAvailability;

    html += '<tr>\
    <td>' + data[i].afkorting + '</td>';
    for (var x = 0; x < config.dagen.length; x++) {
      if (
        typeof userAvailability[config.dagen[x]] !== undefined &&
        typeof userAvailability[config.dagen[x]] !== null &&
        userAvailability[config.dagen[x]]
      ) {
        html += '<td>&#10003;</td>';
      } else {
        html += '<td>&#10799;</td>';
      }
    }
    html += '<td>\
    <div class="actions" data-docent=\'' + JSON.stringify(data[i]).replace(/\'/g, "&#39;") + '\'>\
    <img src="/img/closeBlack.svg" alt="remove" onclick="deleteDocent(this.parentElement.dataset.docent, config)"></div>\
    </td>\
    </tr>\n';
  }
  html += '</table>\n';
  load(false);
  docentList.innerHTML = html;
  //sort table
  sortTable(document.getElementById('docentTable'));
}

function deleteDocent(data, config) {
  load(true);
  if (!confirm("Wilt u deze gebruiker verwijderen?")) {
    load(false);
    return null;
  }
  let ID = JSON.parse(data).ID;
  let url = '/admin/api.php?deleteDocent=true&ID=' + encodeURIComponent(ID);
  sendURL(url, function(response) {
    message(response);
    let docentList = document.getElementById('docentList');
    loadDocenten(docentList);
  });
}


function updateLaptops() {
  load(true);
  let laptops = document.getElementById("laptopInput").value;
  let url = "/admin/api.php?updateLaptops=true&laptops=" + encodeURIComponent(laptops);
  sendURL(url, function(response) {
    load(false);
    message(response);
  });
}

function addDay(days = 0) {
  days++;
  let dag = document.createElement('input');
  dag.type = "text";
  dag.id = "weekDag" + days;
  dag.placeholder = "Dag " + days;

  document.getElementById("weekDagen").appendChild(dag);
  return days;
}

function updateDays() {
  alert("UNFINISHED");
}
