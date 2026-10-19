<?php
require __DIR__.'/common.php';
if($_SERVER['REQUEST_METHOD']!=='GET'){header('Allow: GET, OPTIONS');fail('Method not allowed',405);}
try {
 $db=database();$t=$db->query('SELECT * FROM telemetry_data ORDER BY id DESC LIMIT 1')->fetch()?:null;
 $i=$db->query('SELECT * FROM warehouse_inventory ORDER BY id DESC LIMIT 1')->fetch()?:null;
 if($t){$t['temperature']=(float)$t['temperature'];$t['humidity']=(float)$t['humidity'];}
 if($i)foreach(['apple_count','mango_count','orange_count'] as $k)$i[$k]=(int)$i[$k];
 respond(['status'=>'success','telemetry'=>$t,'inventory'=>$i]);
}catch(PDOException $e){error_log((string)$e);fail('Database unavailable',503);}
