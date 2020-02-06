<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
script om te exporten naar somToday format
Official:     <Docent>  <Jaar>  <Maand> <Dag> <Lesuur>  <Vak/Project> <Klas>  <Lokaal>  <Leeg>    <Leeg>  <Leeg>  <Starttijd> <Duur (min)>  <Leeg>
Alternative:  <Docent>  <Jaar>  <Maand> <Dag> <Lesuur>  <Vak/Project> <Klas>  <Lokaal>  <Laptops> <Info>  <Leeg>  <Starttijd> <Duur (min)>  <Leeg>


UITTREKSEL VAN SOMTODAY SPEC
Bestandsspecificaties Import Velden voorafgegaan door een * zijn verplicht.
Het aantal velden, zoals bij elk voorbeeld wordt vermeld, moet altijd aanwezig zijn.
Dit is te controleren door het aantal scheidingstekens te tellen, het aantal scheidingstekens is altijd 1 minder dan het aantal velden: bij 7 scheidingstekens zijn er 8 velden.

  * Docentenafkorting
  * Jaar
  * Maand
  * Dag
  * Lesuur
  * Vakafkorting
  * Klas/lesgroep
  * Lokaal
  <leeg>
  <leeg>
  <leeg>
  * Starttijd
  * Duur in minuten


 */
require('funcLib.php');
if (!_GETIsset(['startDate'])) {
    die("[INPUT]\tNOT ALL PARAMETERS SET");
}
// yyyy-mm-dd
$date = [
  "yyyy" => 0000,
  "mm" => 00,
  "dd" => 00
];

$dateIn = explode('-', $_GET['startDate']);

if (count($dateIn) !== 3) {
    die("[DATE] INVALID PLEASE USE yyyy-mm-dd");
}

//put date in array
$date["yyyy"] = toNum($dateIn[0]);
$date[ "mm" ] = toNum($dateIn[1]);
$date[ "dd" ] = toNum($dateIn[2]);

$conf = getConf();

//loop door dagen
foreach ($conf->dagen as $dag) {
    //loop door uren
    for ($i=0; $i < $conf->uren; $i++) {
        echo "$dag$i\n";
    }
}
