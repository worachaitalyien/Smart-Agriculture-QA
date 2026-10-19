<?php
require __DIR__.'/input.php';
$v=input_data();$temp=number_value($v,'temperature',-40,125);$humidity=number_value($v,'humidity',0,100);
$db=database();$st=$db->prepare('INSERT INTO telemetry_data (temperature,humidity) VALUES (?,?)');$st->execute([$temp,$humidity]);
respond(['status'=>'success','message'=>'Telemetry data saved','id'=>$db->lastInsertId()],201);
