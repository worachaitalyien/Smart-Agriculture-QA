<?php
require __DIR__.'/input.php';
$v=input_data();$args=[];foreach(['apple','mango','orange'] as $k)$args[]=number_value($v,$k,0,1000000,true);
$db=database();$st=$db->prepare('INSERT INTO warehouse_inventory (apple_count,mango_count,orange_count) VALUES (?,?,?)');$st->execute($args);
respond(['status'=>'success','message'=>'Inventory data saved','id'=>$db->lastInsertId()],201);
