function makeHour(timeText, hourID, titles) {
  var data = ["Optie 1","Optie 1","Optie 1","Optie 1","Optie 1"];
  var html = "<div class=\"hour\"><p>"+timeText+"</p>\n";
  // <select name='"+day+number+"op1'>"+getListContent(groupTitle, data)+"</select><select name='"+day+number+"op2'>"+getListContent(roomTitle, data)+"</select>
  for (var i = 0; i < titles.length; i++) {
    html += "\t<select name=\""+hourID+"s"+i+"\">\n"+getListContent(hourID, titles[i], data)+"\t</select>\n";
  }
  html += '\t<input type="text" name="'+hourID+"s"+(i+1)+'" placeholder="Notes">\n';
  html += "</div>\n";
  return html;
}

function getListContent(hourID, title, data, select = 'title') {
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
console.log(makeHour("8.30 - 9.15", "MA0", data));








var dataVar;
function getOptions() {
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
      }
    }
  };
  xhttp.open("GET", "/api.php?getList", true);
  xhttp.send();
}
function buildSelect(type, data) {
  //
  var dataList = data[type];
  var html = getListContent(type, dataList);
  var list = document.getElementsByName('displayModeFinal')[0];
  list.innerHTML = html;

}


function setTimetable() {
  var setting = [document.getElementsByName('displayMode')[0].value, document.getElementsByName('displayModeFinal')[0].value];
  console.log(setting);
  //if klas, make timetable for class
  if (setting[0] == "klas") {
    makeTimetable(['Docent', 'Lokaal', 'Laptops', 'Extra Docent', 'Extra Lokaal'], setting);
  }
  else if (setting[0] == "docent") {
    makeTimetable(['Klas', 'Lokaal', 'Laptops', 'Extra Klas', 'Extra Lokaal'], setting);
  }
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
  var data = ["Optie 1","Optie 1","Optie 1","Optie 1","Optie 1"];
  var html = "<div class=\"hour\"><p>"+timeText+"</p>\n";
  // <select name='"+day+number+"op1'>"+getListContent(groupTitle, data)+"</select><select name='"+day+number+"op2'>"+getListContent(roomTitle, data)+"</select>
  for (var i = 0; i < titles.length; i++) {
    html += "\t<select name=\""+hourID+"s"+i+"\">\n"+getListContent(hourID, titles[i], data)+"\t</select>\n";
  }
  //add notes
  html += '\t<input type="text" name="'+hourID+"s"+(i+1)+'" placeholder="Notes">\n';
  //close container
  html += "</div>\n";
  return html;
}

function getListContent(hourID, title, data, select = 'title') {
  //if the selected option is the title add the title to the array with the SELECTED attribute
  if (select == 'title') {
    var html = '\t\t<option selected disabled>'+title+'</option>\n';
  }
  //else add it without the SELECTED attribute
  else {
    var html = '\t\t<option disabled>'+title+'</option>\n';
  }
  for (var i = 0; i < data.length; i++) {
    //if selected option add SELECTED attribute
    if (i == select) {
      html += '\t\t<option selected value="'+data[i]+'">'+data[i]+'</option>\n';
    }
    //
    else {
      html += '\t\t<option value="'+data[i]+'">'+data[i]+'</option>\n';
    }
  }
  return html;
}

var data  = ['Docent', 'Lokaal', 'Laptops', 'Extra Docent', 'Extra Lokaal'];
console.log(makeHour("8.30 - 9.15", "MA0", data));




function getTimetableData(settings) {
  return null;
}
