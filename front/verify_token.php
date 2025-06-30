<?php
if (empty($_COOKIE['token'])) {
  echo json_encode(['ok'=>false,'msg'=>'Aucun token']);
  exit;
}
$opts=['http'=>[
  'method'=>'GET',
  'header'=>"Authorization: Bearer {$_COOKIE['token']}\r\n"
]];
$ctx=stream_context_create($opts);
$res=@file_get_contents('http://localhost:3010/verify',false,$ctx);
echo json_encode(['ok'=>$res==='OK','msg'=>$res]);
