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
    <link rel="stylesheet" href="/css/rooster.css">
    <!-- <link rel="stylesheet" href="/css/fork.css"> -->
    <!-- title -->
    <title>Rooster</title>
    <!-- CDN -->
    <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script> -->
  </head>
  <body>
    <nav>
      <img src="/img/logo.png" alt="Logo">
      <p>FOO</p>
    </nav>
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
      <p><a href="https://www.joerigeuzinge.nl/" target="_blank" rel="noreferrer">&copy; Joeri Geuzinge</a></p>
    </footer>
    <script src="/js/roosterMaster.js" charset="utf-8"></script>
    <script src="/js/rooster.js" charset="utf-8"></script>
  </body>
</html>
