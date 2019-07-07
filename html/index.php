<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
  header("location: /login");
  die();
}
 ?>
 <!DOCTYPE html>
 <html lang="en" dir="ltr">
   <head>
     <meta charset="utf-8">
     <!-- icon -->
     <link rel="shortcut icon" href="/img/logo.png">
     <!-- css -->
     <link rel="stylesheet" href="/css/master.css">
     <link rel="stylesheet" media="(min-width: 992px)" href="/css/desktop.css">
     <link rel="stylesheet" media="(max-width: 991px)" href="/css/mobile.css">
     <!-- <link rel="stylesheet" href="/css/fork.css"> -->
     <!-- title -->
     <title>Rooster</title>
     <!-- CDN -->
     <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
     <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script> -->
   </head>
   <body>
     <!-- navbar -->
     <nav>
       <a href="/" target="_self" rel="noreferrer"><img src="/img/logo.png" alt="Logo"></a>
       <p>Welcome <?php echo htmlentities($_SESSION['username']); ?></p>
       <p><a href="#" target="_self" rel="noreferrer">Home</a></p>
       <p><a href="#" target="_self" rel="noreferrer">Settings</a></p>
       <p><a href="#" target="_self" rel="noreferrer">Logout</a></p>
     </nav>
     <!-- select timetable mode -->
     <div class="select">
       <select name="displayMode" onchange="buildSelect(this.value, dataVar);">
         <option value="klas">Klassen</option>
         <option value="docent">Docenten</option>
       </select>
       <select name="displayModeFinal" onchange="setTimetable();">
         <option value="">Klassen</option>
       </select>
     </div>
     <!-- timetable -->
     <main>
     </main>
     <!-- Loading svg -->
     <div id="loading">
       <div id="loadingContent">
         <img src="/img/loading.svg" alt="Loading...">
       </div>
     </div>
     <div id="messageModal">
       <div id="messageModalContent">
         <p>TEST</p>
       </div>
     </div>
     <!-- foter -->
     <footer>
       <p><a href='https://www.joerigeuzinge.nl/' target='_blank' rel="noreferrer">&copy; Joeri Geuzinge</a></p>
     </footer>
     <!-- Scripts -->
     <script src="/js/master.js" charset="utf-8"></script>
     <script type="text/javascript">
     getOptions();

     var tijden = [
       "8.30 - 9.15",
       "9.15 - 10.00",
       "10.20 - 11.05",
       "11.05 - 11.50",
       "12.15 - 13.00",
       "13.00 - 13.45",
       "14.05 - 14.50",
       "14.50 - 15.35",
       "15.35 - 16.20"
     ];
     var dagen = [
       "MA",
       "DI",
       "WO",
       "DO",
       "VR"
     ];



     //message modal
     // When the user clicks anywhere outside of the modal, close it
     // Get the modal
      let modalBox = document.getElementById("messageModal");
      window.onclick = function(event) {
        if (event.target == modalBox) {
          let messageModal = document.getElementById('messageModal');
          let messageModalContent = document.getElementById('messageModalContent');
          //fade out
          messageModalContent.setAttribute('class', 'fade-out');
          //remove fadeout
          setTimeout(function() {
            messageModal.style.display = "none";
            messageModal.setAttribute('class', '');
          }, 200);
        }
      }
     </script>
   </body>
 </html>
