<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
script met functies die in verschilldende sripts gebruikt worden
  - _GETIsset()
    * functie om efficient te checken of alle inputs gezet zijn
  - _POSTIsset()
    * zelfde als _GETIsset maar dan met $_POST
  - isOverlap()
    * functie om te checken of er overlap is tussen twee arrays
  - isOverlapKlas()
    * isOverlap() maar dan aangepast voor klasssen
  - notNone()
    * functie om te checken of de value van de input None is, of een soort gelijke value
  - notNoneKlas()
    * notNone() maar dan aangepast voor klassen
  - checkKlas()
    * functie om te checken of alle nodige values van een klas object gezet zijn ($klas->jaar, $klas->niveau, $klas->nummer)
  - daypartCheck()
    * functie om te checken of het opgegeven dagdeel wel valid is vergeleken met de config files
  - toNum()
    * check of input een nummer is en als dat het geval is maak nummer
  - getConf()
    * load config file to object
*/
//function om snel te checken of alle inputs gezet zijn
function _POSTIsset($input = array())
{
    //loop door array
    for ($i=0; $i < count($input); $i++) {
        //als de key niet gezet is return false
        if (!isset($_POST[$input[$i]])) {
            return false;
        }
    }
    return true;
}

//function om snel te checken of alle inputs gezet zijn
function _GETIsset($input = array())
{
    //loop door array
    for ($i=0; $i < count($input); $i++) {
        //als de key niet gezet is return false
        if (!isset($_GET[$input[$i]])) {
            return false;
        }
    }
    return true;
}

//functie om te checken of er overlap is tussen twee arrays
function isOverlap($arr1 = array(), $arr2 = array())
{
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
function isOverlapKlas($arr1 = array(), $arr2 = array())
{
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
function notNone($in = null)
{
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


//notNone maar dan voor klas
function notNoneKlas($in = null)
{
    if (notNone($in->jaar) && notNone($in->niveau) && notNone($in->nummer)) {
        return true;
    }
    return false;
}

//check of op zijn minst een van de entries in een array niet None is en of dit array dus een mogelijke input voor het rooster is
function isPossible($input = array())
{
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
function isPossibleKlas($input = array())
{
    for ($i=0; $i < count($input); $i++) {
        if (notNone($input[$i]->jaar) && notNone($input[$i]->niveau) && notNone($input[$i]->nummer)) {
            return true;
        }
    }
    return false;
}

//alle drie de waardes voor een klas moeten gezet zijn anders worden deze naar None / 0 gezet
function checkKlas($in = array())
{
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

//check of het opgegeven dagdeel wel bestaat
function daypartCheck($daypart = null)
{
    //check of de input format klopt
    if (strlen($daypart) !== 3 || !notNone($daypart)) {
        return false;
    }
    //haal het dagdeel uit de input string
    $dag = substr($daypart, 0, 2);
    //type shift zodat we kunnen checken of het in de range is
    $uur = (int) substr($daypart, 2);

    //lees config file met alle dagen en uren
    $file = file_get_contents('../conf/conf.json');
    //parse de JSON
    $json = json_decode($file);
    //haal dagen en uren uit config file
    $dagen = $json->dagen;
    $uren = $json->uren;

    //check of uur in de config file zit
    if (!in_array($dag, $dagen)) {
        return false;
    }
    //check of uur wel in de range zit
    if ($uur < 0 || $uur > $uren) {
        return false;
    }
    return true;
}

function toNum($in = '0')
{
  if (ctype_digit($in))
    return intval($in);
  return 0;
}

function getConf($file = "../conf/conf.json")
{
  $json = file_get_contents($file);
  try {
    $data = json_decode($json);
  } catch (\Exception $e) {
    $data = new stdClass;
    $data->dagen = array();
    $data->uren = 0;
    $data->lestijden = array();
    $data->lesuurStart = array();
    $data->lesuurDuur = 0;
    $data->laptops = 0;
  }
  return $data;
}
