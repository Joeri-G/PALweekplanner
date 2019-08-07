//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
function login() {
  load(true);
  //haal username en wachtwoord uit textboxes
  let usernameObj = document.getElementById('username');
  let passwordObj = document.getElementById('password');
  let username = usernameObj.value;
  let password = passwordObj.value;
  //maak password textbox leeg
  document.getElementById('password').value = '';

  //check of beide wel gezet zijn
  if (username.replace(/\s/g, '') == '' || password == '') {
    load(false);
    message('Username or password is empty');
    return null;
  }

  //maak post data string
  let post = "username="+encodeURIComponent(username)+"&password="+encodeURIComponent(password);
  //maak request object
  let xhttp = new XMLHttpRequest();
  //laad list met alle docenten en klassen
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      load(false);
      if (this.responseText == 'OK' || this.responseText == 'Already logged in') {
        usernameObj.className = '';
        passwordObj.className = '';
        window.location = '/';
      }
      else {
        usernameObj.className = 'incorrect';
        passwordObj.className = 'incorrect';
        load(false);
        message(this.responseText);
      }
    }
  };
  xhttp.open("POST", "/login/login.php", true);
  xhttp.setRequestHeader("Content-Encoding", "gzip, x-gzip, identity");
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(post);
}

//voeg trigger toe voor username en password boxes
document.getElementById('username').addEventListener("keydown", function (e) {
    if (e.keyCode === 13) {  //checks whether the pressed key is "Enter"
        login();
    }
});
document.getElementById('password').addEventListener("keydown", function (e) {
    if (e.keyCode === 13) {  //checks whether the pressed key is "Enter"
        login();
    }
});
