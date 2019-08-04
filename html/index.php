<?php
//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
session_start();
if (!isset($_SESSION['loggedin'])) {
  header("location: /login");
  die();
}
 ?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<!-- BY JOERI GEUZINGE (https://www.joerigeuzinge.nl) -->
  <head>
    <meta charset="utf-8">
    <!-- icon -->
    <link rel="shortcut icon" href="/img/logo.png">
    <!-- css -->
    <link rel="stylesheet" href="/css/master.css">
    <link rel="stylesheet" href="/css/rooster.css">

    <link rel="stylesheet" href="/css/fullRooster.css">
    <link rel="stylesheet" href="/css/jaarlaagRooster.css">
    <!-- title -->
    <title>Rooster</title>
    <!-- CDN -->
    <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script> -->
  </head>
  <body>
    <nav>
      <img src="/img/logo.png" alt="Logo">
      <a href="/#" onclick="modeDefault()">Standaard Weergave</a>
      <a href="/#" onclick="modeFull()">Volledige Weergave</a>
      <a href="/#" onclick="modeJaarlaag()">Jaarlaag Weergave</a>
      <span onclick="menu(true)"><img src="/img/menu.svg" alt="menu"></span>
    </nav>
    <menu>
      <div class="items">
        <div class="top">
          <span>
            <img src="/img/logo.png" alt="Logo">
          </span>
          <span>
            <img src ="/img/close.svg" alt="Close" onclick="menu(false)" class="close">
          </span>
        </div>
        <span>
          <a href="/#" onclick="modeDefault()">Standaard Weergave</a>
        </span>
        <span>
          <a href="/#" onclick="modeFull()">Volledige Weergave</a>
        </span>
        <span>
          <a href="/#" onclick="modeJaarlaag()">Jaarlaag Weergave</a>
        </span>
      </div>
    </menu>
    <div class="select">
      <select name="displayMode" onchange="buildSelect(this.value);">
        <option value="klas" selected>Klassen</option>
      </select>
      <select name="displayModeFinal" onchange="setTimetable(this.value);">
        <option>Klas</option>
      </select>
    </div>
    <main style="display:block">
    </main>
    <!-- Loading svg -->
    <div id="loading">
      <div id="loadingContent">
        <img src="/img/loading.svg" alt="Loading...">
      </div>
    </div>
    <!-- Message modal -->
    <div id="messageModal">
      <div id="messageModalContent">
      </div>
    </div>
    <footer style="position:absolute">
      <span>
        <p>&copy; <a href="https://www.joerigeuzinge.nl/" target="_blank" rel="noreferrer">Joeri Geuzinge</a><br>Licensed under <a href="https://www.gnu.org/licenses/agpl-3.0.txt" target="_blank" rel="noreferrer">AGPL 3.0</a> </p>
        <p>
          Loading animation by <a href="http://samherbert.net/svg-loaders/" rel="noreferrer" target="_blank">Sam Herbert</a>
          <br>
          Menu and Close Icon based on <a href="https://www.flaticon.com/free-icon/menu-button-of-three-horizontal-lines_56763" rel="noreferrer" target="_blank">icon</a> by
          <a href="https://www.flaticon.com/authors/freepik" rel="noreferrer" target="_blank">Freepik</a> and <a href="https://www.flaticon.com/authors/chanut" rel="noreferrer" target="_blank">Chanut</a>.
          <br>Edited by <a href="https://www.joerigeuzinge.nl/" target="_blank" rel="noreferrer">Joeri Geuzinge</a>
          and licensed under <a href="https://creativecommons.org/licenses/by/3.0/" target="_blank" rel="noreferrer">CC 3.0 BY</a>
        </p>
      </span>
    </footer>
    <script src="/js/master.js" charset="utf-8"></script>

    <script src="/js/roosterMaster.js" charset="utf-8"></script>
    <script src="/js/rooster.js" charset="utf-8"></script>

    <script src="/js/roosterFullMaster.js" charset="utf-8"></script>
    <script src="/js/roosterFull.js" charset="utf-8"></script>

    <script src="/js/roosterJaarlaagMaster.js" charset="utf-8"></script>
    <script src="/js/roosterJaarlaag.js" charset="utf-8"></script>
  </body>
</html>
