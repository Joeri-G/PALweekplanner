<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
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
   <title>TEST PAGE</title>
   <style media="screen">
   html, body {
     font-family: 'Barlow Semi Condensed',sans-serif;
     margin: 0;
   }
     main {
       max-width: 980px;
       margin: auto;
     }
    * {
     box-sizing: border-box;
    }
    table {
      font-family: arial, sans-serif;
      border-collapse: collapse;
      width: 100%;
    }
    .tables {
      margin: 0 64px;
    }
    td, th {
      border: 1px solid #dddddd;
      text-align: left;
      padding: 8px;
    }

    tr:nth-child(even) {
      background-color: #dddddd;
    }
    footer {
      height: 100px;
      margin-top: 32px;
      display: flex;
      justify-content: center;
      align-items: center;
      background: #dddddd;
    }
    a {
      color: inherit;
    }
    a:hover {
      color: darkgrey;
    }
    input, select {
      border-radius: 0;
      border: 1px #dddddd solid;
      margin: 1px;
      padding: 4px;
    }
    p.big {
      font-size: 1.3em;
    }
     </style>
   </head>
   <body>
     <main>
       <form action="/api.php" method="get">
         <p class="big">INSERT</p>
         <p>Voeg hier items toe aan het rooster.<br>
           Voer bij docent, klas en lokaal steeds minstens veld in (bij klas alle velden met klas1 of klas2), selecteer een dagdeel (MA-VR, 0-4) en klik op INSERT</p>
         <input type="hidden" name="insert" value="true">
         <input type="text" name="daypart" placeholder="dagdeel">
         <input type="text" name="lokaal1" placeholder="lokaal1">
         <input type="text" name="lokaal2" placeholder="lokaal2">
         <input type="text" name="klas1jaar" placeholder="klas1jaar">
         <input type="text" name="klas1niveau" placeholder="klas1niveau">
         <input type="text" name="klas1nummer" placeholder="klas1nummer">
         <input type="text" name="klas2jaar" placeholder="klas2jaar">
         <input type="text" name="klas2niveau" placeholder="klas2niveau">
         <input type="text" name="klas2nummer" placeholder="klas2nummer">
         <input type="text" name="docent1" placeholder="docent1">
         <input type="text" name="docent2" placeholder="docent2">
         <input type="text" name="laptops" placeholder="laptops">
         <input type="text" name="projectCode" placeholder="projectCode">
         <input type="text" name="note" placeholder="note">
         <input type="submit" value="INSERT">

       </form>
       <form action="/api.php" method="get">
         <p class="big">READ</p>
         <p>Lees hier het rooster van een docent of klas.<br>Selecteer in de dropdown klas of docent, voer in de input de naam van de klas/docent in en klik op READ</p>
         <input type="hidden" name="read" value="true">
         <select name="mode">
           <option value="docent">docent</option>
           <option value="klas">klas</option>
         </select>
         <input type="text" name="selector" placeholder="klas/docent">
         <input type="submit" value="READ">

       </form>
       <form action="/api.php" method="get">
         <p class="big">DELETE</p>
         <p>Gebruik dit om afspraken te verwijderen.<br>Ze worden (nog) niet definitief verwijdert, in plaats daar van worden ze naar een aparte table verplaatst.</p>
         <input type="hidden" name="delete" value="true">
         <input type="text" name="ID" placeholder="ID">
         <input type="submit" value="DELETE">

       </form>
     </main>
     <div class="tables">
       <p class="big">PLANNER</p>
       <p>De rooster informatie</p>
     <table>
       <tr>
         <th>Daypart</th>
         <th>Docent1</th>
         <th>Docent2</th>
         <th>Klas1jaar</th>
         <th>Klas1niveau</th>
         <th>Klas1nummer</th>
         <th>Klas2jaar</th>
         <th>Klas2niveau</th>
         <th>Klas2nummer</th>
         <th>Lokaal1</th>
         <th>Lokaal2</th>
         <th>Laptops</th>
         <th>ProjectCode</th>
         <th>Notes</th>
         <th>ID</th>
       </tr>
       <?php
       //echo alle database info
       require('../php/db-connect.php');
       $stmt = $conn->prepare('SELECT
           daypart,
           docent1,
           docent2,
           klas1jaar,
           klas1niveau,
           klas1nummer,
           klas2jaar,
           klas2niveau,
           klas2nummer,
           lokaal1,
           lokaal2,
           laptops,
           projectCode,
           notes,
           ID
         FROM
           week
      ');
      $stmt->execute();
      $stmt->store_result();
      //res voor result
      $stmt->bind_result(
        $resDaypart,
        $resDocent1,
        $resDocent2,
        $resKlas1jaar,
        $resKlas1niveau,
        $resKlas1nummer,
        $resKlas2jaar,
        $resKlas2niveau,
        $resKlas2nummer,
        $resLokaal1,
        $resLokaal2,
        $resLaptop,
        $resProjectCode,
        $resNote,
        $resID
      );
      while ($stmt->fetch()) {
        echo "<tr>
        <td>$resDaypart</td>
        <td>$resDocent1</td>
        <td>$resDocent2</td>
        <td>$resKlas1jaar</td>
        <td>$resKlas1niveau</td>
        <td>$resKlas1nummer</td>
        <td>$resKlas2jaar</td>
        <td>$resKlas2niveau</td>
        <td>$resKlas2nummer</td>
        <td>$resLokaal1</td>
        <td>$resLokaal2</td>
        <td>$resLaptop</td>
        <td>$resProjectCode</td>
        <td>$resNote</td>
        <td>$resID</td>
        </tr>";
      }
      $stmt->close();
        ?>
      </table>

      <p class="big">USERS</p>
      <p>Alle gebruikers (docenten, admins, etc)</p>
      <table>
        <tr>
          <th>Username</th>
          <th>Password</th>
          <th>Role</th>
          <th>UserLVL</th>
          <th>UserAvailability</th>
          <th>LastLoginIP</th>
          <th>ID</th>
        </tr>
        <?php
        $stmt = $conn->prepare('SELECT username, role, userLVL, userAvailability, lastLoginIP, ID FROM users');
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($resUsername, $resRole, $resUserLVL, $resUserAvailability, $resLastLoginIP, $resID);
        while ($stmt->fetch()) {
          echo "<tr>
          <td>$resUsername</td>
          <td>***</td>
          <td>$resRole</td>
          <td>$resUserLVL</td>
          <td>$resUserAvailability</td>
          <td>$resLastLoginIP</td>
          <td>$resID</td>
          </tr>";
        }
        $stmt->close();
         ?>
       </table>

       <p class="big">KLASSEN</p>
       <p>Ale klassen</p>
       <table>
         <tr>
           <th>Jaar</th>
           <th>Niveau</th>
           <th>Nummer</th>
           <th>Created</th>
           <th>ID</th>
         </tr>
         <?php
         $stmt = $conn->prepare('SELECT jaar, niveau, nummer, created, ID FROM klassen');
         $stmt->execute();
         $stmt->store_result();
         $stmt->bind_result($resJaar, $resNiveau, $resNummer, $resCreated, $resID);
         while ($stmt->fetch()) {
           echo "<tr>
           <td>$resJaar</td>
           <td>$resNiveau</td>
           <td>$resNummer</td>
           <td>$resCreated</td>
           <td>$resID</td>
           </tr>";
         }
         $stmt->close();
          ?>
       </table>

       <p class="big">LOKALEN</p>
       <p>Alle lokalen</p>
       <table>
         <tr>
           <th>Lokaal</th>
           <th>Created</th>
           <th>ID</th>
         </tr>
           <?php
           $stmt = $conn->prepare('SELECT lokaal, created, ID FROM lokalen');
           $stmt->execute();
           $stmt->store_result();
           $stmt->bind_result($resLokaal, $resCreated, $resID);
           while ($stmt->fetch()) {
             echo "<tr>
             <td>$resLokaal</td>
             <td>$resCreated</td>
             <td>$resID</td>
             </tr>";
           }
           $stmt->close();
            ?>
       </table>

       <p class="big">DELETED</p>
       <p>Hier staan alle afspraken die uit het rooster zijn gehaald maar nog niet definitief verwijderd</p>
       <table>
          <tr>
           <th>Daypart</th>
           <th>Docent1</th>
           <th>Docent2</th>
           <th>Klas1jaar</th>
           <th>Klas1niveau</th>
           <th>Klas1nummer</th>
           <th>Klas2jaar</th>
           <th>Klas2niveau</th>
           <th>Klas2nummer</th>
           <th>Lokaal1</th>
           <th>Lokaal2</th>
           <th>Laptops</th>
           <th>ProjectCode</th>
           <th>Notes</th>
           <th>UserCreate</th>
           <th>UserDelete</th>
           <th>ID</th>
         </tr>
         <?php
         //echo alle database info
         require('../php/db-connect.php');
         $stmt = $conn->prepare('SELECT
             daypart,
             docent1,
             docent2,
             klas1jaar,
             klas1niveau,
             klas1nummer,
             klas2jaar,
             klas2niveau,
             klas2nummer,
             lokaal1,
             lokaal2,
             laptops,
             projectCode,
             notes,
             userCreate,
             userDelete,
             ID
           FROM
             deleted
        ');
        $stmt->execute();
        $stmt->store_result();
        //res voor result
        $stmt->bind_result(
          $resDaypart,
          $resDocent1,
          $resDocent2,
          $resKlas1jaar,
          $resKlas1niveau,
          $resKlas1nummer,
          $resKlas2jaar,
          $resKlas2niveau,
          $resKlas2nummer,
          $resLokaal1,
          $resLokaal2,
          $resLaptop,
          $resProjectCode,
          $resNote,
          $resUserCreate,
          $resUserDelete,
          $resID
        );
        while ($stmt->fetch()) {
          echo "<tr>
          <td>$resDaypart</td>
          <td>$resDocent1</td>
          <td>$resDocent2</td>
          <td>$resKlas1jaar</td>
          <td>$resKlas1niveau</td>
          <td>$resKlas1nummer</td>
          <td>$resKlas2jaar</td>
          <td>$resKlas2niveau</td>
          <td>$resKlas2nummer</td>
          <td>$resLokaal1</td>
          <td>$resLokaal2</td>
          <td>$resLaptop</td>
          <td>$resProjectCode</td>
          <td>$resNote</td>
          <th>$resUserCreate</th>
          <th>$resUserDelete</th>
          <td>$resID</td>
          </tr>";
        }
        $stmt->close();
        $conn->close();
          ?>
      </table>
    </div>
    <footer>
      <p><a href='https://www.joerigeuzinge.nl/copyright' target='_blank'>&copy; Joeri Geuzinge</a></p>
    </footer>
   </body>
 </html>
