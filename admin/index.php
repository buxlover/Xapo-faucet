<?php
session_start();

require '../config.php';
require 'functions.php';
require '../functions.php';

try {
  $sql = new PDO($dbdsn, $mysqlUsername, $mysqlPassword, array(PDO::ATTR_PERSISTENT => true,
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
} catch(PDOException $e) {
  die($e->getMessage());
}

if(isset($_POST["action"])){
  if($_POST["action"]=='logout'){
    unset($_SESSION['admin']);
  }
}

if(isset($_POST["password"])){
  $pass = $_POST["password"];
  $pass = encryption($myHashKey,$pass);
  $query = "select * from settings where name='password' and value=?";
  $q = $sql->prepare($query);
  $q->execute(array($pass));
  $res = $q->fetch();
  if($res){
    $_SESSION['admin']=true;
  }
  else{
    $view["admin_message_html"]='<div class="alert alert-danger" role="alert">
  Incorrect password.
    </div>';
    $_SESSION['admin']=false;
  }
}

//check if user is logged in, if not take to login page
if(!isset($_SESSION['admin'])){
  require '../template/login.php';
  die;
}


if(isset($_POST["title"])){
  //update new settings values
  if($_SESSION['admin']){
    $settings = $_POST;
    foreach ($settings as $key => $value) {
      if($key!="password"||$key!="xapo_app_id"||$key!="xapo_secret_key"){
          $query = "update settings set value= ? where name=?;";
          $q = $sql->prepare($query);
          $q->execute(array($value,$key));
          $q->closeCursor();
        }
      }
    //Clear settings array, will be reloaded later
    $settings = Array();
    $view["admin_message_html"]='<div class="alert alert-success" role="alert">Changes made successfully.</div>';
  }
}
else{
  if(isset($_POST["new_password"])){
    if($_SESSION['admin']){
      if($_POST["new_password"]==$_POST["password_confirmation"]){
        $updatePassword =  update_password_query();
        $q = $sql->prepare($updatePassword);
        $new_password = encryption($myHashKey,$_POST["new_password"]);
        $q->execute(array($new_password));
        unset($_SESSION['admin']);
        $view["admin_message_html"]='<div class="alert alert-success" role="alert">Password changed successfully.</div>';
        require 'template/login.php';
        die;
      }
      else{
        $view["admin_message_html"]='<div class="alert alert-danger" role="alert">Password does not match confirmation.</div>';

      }
    }
  }
  else if(isset($_POST["new_xapo_app_id"])){
    if($_SESSION['admin']){
    if($_POST["new_xapo_app_id"]!="" && $_POST["new_xapo_secret_key"]!=""){

      $updateKeys =  update_keys_query();
      $q = $sql->prepare($updateKeys);

      $xapo_key = encryption($myHashKey,$_POST["new_xapo_app_id"]);
      $xapo_secret = encryption($myHashKey,$_POST["new_xapo_secret_key"]);

      $q->execute(array($xapo_key,$xapo_secret));
      $q->closeCursor();
      $view["admin_message_html"]='<div class="alert alert-success" role="alert">Keys changed successfully.</div>';
    }
      else{
        $view["admin_message_html"]='<div class="alert alert-danger" role="alert">Keys can\'t be empty.</div>';
      }
    }
  }
}

$queryGeneralSettings = "select * from settings where name<>'password'";
$resultSettings = $sql->query($queryGeneralSettings);

if ($resultSettings) {
  while ($row = $resultSettings->fetch()) {
    if($row['name']=="xapo_app_id"||$row['name']=="xapo_secret_key"){
      $decriptedKey = trim(decryption($myHashKey,$row['value']));
      $hiddenKey = hide_key($decriptedKey);
      $settings[$row['name']] = $hiddenKey;
    }
    else{
      $settings[$row['name']] = $row['value'];
    }
  }
}

require 'template/index.php';

?>
