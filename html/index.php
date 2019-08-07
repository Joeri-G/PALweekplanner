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
    <!-- title -->
    <title>Rooster</title>
    <!-- icon -->
    <link rel="shortcut icon" href="/img/logo.svg">
    <!-- css -->
    <link rel="stylesheet" href="/css/master.css">
    <link rel="stylesheet" href="/css/gridRooster.css">
    <link rel="stylesheet" href="/css/weekRooster.css">
  </head>
  <body>
    <!-- Navbar -->
    <nav>
      <a href="/" target="_self" class="icon"><img src="/img/logo.svg" alt="Logo"></a>
      <a href="#" onclick="modeDefault()">Standaard Weergave</a>
      <a href="#" onclick="modeFull()">Volledige Weergave</a>
      <a href="#" onclick="modeJaarlaag()">Jaarlaag Weergave</a>
      <span onclick="menu(true)"><img src="/img/menu.svg" alt="menu"></span>
    </nav>
    <!-- Hamburger Menu -->
    <menu>
      <div class="items">
        <div class="top">
          <span><a href="#" class="icon"><img src="/img/logo.svg" alt="Logo"></a></span>
          <span><a href="#" class="icon"><img src ="/img/close.svg" alt="Close" onclick="menu(false)" class="close"></a></span>
        </div>
        <span><a href="/#" onclick="menu(false);modeDefault()">Standaard Weergave</a></span>
        <span><a href="/#" onclick="menu(false);modeFull()">Volledige Weergave</a></span>
        <span><a href="/#" onclick="menu(false);modeJaarlaag()">Jaarlaag Weergave</a></span>
      </div>
    </menu>
    <!-- Noscript tags voor als een oma een keer met Internet Explorer 6 de pagina probeert te laden -->
    <noscript><p style="font-size:48px">Please enable JavaScript or switch to a browser that supports it.</p></noscript>
    <!-- Selection object -->
    <div class="select"></div>
    <!-- Main document -->
    <main style="display:block"></main>
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
    <!-- Footer -->
    <!-- <footer style="position:absolute"> -->
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
    <!-- Scripts -->
    <script src="/js/master.js" charset="utf-8"></script>
    <script src="/js/weekRooster.js" charset="utf-8"></script>
    <script src="/js/gridRooster.js" charset="utf-8"></script>
    <script src="/js/roosterFunctions.js" charset="utf-8"></script>
    <script type="text/javascript">
      modeDefault();
    </script>
  </body>
</html>
