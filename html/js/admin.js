//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
  - loadUsers()
    * functie om te text van de show/hide button aan te passen en de table te laden
  - loadUsers()
    * functie om lijst met alle gebruikers te laden en edit functies toe te voegen
  - buildUserList()
    * functie om de user table te 'bouwen' uit de JSON
  - saveUser()
    * komt nog
  - deleteUser()
    * komt nog

*/
function toggleUsers(element) {
  load(true);
  let userList = document.getElementById('userList');
  if (element.dataset.toggle == 'hidden') {
    userList.style.display = 'block';
    loadUsers(userList);
    element.dataset.toggle = 'shown';
    element.innerHTML = 'Hide';
  }
  else if (element.dataset.toggle == 'shown') {
    userList.style.display = 'none';
    element.dataset.toggle = 'hidden';
    element.innerHTML = 'Show';
    load(false);
  }
}


function loadUsers(userList) {
  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        //stop loading animatie
        let data = JSON.parse(this.responseText);
        buildUserList(data, userList);
      }
      catch (e) {
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

function buildUserList(data, userList) {
  let xhttp = new XMLHttpRequest();
  //laad list met config docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        config = JSON.parse(this.responseText);
        let html = '<table id="userTable">\n';
        html += '<tr><th>Username</th><th>Password</th><th>Role</th><th>UserLVL</th>';
        for (var i = 0; i < config.dagen.length; i++) {
          html += '<th>' + config.dagen[i] + '</th>';
        }
        html += '<th>Action</th></tr>';
        for (var i = 0; i < data.length; i++) {
          html += '<tr>\n';
          html += '<td>' + data[i].username + '</td>\n';
          html += '<td><input type="password" placeholder="password" name="' + data[i].username + 'password"></td>\n';
          html += '<td><input type="text" placeholder="role" value="' + data[i].role + '" name="' + data[i].username + 'role"></td>\n';
          html += '<td><input type="number" placeholder="userLVL" value="' + data[i].userLVL + '" name="' + data[i].username + 'userLVL"></td>\n';
          for (var x = 0; x < config.dagen.length; x++) {
            html += '<td><input type="checkbox" checked="checked" name="' + data[i].userame + 'userAvailability' + config.dagen[x] + '">\n</td>\n';
          }
          html += '<td><div class="centerContent actions" data-user=\'' + JSON.stringify(data[i]) + '\'><img src="/img/save.svg" alt="save" onclick="saveUser(this.parentElement.dataset.user)"><img src="/img/closeBlack.svg" alt="remove" onclick="deleteUser(this.parentElement.dataset.hour)"></div></td>\n';
          html += '</tr>\n';
        }
        html += '</table>\n';
        load(false);
        userList.innerHTML = html;
        //sort table
        sortTable(document.getElementById('userTable'));
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

function saveUser(data) {
  try {
    let user = JSON.parse(data).username;
  }
  catch (e) {
    errorMessage(e);
    return null;
  }
  console.log(data);
}

function deleteUser(data) {
  try {
    let ID = JSON.parse(data).ID;
  }
  catch (e) {
    errorMessage(e);
    return null;
  }
}

function toggleKlassen(element) {
  load(true);
  let klasList = document.getElementById('klasList');
  if (element.dataset.toggle == 'hidden') {
    klasList.style.display = 'block';
    loadKlas(klasList);
    element.dataset.toggle = 'shown';
    element.innerHTML = 'Hide';
  }
  else if (element.dataset.toggle == 'shown') {
    klasList.style.display = 'none';
    element.dataset.toggle = 'hidden';
    element.innerHTML = 'Show';
    load(false);
  }
  else {
    element.dataset.toggle = 'hidden';
  }
}

function loadKlas(klasList) {
  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        //stop loading animatie
        let data = JSON.parse(this.responseText);
        buildKlasList(data, klasList);
        load(false);
      }
      catch (e) {
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

function buildKlasList(data, klasList) {
  let html = '<table id="klasTable">\n';
  html += '<tr><th>Klas</th><th>Jaar</th><th>Niveau</th><th>Nummer</th><th>Action</th></tr>\n';
  for (var i = 0; i < data.length; i++) {
    html += '<tr>\
    <td>' + data[i].jaar + data[i].niveau + data[i].nummer + '</td>\
    <td><input type="number" name="klas' + data[i].ID + 'jaar" value="' + data[i].jaar + '" placeholder="Jaar"></td>\
    <td><input type="text" name="klas' + data[i].ID + 'niveau" value="' + data[i].niveau + '" placeholder="Niveau"></td>\
    <td><input type="number" name="klas' + data[i].ID + 'nummer" value="' + data[i].nummer + '" placeholder="Nummer"></td>\
    <td><div class="centerContent actions" data-user=\'' + JSON.stringify(data[i]) + '\'><img src="/img/save.svg" alt="save" onclick="saveKlas(this.parentElement.dataset.user)"><img src="/img/closeBlack.svg" alt="remove" onclick="deleteKlas(this.parentElement.dataset.hour)"></div></td>\
    </tr>\n';
    data[i]
  }
  klasList.innerHTML = html;
}
