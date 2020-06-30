<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
session_start();
//login check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['userLVL'] < 2) {
  if (!isset($_SESSION['userLVL']) || $_SESSION['userLVL'] < 2) {
    header("location: /");
    die("Insufficient Permissions");
  }
    header("location: /login");
    die('Not Logged In');
}
?>
<!DOCTYPE html>
<html lang="nl" dir="ltr">
  <!-- BY JOERI GEUZINGE (https://www.joerigeuzinge.nl) -->
  <head>
    <meta charset="utf-8">
    <title>Planner | Admin</title>
    <link rel="shortcut icon" href="/img/logo.svg">
    <link rel="stylesheet" href="/css/min/master.css">
    <link rel="stylesheet" href="/css/min/panel.css">
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
          <span class="col_3">
            <span><input type="text" placeholder="Username" id="adduserUsername"></span>
            <span><input type="password" placeholder="Password" id="adduserPassword"></span>
            <span id="userPerm"></span>
          </span>
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
      <!-- Add Docent -->
      <div>
        <p>Docent Toevoegen</p>
        <p>Vul alle velden in om een docent toe te voegen</p>
        <div class="inputParent">
          <span class="col_1">
            <input type="text" id="addDocentAfkorting" placeholder="Afkorting">
          </span>
          <span id="docentDagen"></span>
          <span class="buttonContaier">
            <button type="button" onclick="addDocent()" class="button">Add</button>
          </span>
        </div>
      </div>
      <!-- listDocent -->
      <div>
        <p>Docenten</p>
        <p>Lijst met alle docenten</p>
        <div class="inputParent">
          <button type="button" class="button" data-toggle="hidden" onclick="toggleDocenten(this, config)">Show</button>
          <div id="docentList" class="list" style="display:none;"></div>
        </div>
      </div>
      <!-- Add Klas -->
      <div>
        <p>Klas Toevoegen</p>
        <p>Vul de velden in om een klas toe te voegen</p>
        <div class="inputParent">
          <span class="col_2">
            <span><input type="number" placeholder="Jaarlaag" id="addKlasJaar"></span>
            <span><input type="text" placeholder="Volledige Naam (bv. 1H1)" id="addKlasNaam"></span>
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
      <!-- Laptops -->
      <div>
        <p>Laptops</p>
        <p>Vul hier het totaal aantal laptops in</p>
        <div class="inputParent">
          <input type="number" id="laptopInput" placeholder="Laptops">
          <button type="button" class="button" onclick="updateLaptops()">Update</button>
        </div>
      </div>
      <!-- dagen -->
      <div>
        <p>Dagen</p>
        <p>Gebruik dit panel om de dagen in een week aan te passen. Een dag is max twee (2) karakters</p>
        <p>WAARSCHUWING: Dit kan bestaande afspraken in de war schoppen</p>
        <div class="inputParent">
          <span id="weekDagen"></span>
          <button type="button" class="button" onclick="weekDagen = addDay(weekDagen)">+</button>
          <button type="button" class="button" onclick="updateDays()">Update</button>
        </div>
      </div>
      <!-- phpMyAdmin -->
      <div>
        <p>Database Control Panel</p>
        <p>Control panel om database beter te kunnen besturen. Powered By <a href="https://www.phpmyadmin.net/" target="_blank" rel="noreferrer">phpMyAdmin</a></p>
        <div class="inputParent">
          <?php // NOTE: URL CAN DIFFER DEPENDING ON SETUP?>
          <a href="/phpmyadmin" target="_blank"><button type="button" class="button">Panel</button></a>
        </div>
      </div>
      <!-- export -->
      <div>
        <p>Export</p>
        <p>Export naar *.CSV</p>
        <div class="inputParent">
          <a href="/api.php?export=true" target="_blank"><button type="button" class="button" name="button">Export</button></a>
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
    <div id="messageModal" class="messageModal">
      <div id="messageModalContent" class="messageModalContent"></div>
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
    <script src="/js/admin.js" charset="utf-8"></script>
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
            //insert number in laptops box
            document.getElementById('laptopInput').value = config.laptops;
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
    xhttp.open("GET", "/admin/api.php?loadConf=true", true);
    xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
    xhttp.send();
    document.getElementById("userPerm").innerHTML = buildDropdown(["Read", "Read/Write", "Admin"], ['0', '1', '2'], "Rechten", "adduserUserLVL");
    let weekDagen = addDay(0);
    </script>
  </body>
</html>
