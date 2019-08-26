<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
session_start();
//login check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['userLVL'] <= 3) {
    header("location: /login");
    die('Not Logged In');
}
?>
<!DOCTYPE html>
<html lang="nl" dir="ltr">
  <!-- BY JOERI GEUZINGE (https://www.joerigeuzinge.nl) -->
  <head>
    <meta charset="utf-8">
    <title>Admin Panel</title>
    <link rel="shortcut icon" href="/img/logo.svg">
    <link rel="stylesheet" href="/css/min/master.css">
    <link rel="stylesheet" href="/css/min/admin.css">
  </head>
  <body>
    <!-- Navbar -->
    <nav>
      <a href="/" target="_self" class="icon"><img src="/img/logo.svg" alt="Logo"></a>
      <a href="/">Home</a>
      <a href="/projecten">Projecten</a>
      <a href="/admin">Panel</a>
      <a href="/logout">Logout</a>
      <span onclick="menu(true)"><img src="/img/menu.svg" alt="menu"></span>
    </nav>
    <!-- Hamburger Menu -->
    <menu>
      <div class="items">
        <div class="top">
          <span><a href="#" class="icon"><img src="/img/logo.svg" alt="Logo"></a></span>
          <span><a href="#" class="icon"><img src ="/img/close.svg" alt="Close" onclick="menu(false)" class="close"></a></span>
        </div>
        <span><a href="/">Home</a></span>
        <span><a href="/projecten">Projecten</a></span>
        <span><a href="/admin">Panel</a></span>
        <span><a href="/logout">Logout</a></span>
      </div>
    </menu>
    <main>
      <!-- Add User -->
      <div>
        <p>Gebruiker Toevoegen</p>
        <p>Vul alle velden in om een gebruiker toe te voegen.</p>
        <div class="inputParent">
          <span class="col_2">
            <span><input type="text" placeholder="Username" id="adduserUsername"></span>
            <span><input type="password" placeholder="Password" id="adduserPassword"></span>
            <span><input type="text" placeholder="Role" id="adduserRole"></span>
            <span><input type="number" placeholder="UserLVL" id="adduserUserLVL"></span>
          </span>
          <span id="docentDagen"></span>
          <span class="buttonContaier">
            <button type="button" class="button" onclick="addUser(config)">Add</button>
          </span>
        </div>
      </div>
      <!-- listUsers -->
      <div>
        <p>Gebruikers</p>
        <p>Lijst met alle gebruikers</p>
        <div class="inputParent">
          <button type="button" class="button" data-toggle="hidden" onclick="toggleUsers(this, config)">Show</button>
          <div id="userList" class="list" style="display:none;"></div>
        </div>
      </div>
      <!-- Add Klas -->
      <div>
        <p>Klas Toevoegen</p>
        <p>Vul de velden in om een klas toe te voegen</p>
        <div class="inputParent">
          <span class="col_3">
            <span><input type="number" placeholder="Jaarlaag" id="addklasJaar"></span>
            <span><input type="text" placeholder="Niveau" id="addklasNiveau"></span>
            <span><input type="number" placeholder="Nummer" id="addklasNummer"></span>
          </span>
          <span class="buttonContaier">
            <button type="button" class="button" onclick="addKlas()">Add</button>
          </span>
        </div>
      </div>
      <!-- listKlassen -->
      <div>
        <p>Klassen</p>
        <p>Lijst met alle klassen</p>
        <div class="inputParent">
          <button type="button" class="button" data-toggle="hidden" onclick="toggleKlassen(this)">show</button>
          <div id="klasList" class="list" style="display:none;"></div>
        </div>
      </div>
      <!-- Add lokaal -->
      <div>
        <p>Lokaal Toevoegen</p>
        <p>Vul het veld in om een lokaal toe te voegen</p>
        <div class="inputParent">
          <span class="col_1">
            <input type="text" placeholder="Lokaal" id="addlokaalLokaal">
          </span>
          <span class="buttonContaier">
            <button type="button" onclick="addLokaal()" class="button">Add</button>
          </span>
        </div>
      </div>
      <!-- listLokaal -->
      <div>
        <p>Lokalen</p>
        <p>Lijst met alle lokalen</p>
        <div class="inputParent">
          <button type="button" class="button" data-toggle="hidden" onclick="toggleLokalen(this, config)">show</button>
          <div id="lokaalList" class="list" style="display:none;"></div>
        </div>
      </div>
      <!-- delete all -->
      <div>
        <p>Delete</p>
        <p>Delete alle afspraken in de database</p>
        <div class="inputParent">
          <button type="button" class="button" onclick="deleteAll()">Delete</button>
        </div>
      </div>
    </main>
    <!-- Loading svg -->
    <div id="loading">
      <div id="loadingContent"></div>
    </div>
    <!-- Message modal -->
    <div id="messageModal">
      <div id="messageModalContent"></div>
    </div>
    <!-- Footer -->
    <footer>
      <!-- Credits -->
      <span>
        <p>&copy; <a href="https://www.joerigeuzinge.nl/" target="_blank" rel="noreferrer">Joeri Geuzinge</a><br>Licensed under <a href="https://www.gnu.org/licenses/agpl-3.0.txt" target="_blank" rel="noreferrer">AGPL 3.0</a> </p>
        <p>
          Logo by <a href="https://www.youtube.com/channel/UC4XQZNE6g3aj2ZGuBITl6hQ" target="_blank" rel="noreferrer">Ties van Schaik</a>.<br>
          Loading animation by <a href="http://samherbert.net/svg-loaders/" rel="noreferrer" target="_blank">Sam Herbert</a> under <a href="https://mit-license.org/" target="_blank" rel="noreferrer">MIT license</a>
          <br>
          <!-- Icons -->
          Menu, save and Close icons based on icons by
          <a href="https://www.flaticon.com/authors/freepik" rel="noreferrer" target="_blank">Freepik</a> and <a href="https://www.flaticon.com/authors/chanut" rel="noreferrer" target="_blank">Chanut</a>.
          <br>Edited by <a href="https://www.joerigeuzinge.nl/" target="_blank" rel="noreferrer">Joeri Geuzinge</a>
          and licensed under <a href="https://creativecommons.org/licenses/by/3.0/" target="_blank" rel="noreferrer">CC 3.0 BY</a>
        </p>
      </span>
    </footer>
    <script src="/js/master.js" charset="utf-8"></script>
    <script type="text/javascript">
    load(true);
    let xhttp = new XMLHttpRequest();
    let config = {dagen:[], uren:0, roostertijden:[]};
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        try {
          //stop loading animatie
          config = JSON.parse(this.responseText);
          let docentDagen = document.getElementById('docentDagen');
          for (var i = 0; i < config.dagen.length; i++) {
            docentDagen.innerHTML += '<span>\
            <label for="adduser' + config.dagen[i] + '">' + config.dagen[i] + '</label>\
            <input type="checkbox" checked="checked" id="adduser' + config.dagen[i] + '">\
            </span>';
          }
          load(false);
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
    </script>
    <script src="/js/admin.js" charset="utf-8"></script>
  </body>
</html>
