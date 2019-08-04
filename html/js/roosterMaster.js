//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
function buildSelect(value) {
  mode = value;
  if (value != 'klas' && value != 'docent') {
    errorMessage('INVALID MODE');
    return false;
  }
  let html = buildList(list[mode]);
  //selecteer de dropdown en vervang de HTML
  let drop = document.getElementsByName('displayModeFinal')[0];
  drop.innerHTML = html;
}

//functie om alle opties voor de lijst te maken
function buildList(input) {
  let html = '\t<option selected disabled>'+mode+'</option>\n';
  if (mode == 'docent') {
    for (var i = 0; i < input.length; i++) {
      html += '\t<option value="'+i+'">'+input[i].username+'</option>\n';
    }
  }
  else if (mode == 'klas') {
    for (var i = 0; i < input.length; i++) {
      html += '\t<option value="'+i+'">'+input[i].jaar+input[i].niveau+input[i].nummer+'</option>\n';
    }
  }
  return html;
}

function setTimetable(selected) {
  //load animatie
  load(true);
  //de values van de selectie lijst wijzen naar array entries dus de selectie dus hiermee halen we die waarde op uit de array
  selector = list[mode][selected];
  let xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        let data = JSON.parse(this.responseText);
        buildTimetable(data);
      }
      catch (e) {
        load(false);
        errorMessage(e);
      }
    }
  };
  //als de mode docent is haal dan de data van de klassen pagina
  if (mode == 'klas') {
    xhttp.open("GET", "/api.php?readKlas=true&jaar="+encodeURIComponent(selector.jaar)+"&niveau="+encodeURIComponent(selector.niveau)+"&nummer="+selector.nummer);
  }
  //anders haal de data van de docenten pagina
  else {
    xhttp.open("GET", "/api.php?readDocent=true&docent="+encodeURIComponent(selector.username), true);
  }
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.send();
}

function buildTimetable(data) {
  //request alle data voor beschikbare docenten, klassen en lokalen
  let xhttp3 = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp3.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
     try {
       //nu we de docent/klassen data hebben kunnen we de lijst maken
       let listAvailable = JSON.parse(this.responseText);
       let html = '';
       for (var i = 0; i < dagen.length; i++) {
         //functie om door dagen te loopen
         html += buildDay(data, dagen[i], listAvailable);
         //stop loading animatie
         load(false);
       }
       //haal de random text uit css rules uit de main
       main.style.display = 'grid';
       //plaats de HTML het document
       main.innerHTML = html;
       //zorg dat de footer onder de timetable zit
       document.getElementsByTagName('footer')[0].style.position = 'relative';
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

function buildDay(data, dag, listAvailable) {
  //als de mode docent is check dan of de docent wel op school is
  if (mode == 'docent' && typeof selector.availability[dag] !== undefined && !selector.availability[dag]) {
    let html = '<section>\n<p>'+dag+'</p>';
    for (var i = 0; i < uren; i++) {
      //fucntie om uur te maken
      html += '<div class="hour afwezig"><p>Docent is niet op school</p></div>\n';
      if (i < uren - 1) {
        html += '<div class="pause"></div>\n';
      }
    }
    html += '</section>';
    return html;
  }
  let html = '<section>\n';
  html += "<p>"+dag+"</p>";
  for (var i = 0; i < uren; i++) {
    dagdata = data[dag+i];
    //fucntie om uur te maken
    html += buildHour(data, dag, i, listAvailable);
  }
  html += '</section>\n';
  return html;
}

function buildHour(data, dag, uur, listAvailable) {
  let html = '';
  //selection om te bepalen of er een afspraak weergegeven moet worden of een input voor een nieuwe
  if (typeof data[dag+uur] !== 'undefined' && data[dag+uur] !== null) {
    let inside = {};
    nu = data[dag+uur];
    html = '<div class="hour afspraak" data-hour=\''+JSON.stringify(nu)+'\'">';
    for (var i = 0; i < nu.klas.length; i++) {
      if (nu.klas[i].niveau == 'None') {
        nu.klas[i].jaar = '';
        nu.klas[i].nummer = '';
      }
    }
    inside.docent1 = '<span>Docent1:</span><span>'+nu.docent[0]+'</span>\n';
    inside.docent2 = '<span>Docent2:</span><span>'+nu.docent[1]+'</span>\n';

    inside.klas = '<span>Klas:</span><span>'+nu.klas[0].jaar+nu.klas[0].niveau+nu.klas[0].nummer+'</span>\n';

    // inside.klas1 = '<span>Klas1:</span><span>'+nu.klas[0].jaar+nu.klas[0].niveau+nu.klas[0].nummer+'</span>\n';
    // inside.klas2 = '<span>Klas2:</span><span>'+nu.klas[1].jaar+nu.klas[1].niveau+nu.klas[1].nummer+'</span>\n';

    inside.lokaal1 = '<span>Lokaal1:</span><span>'+nu.lokaal[0]+'</span>\n';
    inside.lokaal2 = '<span>Lokaal2:</span><span>'+nu.lokaal[1]+'</span>\n';

    inside.laptops = '<span>laptops:</span><span>'+nu.laptop+'</span>\n';
    inside.projectCode = '<span>ProjectCode:</span><span>'+nu.projectCode+'</span>\n';
    inside.note = '<span>Note:</span><span class="note">'+nu.note+'</span>\n';

    html += '<button type="button" class="close" onclick="deleteHour(this.parentElement.dataset.hour)"><img src="/img/closeBlack.svg"></button>\n<p>\n'+lestijden[uur]+'</p><div class="menu list" onclick="enlargeHour(this.parentElement.dataset.hour);">\n'+
            inside.docent1+
            inside.docent2+
            inside.klas+
            // inside.klas1+
            // inside.klas2+
            inside.lokaal1+
            inside.lokaal2+
            inside.laptops+
            inside.projectCode+
            inside.note+
            '</div>\n';
  }
  else {
    html = '<div class="hour input"><p>\n'+lestijden[uur]+'</p><div name="'+dag+uur+'" class="menu">';
    //de selection hangt af van de mode
    if (mode == 'docent') {
      //voeg een hidden input toe met de naam van de docent
      //omdat het rooster van een docent is geselecteerd is hoeven we niet de selectie van twee weer te geven
      html += '<input type="hidden" name="'+dag+uur+'docent1" value="'+selector.username+'">';
      html += '\n<select name="'+dag+uur+'docent2">';
      html += makeList(dag+uur, 'docent', 'docent2', listAvailable);
      html += '</select>\n';
      html += '<select name="'+dag+uur+'klas1" data-klas=\'{"data":'+JSON.stringify(listAvailable.klas[dag+uur])+'}\'>';
      html += makeList(dag+uur, 'klas', 'klas', listAvailable);
      // html += '</select>\n<select name="'+dag+uur+'klas2" data-klas=\'{"data":'+JSON.stringify(listAvailable.klas[dag+uur])+'}\'>';
      // html += makeList(dag+uur, 'klas', 'klas2', listAvailable);
      html += '</select>\n';
      html += '\n<select name="'+dag+uur+'lokaal1">';
      html += makeList(dag+uur, 'lokaal', 'lokaal1', listAvailable);
      html += '</select>\n<select name="'+dag+uur+'lokaal2">';
      html += makeList(dag+uur, 'lokaal', 'lokaal2', listAvailable);
      html += '</select><input type="number" name="'+dag+uur+'laptops" min="0" max="1000" placeholder="laptops">';
    }
    else {
      html += '<input type="hidden" name="'+dag+uur+'klas1" value="klas0" data-klas=\'{"data":['+JSON.stringify(selector)+']}\'>';
      html += '<select name="'+dag+uur+'docent1">';
      html += makeList(dag+uur, 'docent', 'docent1', listAvailable);
      html += '</select><select name="'+dag+uur+'docent2">';
      html += makeList(dag+uur, 'docent', 'docent2', listAvailable);
      // html += '</select><select name="'+dag+uur+'klas2" data-klas=\'{"data":'+JSON.stringify(listAvailable.klas[dag+uur])+'}\'>';
      // html += makeList(dag+uur, 'klas', 'klas2', listAvailable);
      html += '</select>\n';
      html += '\n<select name="'+dag+uur+'lokaal1">';
      html += makeList(dag+uur, 'lokaal', 'lokaal1', listAvailable);
      html += '</select>\n<select name="'+dag+uur+'lokaal2">';
      html += makeList(dag+uur, 'lokaal', 'lokaal2', listAvailable);
      html += '</select>\n<input type="number" name="'+dag+uur+'laptops" min="0" max="1000" placeholder="laptops">\n';
    }

    html += '<input type="text" name="'+dag+uur+'projectCode" placeholder="ProjectCode">\n</div><input type="text" name="'+dag+uur+'note" placeholder="Note">\n<button type="button" onclick="sendHour(\''+dag+uur+'\', \''+dag+uur+'\')">Go</button>';
  }
  html += '</div>\n';
  if (uur < uren - 1) {
    html += '<div class="pause"></div>';
  }
  return html;
}

function sendHour(name, dagdeel) {
  load(true);
  //we moeten nu de values van alle selections/input uit het parent element halen
  let inputs = ['docent1', 'docent2', 'klas1',/* 'klas2',*/ 'lokaal1', 'lokaal2', 'laptops', 'projectCode', 'note'];
  let url = '/api.php?insert=true&daypart='+dagdeel;
  let value;
  let klassen = 0;
  for (var i = 0; i < inputs.length; i++) {
    value = document.getElementsByName(name+inputs[i])[0].value;
    console.log(name+inputs[i]);
    console.log(value);
    //als het veld leeg is maak er dan None van
    if (value == '') { value = "None"};
    //als de value een klas is moeten we wat meer doen om het jaar, niveau en nummer er uit te krijgen
    if (inputs[i].substring(0,4) == 'klas' ) {
      klassen++;
      if (value !=='None') {
        let pos = Number(value.substring(4, 5));
        let json = JSON.parse(document.getElementsByName(name+inputs[i])[0].dataset.klas);
        url += '&klas'+klassen+'jaar='+json.data[pos].jaar;
        url += '&klas'+klassen+'niveau='+json.data[pos].niveau;
        url += '&klas'+klassen+'nummer='+json.data[pos].nummer;
      }
      else {
        url += '&klas'+klassen+'jaar=None';
        url += '&klas'+klassen+'niveau=None';
        url += '&klas'+klassen+'nummer=None';
      }
    }
    else {
      //voeg de key en value toe aan de url
      url += '&'+encodeURIComponent(inputs[i])+'='+encodeURIComponent(value);
    }
  }
  console.log(url);
  let xhttp4= new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp4.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      message(this.responseText);
      setTimetable(document.getElementsByName('displayModeFinal')[0].value);
      return null;
    }
  };
  xhttp4.open("GET", url, true);
  xhttp4.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp4.send();
}
