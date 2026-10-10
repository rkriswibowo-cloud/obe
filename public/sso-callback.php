<?php
session_start();
$ticket=trim((string)($_GET['ticket']??''));
$ctx=stream_context_create(['http'=>['method'=>'POST','timeout'=>3,'ignore_errors'=>true,'header'=>"Content-Type: application/x-www-form-urlencoded\r\nX-Client-Secret: change-obe-secret\r\n",'content'=>http_build_query(['app'=>'obe','ticket'=>$ticket])]]);
$raw=$ticket!==''?@file_get_contents('http://localhost/platform-auth/exchange',false,$ctx):false;$data=$raw?json_decode($raw,true):null;
if(empty($data['ok'])||empty($data['local_user_id'])){$_SESSION['error']='Tiket SSO tidak valid.';header('Location: login');exit;}
require dirname(__DIR__).'/config/database.php';$db=Database::getInstance();$stmt=$db->prepare('SELECT u.*,r.name role_name FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=? LIMIT 1');$stmt->execute([(int)$data['local_user_id']]);$user=$stmt->fetch();
if(!$user){$_SESSION['error']='Akun lokal OBE tidak ditemukan.';header('Location: login');exit;}
session_regenerate_id(true);$_SESSION['user_id']=$user['id'];$_SESSION['username']=$user['username'];$_SESSION['nama_lengkap']=$user['nama_lengkap'];$_SESSION['role_id']=$user['role_id'];$_SESSION['role']=$user['role_name'];$_SESSION['just_logged_in']=true;header('Location: dashboard');exit;
