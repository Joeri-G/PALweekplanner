<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
session_start();
//login check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: /login");
    die('Not Logged In');
}
?>
<!DOCTYPE html>
<html lang="nl" dir="ltr">
  <!-- BY JOERI GEUZINGE (https://www.joerigeuzinge.nl) -->
  <head>
    <meta charset="utf-8">
    <title>Planner | Projects</title>
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
      <?php
      //als de gebruiker een admin is geef dan de admin link weer
      if ($_SESSION['userLVL'] >= 2) {
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
        <span><a href="/projecten">Projecten</a></span>
        <?php
        //als de gebruiker een admin is geef dan de admin link weer
        if ($_SESSION['userLVL'] >= 2) {
            echo '<span><a href="/admin">Panel</a></span>';
        }
         ?>
        <span><a href="/logout">Logout</a></span>
      </div>
    </menu>
    <!-- Noscript tags voor als een oma een keer met Internet Explorer 6 de pagina probeert te laden -->
    <noscript><p style="font-size:48px">Please enable JavaScript or switch to a browser that supports it.</p></noscript>
    <main>
      <div>
        <p>Voeg Project Toe</p>
        <p>Vul alle velden in om een project toe te voegen</p>
        <div class="inputParent">
          <span class="col_3">
            <span><input type="text" placeholder="Titel" id="projectTitel"></span>
            <span><input type="text" placeholder="Afkorting" id="projectAfkorting"></span>
            <span id="projectLeider">
            </span>
          </span>
          <span class="col_1">
            <span><textarea placeholder="Beschrijving van project" id="projectBeschrijving"></textarea></span>
            <span><textarea placeholder="Instructies voor leerlingen" id="projectInstructie"></textarea></span>
          </span>
          <span class="buttonContaier">
            <button type="button" class="button" onclick="addProject()">Add</button>
          </span>
        </div>
      </div>
      <div>
        <p>Projecten</p>
        <p>Lijst met alle projecten</p>
        <div class="inputParent">
          <button type="button" class="button" data-toggle="hidden" onclick="toggleProjecten(this)">show</button>
          <div id="projectList" class="list" style="display:none;"></div>
        </div>
      </div>
      <div>
        <p>Export</p>
        <p>Exporteer projecten naar CSV</p>
        <div class="inputParent">
          <a href="/api.php?exportProjects=true" target="_blank">
            <button type="button" class="button" name="button">Export</button>
          </a>
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
    <!-- Edit modal -->
    <div id="editModal" class="messageModal">
      <div id="editModalContent" class="messageModalContent">
      </div>
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
    <script src="/js/projecten.js" charset="utf-8"></script>
  </body>
</html>
