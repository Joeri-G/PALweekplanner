var dataVar, mode, selected;
const docentTitles = ['Klas', 'Lokaal', 'Laptops', 'Klas 2', 'Docent 2', 'Lokaal 2'];
const klasTitles = ['Docent', 'Lokaal', 'Laptops', 'Klas 2', 'Docent 2', 'Lokaal 2'];

function getOptions() {
  //start loading animation
  load(true);
  //send request to server
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
        //parse data
      try {
        dataVar = JSON.parse(this.responseText);
        buildSelect('klas', dataVar);
        return dataVar;
      }
      catch (e) {
        console.error(e);
        console.log(this.responseText);
      }
      finally {
        //end loading animation
        setTimeout(function() {load(false);}, 500);
      }
    }
  };
  xhttp.open("GET", "/api.php?getList", true);
  xhttp.send();
}
function buildSelect(type, data) {
  //global mode var
  mode = type;
  //generate list
  var dataList = data[type];
  var html = getListContent(type, dataList);
  var list = document.getElementsByName('displayModeFinal')[0];
  list.innerHTML = html;

}


function setTimetable() {
  //fetch value
  selected = document.getElementsByName('displayModeFinal')[0].value;
  //start loading animation
  load(true);
  const setting = [document.getElementsByName('displayMode')[0].value, document.getElementsByName('displayModeFinal')[0].value];

  console.log(setting);


  //if klas, make timetable for class
  if (setting[0] == "klas") {
    makeTimetable(klasTitles, setting);
  }
  else if (setting[0] == "docent") {
    makeTimetable(docentTitles, setting);
  }

  //end load animation
  setTimeout(function() {load(false);}, 500);
}

function makeTimetable(titles, setting) {
  var day = dagen;
  var timeText = tijden;
  var html = '';
  for (var i = 0; i < day.length; i++) {
    //array with all the pauses
    let pauses = [2, 4, 6];

    //add day
    html += "<section><p>"+day[i]+"</p>";
    for (var x = 0; x < timeText.length; x++) {
      var status = '';
      //add pauses
      if (pauses.indexOf(x) > -1) {
        html += '<div class="pause"></div>'
      }
      //add hour
      html += makeHour(timeText[x], (day[i]+x), titles);
    }
    html += "</section>"
  }
  document.getElementsByTagName("main")[0].innerHTML = html;
}

function makeHour(timeText, hourID, titles) {
  var data = ["None", "Optie 1","Optie 2","Optie 3","Optie 4","Optie 5"];
  var html = "<div class=\"hour\"><p>"+timeText+" | <button onclick=\"pushHour('"+hourID+"')\">Update</button></p>\n";
  // <select name='"+day+number+"op1'>"+getListContent(groupTitle, data)+"</select><select name='"+day+number+"op2'>"+getListContent(roomTitle, data)+"</select>
  for (var i = 0; i < titles.length; i++) {
    html += "\t<select name=\""+hourID+"s"+i+"\">\n"+getHourContent(hourID, titles[i], data)+"\t</select>\n";
  }
  html += '\t<input type="text" name="'+hourID+"s"+(i)+'" placeholder="Notes">\n';
  html += "</div>\n";
  return html;
}

function getHourContent(hourID, title, data, select = 'title') {
  //if the selected option is the title add the title to the array with the SELECTED attribute
  if (select == 'title') {
    var html = '\t\t<option selected disabled>'+title+'</option>\n';
  }
  //else add it without the SELECTED attribute
  else {
    var html = '\t\t<option disabled>'+title+'</option>\n';
  }
  for (var i = 0; i < data.length; i++) {
    if (i == select) {
      html += '\t\t<option selected value="'+data[i]+'">'+data[i]+'</option>\n';
    }
    else {
      html += '\t\t<option value="'+data[i]+'">'+data[i]+'</option>\n';
    }
  }
  return html;
}

var data  = ['Docent', 'Lokaal', 'Laptops', 'Extra Docent', 'Extra Lokaal'];

function getListContent(title, data) {
  var html = '<option selected disabled>'+title+'</option>';
  for (var i = 0; i < data.length; i++) {
    html += '<option>'+data[i]+'</option>';
  }
  return html;
}

function getTimetableData(settings) {
  return null;
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
    //fade out
    loadingContent.setAttribute('class', 'fade-out');
    //remove fadeout
    setTimeout(function(){img.style.display = "none"; img.setAttribute('class', '');}, 200);
  }
}

function pushHour(id) {
  //start load animation
  load(true);

  let isDefaultSelection = false;
  //selections
  var data = [];
  data[0] = document.getElementsByName(id+'s0')[0].value;
  data[1] = document.getElementsByName(id+'s1')[0].value;
  data[2] = document.getElementsByName(id+'s2')[0].value;
  data[3] = document.getElementsByName(id+'s3')[0].value;
  data[4] = document.getElementsByName(id+'s4')[0].value;
  data[5] = document.getElementsByName(id+'s5')[0].value;
  data[6] = document.getElementsByName(id+'s6')[0].value;


  //check if any of the selections is still the default value;
  for (var i = 0; i < klasTitles.length; i++) {
    if (data[i] == klasTitles[i] || data[i] == docentTitles[i]) {
      isDefaultSelection = true;
    }
  }

  //if selections are left to default give error and stop execution
  if (isDefaultSelection) {
    //get objects

    //set error text
    let errrorMSG = "<p>Maak alst u blieft een selectie uit ierdere dropdown</p>\n<p>Als u geen optie wilt selecteren kies dan \"None\" uit het menu</p>";

    //fade out loading animation and fade in message box
    feedbackPopup(errrorMSG, true);

    return null;
  }


  let params = `mode=`+encodeURIComponent(mode)+`&selected=`+encodeURIComponent(selected)+`&daypart=`+encodeURIComponent(id)+`&kd=`+encodeURIComponent(data[0])+`&lk=`+encodeURIComponent(data[1])+`&lp=`+encodeURIComponent(data[2])+`&kl2=`+encodeURIComponent(data[3])+`&d2=`+encodeURIComponent(data[4])+`&lk2=`+encodeURIComponent(data[5])+`&note=`+encodeURIComponent(data[6]);

  console.log(params);

  //post data
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
        //give feedback
        feedbackPopup(this.responseText, true, true);
        return true;
    }
  };
  xhttp.open("GET", "/api.php?"+params, true);
  xhttp.send();
}

//give feedback
function feedbackPopup(text, endAnimation = false, escape = false) {
  //declare objects
  let messageModal = document.getElementById('messageModal');
  let messageModalContent = document.getElementById('messageModalContent');
  //if escape flag is set
  if (escape) {
    //escape text
    text = escapeHTML(text);
  }
  //set text
  messageModalContent.innerHTML = text;

  if (endAnimation) {
    load(false);
  }

  setTimeout(function(){
    messageModal.style.display = 'block';
    messageModalContent.setAttribute('class', 'fade-in');
  }, 200);
}

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
