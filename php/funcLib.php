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
  - notNone()
    * functie om te checken of de value van de input None is, of een soort gelijke value
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
    if (ctype_digit($in)) {
        return intval($in);
    }
    return 0;
}

function getConf($file = __DIR__."/../conf/conf.json")
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
