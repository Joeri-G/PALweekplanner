<?php
//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
  header('location: /');
  die('Already logged in');
}
 ?>
<!DOCTYPE html>
<html lang="nl" dir="ltr">
  <!-- BY JOERI GEUZINGE (https://www.joerigeuzinge.nl) -->
  <head>
    <meta charset="utf-8">
    <!-- BY JOERI GEUZINGE (https://www.joerigeuzinge.nl) -->
    <!-- Title -->
    <title>Login</title>
    <!-- Icon -->
    <link rel="shortcut icon" href="/img/logo.svg">
    <!-- CSS -->
    <link rel="stylesheet" href="/css/master.css">
    <link rel="stylesheet" href="/css/login.css">
  </head>
  <body>
    <!-- Navbar -->
    <nav>
      <a href="/" target="_self" class="icon"><img src="/img/logo.svg" alt="Logo"></a>
      <span onclick="menu(true)"><img src="/img/menu.svg" alt="menu"></span>
    </nav>
    <!-- Hamburger Menu -->
    <menu>
      <div class="items">
        <div class="top">
          <span><a href="#" class="icon"><img src="/img/logo.svg" alt="Logo"></a></span>
          <span><a href="#" class="icon"><img src ="/img/close.svg" alt="Close" onclick="menu(false)" class="close"></a></span>
        </div>
        <span><a href="/" target="_self" rel="noreferrer">Home</a></span>
      </div>
    </menu>
    <main>
      <section>
        <p>Login</p>
        <input type="text" placeholder="Username" id="username"><br>
        <input type="password" placeholder="Password" id="password"><br>
        <button type="button" onclick="login()">Submit</button>
      </section>
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
    <script src="/js/login.js" charset="utf-8"></script>
  </body>
</html>
