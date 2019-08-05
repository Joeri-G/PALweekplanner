//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
//GLOBALS
let conf, dagen, uren, lestijden;
let main = document.getElementsByTagName('main')[0];

//function voor menu buttons
function menu(bool) {
  if (bool) {
    let menu = document.getElementsByTagName('menu')[0].style.display = 'block';
  }
  else if (!bool) {
    let menu = document.getElementsByTagName('menu')[0].style.display = 'none';
  }
}

//function om error modals weer te geven
function errorMessage(error) {
  console.log(error);
  message(error, false);
}

function load(mode) {
  //get image
  let img = document.getElementById('loading');
  let loadingContent = document.getElementById('loadingContent');
  //if show
  if (mode) {
    img.style.display = 'block';
    loadingContent.setAttribute('class', 'fade-in');
    //start loading animation
  }
  //else
  else {
    setTimeout(function() {
      //fade out
      loadingContent.setAttribute('class', 'fade-out');
      //remove fadeout
      setTimeout(function(){img.style.display = "none"; img.setAttribute('class', '');}, 200);
    }, 500);
  }
}

//function om berichten weer te geven
function message(text, escape = true) {
  //declare objects
  let messageModal = document.getElementById('messageModal');
  let messageModalContent = document.getElementById('messageModalContent');
  //escape text
  if (escape) {
    text = escapeHTML(text);
  }
  //zet text
  messageModalContent.innerHTML = text;

  setTimeout(function(){
    messageModal.style.display = 'block';
    messageModalContent.setAttribute('class', 'fade-in');
  }, 200);
}

//functie om HTML te escapen
function escapeHTML(input) {
  //replace less than
  let out = input.replace(/</g, '&lt;');
  //replace greater than
  out = out.replace(/>/g,'&gt;');
  //replace newline with line break
  out = out.replace(/\n/g, '<br>');
  //replace ' ' (space) with non-breaking space
  out = out.replace(/ /g, '&nbsp;');
  //return
  return out;
}



//JS voor message modal
//wanneer er buiten de modal geklikt wordt, sluit de modal
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


//js voor config
//laad config file
let xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
  if (this.readyState == 4 && this.status == 200) {
   try {
    config = JSON.parse(this.responseText);
    uren = config.uren;
    dagen = config.dagen;
    lestijden = config.lestijden;
   }
   catch (e) {
     errorMessage(e);
     uren = 0;
     dagen = [];
   }
  }
};
xhttp.open("GET", "/api.php?loadconfig=true", true);
xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
xhttp.send();

function enlargeHour(data) {
  let json = JSON.parse(data);
  let text = 'Docent1:\t\t'+json.docent[0];
  text += '\nDocent2:\t\t'+json.docent[1];
  text += '\n\nKlas:\t\t'+json.klas[0].jaar+json.klas[0].niveau+json.klas[0].nummer;
  text += '\n\nLokaal1:\t\t'+json.lokaal[0];
  text += '\nLokaal2:\t\t'+json.lokaal[0];
  text += '\n\nProjectCode:\t'+json.projectCode;
  text += '\nNote:\t\t'+json.note;
  message(text);
}

function deleteHour(data, mode = 0) {
  load(true);
  if(confirm('Wilt u deze afspraak verwijderen?')) {
    let xhttp5= new XMLHttpRequest();
    //laad list met alle docenten en klassen
    xhttp5.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        message(this.responseText);
        load(false);
        setTimeout(function() {
          if (mode == 0) {
            setTimetable(document.getElementsByName('displayModeFinal')[0].value);
          }
          else if (mode == 1) {
            modeFull();
          }
        }, 1000);
      }
    };
    let id = JSON.parse(data).ID;
    xhttp5.open("GET", "/api.php?delete=true&ID="+encodeURIComponent(id), true);
    xhttp5.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
    xhttp5.send();
  }
  else {
    setTimeout(function() {load(false);}, 200);
  }
}

function makeList(dagdeel, type, lable, listAvailable) {
  //haal de data uit de lijst met beschikbare docenten, klassen, etc
  let lijst = listAvailable[type][dagdeel];
  let html = '<option selected disabled value="None">'+lable+'</option>\n';
  html += '<option value="None">Geen</option>';
  //voor klassen moeten we het anders doen
  if (type == 'klas') {
    for (var i = 0; i < lijst.length; i++) {
      html += '<option value="klas'+i+'">'+lijst[i].jaar+lijst[i].niveau+lijst[i].nummer+'</option>\n';
    }
    return html;
  }
  for (var i = 0; i < lijst.length; i++) {
    html += '<option value="'+lijst[i]+'">'+lijst[i]+'</option>';
  }
  return html;
}

function sendHour(name, dagdeel, mode = 0) {
  load(true);
  //we moeten nu de values van alle selections/input uit het parent element halen
  let inputs = ['docent1', 'docent2', 'klas1',/* 'klas2',*/ 'lokaal1', 'lokaal2', 'laptops', 'projectCode', 'note'];
  let url = '/api.php?insert=true&daypart='+dagdeel;
  let value;
  let klassen = 0;
  for (var i = 0; i < inputs.length; i++) {
    value = document.getElementsByName(name+inputs[i])[0].value;
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
  let xhttp4= new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp4.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      message(this.responseText);
      setTimeout(function() {
        if (mode == 0) {
          setTimetable(document.getElementsByName('displayModeFinal')[0].value);
        }
        else if (mode == 1) {
          modeFull();
        }
      }, 1000);
      return null;
    }
  };
  xhttp4.open("GET", url, true);
  xhttp4.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp4.send();
}
