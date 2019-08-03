<?php
//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
session_start();
if (!isset($_SESSION['loggedin'])) {
  header("location: /login");
  die();
}
/*
0 = klas/docent
1 = full
2 = jaarlaag
*/
$mode = 0;

$modes = array('default', 'full', 'jaarlaag');

if (isset($_GET['mode']) && in_array($_GET['mode'], $modes)) {
  $mode = array_search($_GET['mode'], $modes);
}


if (isset($_GET['full']) && $_GET['full'] == 'true') {
  $mode = 1;
}

if (isset($_GET['jaarlaag']) && $_GET['jaarlaag'] == 'true') {
  $mode = 2;
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
<?php
//css style sheets depend on mode
if ($mode == 0):?>
    <link rel="stylesheet" href="/css/rooster.css">
<?php elseif ($mode == 1):?>
    <link rel="stylesheet" href="/css/fullRooster.css">
<?php elseif ($mode == 2): ?>
    <link rel="stylesheet" href="/css/jaarlaagRooster.css">
<?php endif;?>
    <!-- title -->
    <title>Rooster</title>
    <!-- CDN -->
    <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script> -->
  </head>
  <body>
    <nav>
      <img src="/img/logo.png" alt="Logo">
      <a href="/?mode=default" target="_self" rel="noreferrer">Standaard Weergave</a>
      <a href="/?mode=full" target="_self" rel="noreferrer">Volledige Weergave</a>
      <a href="/?mode=jaarlaag" target="_self" rel="noreferrer">Jaarlaag Weergave</a>
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
          <a href="/?mode=default" target="_self" rel="noreferrer">Standaard Weergave</a>
        </span>
        <span>
          <a href="/?mode=full" target="_self" rel="noreferrer">Volledige Weergave</a>
        </span>
        <span>
          <a href="/?mode=jaarlaag" target="_self" rel="noreferrer">Jaarlaag Weergave</a>
        </span>
      </div>
    </menu>
<?php if ($mode == 0): ?>
    <div class="select">
      <select name="displayMode" onchange="buildSelect(this.value);">
        <option value="klas" selected>Klassen</option>
      </select>
      <select name="displayModeFinal" onchange="setTimetable(this.value);">
        <option>Klas</option>
      </select>
    </div>
    <main style="display:block">
      <p style="font-size:1.5em;text-align:center">Selecteer een docent of klas met de dropdown</p>
    </main>
<?php else: ?>
    <main></main>
<?php endif ?>
    <!-- Loading svg -->
    <div id="loading">
      <div id="loadingContent">
        <img src="/img/loading.svg" alt="Loading...">
      </div>
    </div>
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
          Menu Icon based on <a href="https://www.flaticon.com/free-icon/menu-button-of-three-horizontal-lines_56763" rel="noreferrer" target="_blank">icon</a> by
          <a href="https://www.flaticon.com/authors/freepik" rel="noreferrer" target="_blank">Freepik</a>
          and edited by <a href="https://www.joerigeuzinge.nl/" target="_blank" rel="noreferrer">Joeri Geuzinge</a><br>
          Licensed under <a href="https://creativecommons.org/licenses/by/3.0/" target="_blank" rel="noreferrer">CC 3.0 BY</a>
        </p>
      </span>
    </footer>
<?php if ($mode == 0): ?>
    <script src="/js/roosterMaster.js" charset="utf-8"></script>
    <script src="/js/rooster.js" charset="utf-8"></script>
<?php elseif ($mode == 1): ?>
    <script src="/js/roosterFullMaster.js" charset="utf-8"></script>
    <script src="/js/roosterFull.js" charset="utf-8"></script>
<?php elseif ($mode == 2): ?>
    <script src="/js/roosterJaarlaagMaster.js" charset="utf-8"></script>
    <script src="/js/roosterJaarlaag.js" charset="utf-8"></script>
<?php endif; ?>
    <script type="text/javascript">
      function menu(bool) {
        if (bool) {
          let menu = document.getElementsByTagName('menu')[0].style.display = 'block';
        }
        else if (!bool) {
          let menu = document.getElementsByTagName('menu')[0].style.display = 'none';
        }
      }
    </script>
  </body>
</html>
