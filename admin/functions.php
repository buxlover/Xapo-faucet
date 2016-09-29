<?php
$view["admin_message_html"]="Message from admin";

function hide_key($decriptedKey){
  $length = strlen($decriptedKey);
  $exes = str_repeat("X", $length-4);
  $lastFour = substr($decriptedKey,-4);
  return $exes.$lastFour;
}

function update_password_query(){
  return "update settings set value=? where name='password'";
}

function update_keys_query(){
  return "update settings set value=? where name='xapo_app_id';update settings set value=? where name='xapo_secret_key';";
}

function query_last_week(){
  return "select COALESCE(sum(amount),0) as value from data where date > DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND result=1";
}

function query_last_week_referals(){
  return "select COALESCE(sum(amount),0) as value from data_referals where date > DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND result=1";
}

function query_since_beginning(){
  return "select COALESCE(sum(amount),0) as value from data where result=1";
}

function query_since_beginning_referals(){
  return "select COALESCE(sum(amount),0) as value from data_referals where result=1";
}



?>
