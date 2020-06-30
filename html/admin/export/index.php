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
      <div>
        <p>Export</p>
        <p>Export in SOMtoday format</p>
        <form class="inputParent" action="/admin/api.php?somtodayExport=true" method="get">
          <span id="exportSettings"></span>
          <input type="submit" value="Export">
        </form>
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
    <script src="/js/master.js" charset="utf-8"></script>
    <script type="text/javascript">
    function dagSettings(dag, offset) {
      let parent = document.createElement("div")
      parent.classList = "col_2"
      let text = document.createElement("p")
      text.innerHTML = "Datum dag " + offset.toString() + " [" + dag  + "]"
      let dateIn = document.createElement("input")

      dateIn.name = dag
      dateIn.type = "date"
      dateIn.required = true
      dateIn.placeholder = "Datum dag " + offset.toString()

      parent.appendChild(text)
      parent.appendChild(dateIn)
      return parent
    }

    load(true)
    let config = {dagen: []}
    sendReq('/admin/api.php?loadConf=true', function(response) {
      let html, dag, input
      config = JSON.parse(response)
      //maak voor iedere dag een setup box
      let exportSettings = document.getElementById("exportSettings")
      for (var i = 0; i < config.dagen.length; i++) {
        dag = config.dagen[i]
        input = dagSettings(dag, i)
        exportSettings.appendChild(input)
      }
      load(false)
    })
    </script>
  </body>
</html>
