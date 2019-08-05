//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
function buildFull(data, list) {

  //request alle data voor beschikbare docenten, klassen en lokalen
  let xhttp3 = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp3.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
     try {
       //nu we de docent/klassen data hebben kunnen we de lijst maken
       let listAvailable = JSON.parse(this.responseText);
       //bouw html
       let html = '<table>';
       let dagdeel;
       //maak header titles voor ieder lesuur
       html += '<tr><th>Klas</th>';
       //maak subheader met informatie over wat iedere column is
       let subHeader = '<tr><td>Info</td>';
       for (var i = 0; i < dagen.length; i++) {
         for (var x = 0; x < uren; x++) {
           //voeg een table header toe met het dagdeel en te rooster tijden
           html += '<th colspan="8"><span class="dag">'+dagen[i] + ' ' + (x+1) + '</span><br><span class="tijd">' + lestijden[x]+'</span></th>';
           subHeader += '<td>Docent1</td><td>Docent2</td><td>Lokaal1</td><td>Lokaal2</td><td>Laptops</td><td>ProjectCode</td><td>Note</td><td>Action</td>';
         }
         subHeader += '</html>'
       }
       html += "</tr>";

       //voeg subheader toe
       html += subHeader;
       //voeg afspraken en afspraak inputs toe
       //voeg een klas toe voor iedere dag
       let klas;
       for (var i = 0; i < list.klas.length; i++) {
         html += '<tr>\n';
         //klas titel
         html += '<td>' + list.klas[i].jaar + list.klas[i].niveau + list.klas[i].nummer + '</td>\n';
         //voeg table content voor klas toe
         html += buildKlas(list.klas[i], data, listAvailable);

         html += '</tr>\n';
       }
       html += '</table>';
       main.innerHTML = html;
       //sort table
       sortTable();
     }
     catch (e) {
       //stop loading animatie
       load(false);
       errorMessage(e);
       listAvailable = {};
     }
    }
  };
  xhttp3.open("GET", "/api.php?listAvailable=true", true);
  xhttp3.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp3.send();
}

function buildKlas(klas, data, listAvailable) {
  let dagdeel;
  let html = '';
  //loop door dagen
  for (var i = 0; i < dagen.length; i++) {
    //loop door uren
    for (var x = 0; x < uren; x++) {
      dagdeel = dagen[i]+x;
      let komtVoor = false;
      //als het dagdeel bestaat
      if (typeof data[dagdeel] !== 'undefined' && data[dagdeel] !== null) {
        //loop door dagdeel
        for (var y = 0; y < data[dagdeel].length; y++) {
          //vergelijk objects
          let klasData = data[dagdeel][y];
          if (
            klasData.klas[0].jaar == klas.jaar &&
            klasData.klas[0].niveau == klas.niveau &&
            klasData.klas[0].nummer == klas.nummer
          ) {
            komtVoor = true;
            html += buildAfspraak(klasData);
          }
        }
      }
      if (!komtVoor) {
        html += buildInput(listAvailable, dagdeel, klas);
      }
    }
  }
  return html;
}

function buildAfspraak(data) {
  let html = '';
  //voeg content toe
  html += '<td>'+data.docent[0]+'</td>\n';
  html += '<td>'+data.docent[1]+'</td>\n';
  html += '<td>'+data.lokaal[0]+'</td>\n';
  html += '<td>'+data.lokaal[1]+'</td>\n';
  html += '<td>'+data.laptop+'</td>\n';
  html += '<td>'+data.projectCode+'</td>\n';
  //om te voorkomen dat lange notities de table verpesten truncaten we de note als deze meer dan 7 characters is
  if (data.note.length > 7) {
    data.note = data.note.substr(0, 6) + "\u2026";
  }
  html += '<td>'+data.note+'</td>\n';
  html += '<td data-hour=\'' + JSON.stringify(data) + '\'>';
  html += '<img src="/img/enlarge.svg" onclick="enlargeHour(this.parentElement.dataset.hour)" alt="Enlarge">\n';
  html += '<button type="button" class="SVGbutton" onclick="deleteHour(this.parentElement.dataset.hour, 1)"><img src="/img/closeBlack.svg" alt="Close"></button>';
  html += '</td>';

  return html;
}

function buildInput(listAvailable, dagdeel, klas) {
  let klasTitle = klas.jaar + klas.niveau + klas.nummer;
  let html = '';
  html += '<td><select name="'+dagdeel+klasTitle+'docent1">'+makeList(dagdeel, 'docent', 'Docent1', listAvailable)+'</select></td>\n';
  html += '<td><select name="'+dagdeel+klasTitle+'docent2">'+makeList(dagdeel, 'docent', 'Docent2', listAvailable)+'</select></td>\n';
  html += '<td><select name="'+dagdeel+klasTitle+'lokaal1">'+makeList(dagdeel, 'lokaal', 'Lokaal1', listAvailable)+'</select></td>\n';
  html += '<td><select name="'+dagdeel+klasTitle+'lokaal2">'+makeList(dagdeel, 'lokaal', 'Lokaal2', listAvailable)+'</select></td>\n';
  html += '<td><input type="number" name="'+dagdeel+klasTitle+'laptops" placeholder="Laptops"></td>';
  html += '<td><input type="text" name="'+dagdeel+klasTitle+'projectCode" placeholder="ProjectCode"></td>';
  html += '<td><input type="text" name="'+dagdeel+klasTitle+'note" placeholder="Note"></td>';
  //voeg een hidden input toe aan de laatste cell omdat de function anders in de war raakt
  html += '<td>';
  html += '<input type="hidden" name="'+dagdeel+klasTitle+'klas1" value="klas0" data-klas=\'{"data":['+JSON.stringify(klas)+']}\'>';
  html += '<button type="button" class="SVGbutton" onclick="sendHour(\'' + dagdeel+klasTitle + '\', \'' + dagdeel + '\', 1)"><img src="/img/save.svg" alt="Save"></button>';
  html += '</td>';

  return html;
}

//function om de table te sorten
function sortTable() {
  let table, rows, switching, i, x, y, shouldSwitch;
  table = document.getElementsByTagName("table")[0];
  switching = true;
  /* Make a loop that will continue until
  no switching has been done: */
  while (switching) {
    // Start by saying: no switching is done:
    switching = false;
    rows = table.rows;
    /* Loop through all table rows (except the
    first, which contains table headers): */
    for (i = 1; i < (rows.length - 1); i++) {
      // Start by saying there should be no switching:
      shouldSwitch = false;
      /* Get the two elements you want to compare,
      one from current row and one from the next: */
      x = rows[i].getElementsByTagName("TD")[0];
      y = rows[i + 1].getElementsByTagName("TD")[0];
      // Check if the two rows should switch place:
      // als de row met INFO begint switch dan niet
      if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase() && x.innerHTML != 'Info') {
        // If so, mark as a switch and break the loop:
        shouldSwitch = true;
        break;
      }
    }
    if (shouldSwitch) {
      /* If a switch has been marked, make the switch
      and mark that a switch has been done: */
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
    }
  }
}
