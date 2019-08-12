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
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Admin Panel</title>
    <link rel="shortcut icon" href="/img/logo.svg">
    <link rel="stylesheet" href="/css/master.css">
    <link rel="stylesheet" href="/css/admin.css">
  </head>
  <body>
    <!-- Navbar -->
    <nav>
      <a href="/" target="_self" class="icon"><img src="/img/logo.svg" alt="Logo"></a>
      <a href="/">Home</a>
      <?php
      //als de gebruiker een admin is geef dan de admin link weer
      if ($_SESSION['userLVL'] > 3) {
        echo '<a href="/admin">Panel</a>';
      }
       ?>
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
        <?php
        //als de gebruiker een admin is geef dan de admin link weer
        if ($_SESSION['userLVL'] > 3) {
          echo '<span><a href="/admin">Panel</a></span>';
        }
         ?>
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
            <span><input type="text" placeholder="Username" id="username"></span>
            <span><input type="password" placeholder="Password" id="password"></span>
            <span><input type="text" placeholder="Role" id="role"></span>
            <span><input type="number" placeholder="UserLVL" id="userLVL"></span>
          </span>

          <span id="docentDagen">
            <span><label for="adduserMA">MA</label> <input type="checkbox" checked="checked" id="adduserMA"></span>
            <span><label for="adduserDI">DI</label> <input type="checkbox" checked="checked" id="adduserDI"></span>
            <span><label for="adduserWO">WO</label> <input type="checkbox" checked="checked" id="adduserWO"></span>
            <span><label for="adduserDO">DO</label> <input type="checkbox" checked="checked" id="adduserDO"></span>
            <span><label for="adduserVR">VR</label> <input type="checkbox" checked="checked" id="adduserVR"></span>
          </span>
          <span class="buttonContaier">
            <button type="button" class="button">Add</button>
          </span>
        </div>
      </div>
      <!-- listUsers -->
      <div>
        <p>Gebruikers</p>
        <p>Lijst met alle gebruikers</p>
        <div class="inputParent">
          <button type="button" class="button" data-toggle="hidden" onclick="toggleUsers(this)">Show</button>
          <div id="userList" class="list" style="display:none;"></div>
        </div>
      </div>
      <!-- Add Klas -->
      <div>
        <p>Klas Toevoegen</p>
        <p>Vul de velden in om een klas toe te voegen</p>
        <div class="inputParent">
          <span class="col_3">
            <span><input type="number" placeholder="Jaarlaag" name="jaarlaag"></span>
            <span><input type="text" placeholder="Niveau" name="niveau"></span>
            <span><input type="number" placeholder="Nummer" name="nummer"></span>
          </span>
          <span class="buttonContaier">
            <button type="button" class="button">Add</button>
          </span>
        </div>
      </div>
      <!-- listKlassen -->
      <div>
        <p>Klassen</p>
        <p>Lijst met alle klassen</p>
        <button type="button" class="button" data-toggle="hide" onclick="toggleKlassen(this)">show</button>
        <div id="klasList" class="list" style="display:none;"></div>
      </div>
      <!-- Add lokaal -->
      <div>
        <p>Lokaal Toevoegen</p>
        <p>Vul het veld in om een lokaal toe te voegen</p>
        <div class="inputParent">
          <span class="col_1">
            <input type="text" placeholder="Lokaal">
          </span>
          <span class="buttonContaier">
            <button type="button" name="button" class="button">Add</button>
          </span>
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
    <script src="/js/admin.js" charset="utf-8"></script>
  </body>
</html>
