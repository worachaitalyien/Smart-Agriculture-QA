<?php
require __DIR__.'/common.php';
set_exception_handler(function(Throwable $e){error_log((string)$e);fail('Database unavailable or operation failed',503);});
function input_data():array {
 if($_SERVER['REQUEST_METHOD']!=='POST'){header('Allow: POST, OPTIONS');fail('Method not allowed',405);}
 $type=strtolower(explode(';',$_SERVER['CONTENT_TYPE']??'')[0]);
 if($type==='application/json')return body();
 if(in_array($type,['application/x-www-form-urlencoded','multipart/form-data'],true))return $_POST;
 fail('Use application/json or form data',415);
}
