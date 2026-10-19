<?php
declare(strict_types=1);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD']==='OPTIONS') { http_response_code(204); exit; }
function respond($data,int $status=200): never {
 http_response_code($status);echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);exit;
}
function fail(string $message,int $status=400): never { respond(['status'=>'error','message'=>$message],$status); }
function database(): PDO {
 $file=__DIR__.'/../config.php';
 $c=is_file($file)?require $file:require __DIR__.'/../config.example.php';
 foreach(['host'=>'DB_HOST','port'=>'DB_PORT','database'=>'DB_NAME','user'=>'DB_USER','password'=>'DB_PASSWORD'] as $key=>$env) {
  if (getenv($env)!==false) $c[$key]=getenv($env);
 }
 return new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4",$c['user'],$c['password'],[
  PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
}
function body(): array {
 try {$v=json_decode(file_get_contents('php://input'),true,512,JSON_THROW_ON_ERROR);}
 catch (JsonException $e) { fail('Invalid JSON'); }
 if (!is_array($v)||array_is_list($v)) fail('JSON object required');
 return $v;
}
function text_value(array $v,string $key,int $max=100): string {
 if (!isset($v[$key])||!is_scalar($v[$key])||is_bool($v[$key])) fail("$key is required");
 $s=trim((string)$v[$key]);if($s===''||mb_strlen($s)>$max) fail("Invalid $key");return $s;
}
function number_value(array $v,string $key,float $min,float $max,bool $integer=false): string {
 if (!isset($v[$key])||is_bool($v[$key])||!is_numeric($v[$key])) fail("Invalid $key");
 $n=(float)$v[$key];if(!is_finite($n)||$n<$min||$n>$max||($integer&&floor($n)!==$n)) fail("Invalid $key");
 return (string)$v[$key];
}
function resource(string $table,string $pk,array $fields,callable $validate): void {
 try {
  $method=$_SERVER['REQUEST_METHOD'];
  if(!in_array($method,['GET','POST','PUT','DELETE'],true)) {header('Allow: GET, POST, PUT, DELETE, OPTIONS');fail('Method not allowed',405);}
  $db=database();$key=$_GET[$pk]??$_GET['code']??null;
  if($key!==null&&(!is_string($key)||trim($key)==='')) fail('Invalid key');
  if($method==='GET') {
   $sql="SELECT * FROM `$table`";if($key!==null)$sql.=" WHERE `$pk`=?";$sql.=" ORDER BY `$pk`";
   $st=$db->prepare($sql);$st->execute($key!==null?[$key]:[]);respond($st->fetchAll());
  }
  $v=in_array($method,['POST','PUT'],true)?body():[];
  $key=$key??$v[$pk]??null;
  if($method==='DELETE'||$method==='PUT') {
   if(!is_scalar($key)||is_bool($key)||trim((string)$key)==='')fail("$pk is required");
   $st=$db->prepare("SELECT `$pk` FROM `$table` WHERE `$pk`=?");$st->execute([$key]);
   if(!$st->fetch())fail('Record not found',404);
  }
  if($method==='POST'||$method==='PUT') {
   $v=$validate($v,$method);
   if($method==='POST') {
    $names=$fields;if($pk!=='id')array_unshift($names,$pk);
    $cols=implode('`,`',$names);$marks=implode(',',array_fill(0,count($names),'?'));
    $st=$db->prepare("INSERT INTO `$table` (`$cols`) VALUES ($marks)");$st->execute(array_map(fn($k)=>$v[$k],$names));
    respond(['status'=>'ok','message'=>'Insert Data Complete',$pk=>$pk==='id'?$db->lastInsertId():$v[$pk]],201);
   }
   $set=implode(',',array_map(fn($k)=>"`$k`=?",$fields));
   $st=$db->prepare("UPDATE `$table` SET $set WHERE `$pk`=?");$args=array_map(fn($k)=>$v[$k],$fields);$args[]=$key;$st->execute($args);
   respond(['status'=>'ok','message'=>'Update Data Complete']);
  }
  $st=$db->prepare("DELETE FROM `$table` WHERE `$pk`=?");$st->execute([$key]);respond(['status'=>'ok','message'=>'Delete Data Complete']);
 } catch(PDOException $e) {
  $code=$e->errorInfo[1]??0;
  if($code===1062)fail('Duplicate key',409);
  if($code===1451)fail('Record is used by exam_results; remove those results first',409);
  if($code===1452)fail('Student or course does not exist',422);
  error_log((string)$e);fail('Database unavailable or query failed',503);
 }
}
