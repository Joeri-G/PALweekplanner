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
       let subHeader = '<tr><th>Info</th>';
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
         html += '<th>' + list.klas[i].jaar + list.klas[i].niveau + list.klas[i].nummer + '</th>\n';
         //voeg table content voor klas toe
         html += buildKlas(list.klas[i], data, listAvailable);

         html += '</tr>\n';
       }
       html += '</table>';
       main.innerHTML = html;
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
        html += buildInput(listAvailable);
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
  html += '<img src="/img/enlarge.svg" onclick="enlargeHour(this.parentElement.dataset.hour)">\n';
  html += '<button type="button" class="close" onclick="deleteHour(this.parentElement.dataset.hour, 1)"><img src="/img/closeBlack.svg"></button>';
  html += '</td>';


  return html;
}

function buildInput(listAvailable) {
  let html = '';

  html += '<td><select>'+makeList('MA0', 'lokaal', 'test', listAvailable)+'</select></td>';
  html += '<td><select>'+makeList('MA0', 'lokaal', 'test', listAvailable)+'</select></td>';
  html += '<td><select>'+makeList('MA0', 'lokaal', 'test', listAvailable)+'</select></td>';
  html += '<td><select>'+makeList('MA0', 'lokaal', 'test', listAvailable)+'</select></td>';
  html += '<td><select>'+makeList('MA0', 'lokaal', 'test', listAvailable)+'</select></td>';
  html += '<td><select>'+makeList('MA0', 'lokaal', 'test', listAvailable)+'</select></td>';
  html += '<td><select>'+makeList('MA0', 'lokaal', 'test', listAvailable)+'</select></td>';
  html += '<td><select>'+makeList('MA0', 'lokaal', 'test', listAvailable)+'</select></td>';


  return html;
}
