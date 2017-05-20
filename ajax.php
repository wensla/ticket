<?php
$ajax = [];

$_SESSION["ajax"] = [];

function is_ajax(){
  return !empty($_GET["_ajax"]);
}

function ajax_var(string $name, $value){
  $_SESSION["ajax"][$name] = $value;
}

function ajax_output(){
  if(is_ajax()){
    switch($_GET["_ajax"]){
      case "update":
        updateAjax($ajax);
      break;
      case "null":break;
      default:
        html_error("Unknown ajax request");
    }
    
    if(html_error_count() != 0){
      ajax_var("error", $_SESSION["error"]);
      unset($_SESSION["error"]);
    }
    
    if(html_okay_count() != 0){
      ajax_var("okay", $_SESSION["okay"]);
      unset($_SESSION["okay"]);
    }
    header('Content-Type: application/json');
    echo json_encode($_SESSION["ajax"]);
    unset($_SESSION["ajax"]);
    exit;
  }
}

function updateAjax(&$ajax){
  if(defined("user")){
    ajax_var("unread_ticket", Lib\Ticket\Track::unread());
    ajax_var("notify", Lib\Ext\Notification\Notification::ajax());
  }
}