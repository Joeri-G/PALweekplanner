<?php

//function om snel te checken of alle inputs gezet zijn
function _GETIsset($input) {
  //loop door array
  for ($i=0; $i < count($input); $i++) {
    //if de key net gezet is return false
    if (!isset($_GET[$input[$i]])) {
      return false;
    }
  }
  return true;
}

//functie om te checken of er overlap is tussen twee arrays
function isOverlap($arr1, $arr2) {
  //loop door eerste array
  for ($i=0; $i < count($arr1); $i++) {
    //loop door tweede array
    for ($x=0; $x < count($arr2); $x++) {
      //check iedere entry in het eerste array tegen iedere entry in het tweede array
      if ($arr1[$i] === $arr2[$x] && notNone($arr1[$i])) {
        //als er overlap is en deze overlap is niet tussen twee keer "None" return dan true
        return true;
      }
    }
  }
  return false;
}

//functie om voor overlap te kiezen maar dan speciaal voor klassen
//dit is omdat de klassen een object zijn in plaats van een string
//vrijwel identiek aan isOverlap()
function isOverlapKlas($arr1, $arr2) {
  //loop door eerste array
  for ($i=0; $i < count($arr1); $i++) {
    //loop door tweede array
    for ($x=0; $x < count($arr2); $x++) {
      //vergelijk de twee entries met elkaar, als ze gelijk zijn check dan of het niveau None is. Als het niveau None is zijn de twee andere waarde ook None
      if ($arr1[$i] == $arr2[$x] && $arr1[$i]->niveau !== 'None') {
        return true;
      }
    }
  }
}


//check of de input none is (of iets gelijkwaardigs)
function notNone($in) {
  if (
    $in == "none" ||
    $in == "" ||
    $in == "None" ||
    $in == false ||
    $in == null
  ) {
    return false;
  }
  return true;
}


//check of op zijn minst een van de entries in een array niet None is en of dit array dus een mogelijke input voor het rooster is
function isPossible($input) {
  //loop door het array
  for ($i=0; $i < count($input); $i++) {
    //als de input niet None is return dan true
    if (notNone($input[$i])) {
      return true;
    }
  }
  return false;
}

//zelfde als isPossible() maar dan voor klas omdat dit een object is in plaats van een string
function isPossibleKlas($input) {
  for ($i=0; $i < count($input); $i++) {
    if (notNone($input[$i]->jaar) && notNone($input[$i]->niveau) && notNone($input[$i]->nummer)) {
      return true;
    }
  }
  return false;
}

//alle drie de waardes voor een klas moeten gezet zijn anders worden deze naar None / 0 gezet
function checkKlas($in) {
  //maak leeg object voor output
  //loop door input array
  for ($i=0; $i < count($in); $i++) {
    //als een van de delen van een klas niet gezet is maak dan de hele entry None
    if (!notNone($in[$i]->jaar) || !notNone($in[$i]->niveau) || !notNone($in[$i]->nummer)) {
      $in[$i]->jaar = 0;
      $in[$i]->niveau = "None";
      $in[$i]->nummer = 0;
    }
  }
  return $in;
}

 ?>
