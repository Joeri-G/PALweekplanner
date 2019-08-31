<?php

//haal data uit database
require("db-connect.php");

$data = array();

// plaats headers in data
$data['INFO'] = array();
$data['INFO'][] = array(
  "dagdeel" => "Dagdeel",
  "klas" => "Klas",
  "docent1" => "Docent1",
  "docent2" => "Docent2",
  "lokaal1" => "Lokaal1",
  "lokaal2" => "Lokaal2",
  "laptops" => "Laptops",
  "project" => "Project Code",
  "note" => "Note",
  "USER" => "USER",
  "TIME" => "TIME",
  "IP" => "IP",
  "ID" => "ID"
);


$config = json_decode(file_get_contents('../conf/conf.json'));

foreach ($config->dagen as $dag) {
    for ($i=0; $i < $config->uren; $i++) {
        $data[$dag.$i] = array();
    }
}

$stmt = $conn->prepare(
    'SELECT
  daypart,
  docent1,
  docent2,
  klas1jaar,
  klas1niveau,
  klas1nummer,
  lokaal1,
  lokaal2,
  laptops,
  projectCode,
  notes,
  USER,
  TIME,
  IP,
  ID
  FROM week'
);

$stmt->execute();

$stmt->bind_result(
    $resDaypart,
    $resDocent1,
    $resDocent2,
    $resKlas1Jaar,
    $resKlas1Niveau,
    $resKlas1Nummer,
    $resLokaal1,
    $resLokaal2,
    $resLaptop,
    $resProjectCode,
    $resNote,
    $resUser,
    $resTime,
    $resIP,
    $resID
);
while ($stmt->fetch()) {
    $arr = array();

    $arr['dagdeel'] = $resDaypart;

    $arr['klas'] = "$resKlas1Jaar$resKlas1Niveau$resKlas1Nummer";

    $arr['docent1'] = $resDocent1;
    $arr['docent2'] = $resDocent2;

    $arr['lokaal1'] = $resLokaal1;
    $arr['lokaal2'] = $resLokaal2;

    $arr['laptops'] = $resLaptop;
    $arr['project'] = $resProjectCode;
    $arr['note'] = $resNote;
    $arr['USER'] = $resUser;
    $arr['TIME'] = $resTime;
    $arr['IP'] = $resIP;
    $arr['ID'] = $resID;

    $data[$resDaypart][] = $arr;
}

// var_dump($data);

//5MB is in memory, als het groter word wordt er geschreven naar een temp file
$csv = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');

// var_dump($data);

foreach ($data as $dagdeel) {
    foreach ($dagdeel as $afspraken) {
        fputcsv($csv, $afspraken);
    }
}


rewind($csv);

// put it all in a variable
$out = stream_get_contents($csv);
echo "$out";

//Download headers
header('Content-Type: text/csv');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"planner-export-".date('Y-m-d_H.i.s')."\"");
