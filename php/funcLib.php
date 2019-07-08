<?php

function _GETIsset($input) {
  //loop through list
  for ($i=0; $i < count($input); $i++) {
    //if the value is not set return false
    if (!isset($_GET[$input[$i]])) {
      return false;
    }
  }
  return true;
}

function isOverlap($arr1, $arr2) {
  //loop through first array
  for ($i=0; $i < count($arr1); $i++) {
    //loop through second array
    for ($x=0; $x < count($arr2); $x++) {
      //check every entry in the first array against every entry in the second array
      if ($arr1[$i] === $arr2[$x] && notNone($arr1[$i])) {
        //if there is overlap return true
        return true;
      }
    }
  }
  return false;;
}


//check if any other value than none occurs in array
function notNone($input) {
  if ($input == "none" || $input == "" || $input == "None" || $input == false) {
    return false;
  }
  return true;
}


//check if any other value than none occurs in array
function isPossible($input) {
  //set to false
  $hasValue = false;
  //loop through list
  for ($i=0; $i < count($input); $i++) {
    //if the input is None nor empty set to true
    if ($input[$i] != "none" && $input[$i] != "" && $input[$i] != "None") {
      $hasValue = true;
    }
  }
  return $hasValue;
}


 ?>
