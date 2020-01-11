<?php

//haal data uit database
require("db-connect.php");
require("funcLib.php");
$data = array();
// plaats headers in data
$data['INFO'] = array(
  "projectTitle" => "Title",
  "projectCode" => "Afkorting",
  "projectBeschrijving" => "Beschrijving",
  "projectInstructie" => "Instructie",
  "verantwoordelijke" => "projectleider",
  "user" => "USER",
  "TIME" => "TIME",
  "IP" => "IP",
  "ID" => "ID"
);

$stmt = $conn->prepare(
    'SELECT
  projectTitle,
  projectCode,
  projectBeschrijving,
  projectInstructie,
  verantwoordelijke,
  user,
  `TIME`,
  `IP`,
  ID
FROM
  projecten'
);

$stmt->execute();

$stmt->bind_result(
    $resProjectTitle,
    $resProjectCode,
    $resProjectBeschrijving,
    $resProjectInstructie,
    $resVerantwoordelijke,
    $resUser,
    $resTIME,
    $resIP,
    $resID
);
while ($stmt->fetch()) {
    //escape alle semicolons omdat deze soms ook als cell seperator gebruikt worden
    $arr = array(
      "projectTitle" => str_replace(';', '\;', $resProjectTitle),
      "projectCode" => str_replace(';', '\;', $resProjectCode),
      "projectBeschrijving" => str_replace(';', '\;', $resProjectBeschrijving),
      "projectInstructie" => str_replace(';', '\;', $resProjectInstructie),
      "verantwoordelijke" => str_replace(';', '\;', $resVerantwoordelijke),
      "user" => $resUser,
      "TIME" => $resTIME,
      "IP" => $resIP,
      "ID" => $resID
    );
    $data[] = $arr;
}

// var_dump($data);

//5MB is in memory, als het groter word wordt er geschreven naar een temp file
$csv = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');

// var_dump($data);

foreach ($data as $project) {
    fputcsv($csv, $project);
}

rewind($csv);

// put it all in a variable
$out = stream_get_contents($csv);
echo "$out";
//Download headers
header('Content-Type: text/csv');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"projecten-export-".date('Y-m-d_H.i.s').".csv\"");
