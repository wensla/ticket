<?php
namespace Lib\Ext\Page\Apply;

use Lib\Controler\Page\PageView as P;
use Lib\Database;
use Lib\Report;
use Lib\Age;
use Lib\Tempelate;
use Lib\Page;
use Lib\Ticket\Ticket;
use Lib\Language\Language;

class PageView implements P{
  public function loginNeeded() : string{
    return "YES";
  }
  
  public function identify() : string{
    return "apply";
  }
  
  public function access() : array{
    return [];
  }
  
  public function body(Tempelate $tempelate, Page $page){
    Language::load("apply");
    if(empty($_GET["to"]) || !($data = $this->data())){
      $this->select_to($tempelate);
      return;
    }
  
    if($data["age"]){
      if(($respons = Age::controle($data["age"], $data["name"])) != Age::NO_ERROR){
        switch($respons){
          case AGE::GET_AGE:
            Age::get_age($data["name"], $tempelate, $page);
            return;
          default:
            header("location: ?view=apply");
            exit;
        }
      }
    }
  
    if(!empty($_GET["done"])){
      $this->controle_apply($data);
    }
    
    $tempelate->put("name",        $data["name"]);
    $tempelate->put("category_id", $data["id"]);
    
    $query = Database::get()->query("SELECT * FROM `category_item` WHERE `cid`='{$data["id"]}'");
    $field = [];
    $save = new SaveInputs($data["id"]);
    while($row = $query->fetch()){
      $data = $row->toArray();
      if($row->type == 3){
        $explode = explode(",", $row->placeholder);
        $placeholder = [];
        for($i=0;$i<count($explode);$i++){
          $placeholder[] = [
            "id"    => $i,
            "value" => trim($explode[$i])
            ];
        }
        $data["placeholder"] = $placeholder;
      }
      $data["saved"] = $save->get($row->id);
      $field[] = $data;
    }
    $save->delete();
    $tempelate->put("field", $field, $page);
    
    $tempelate->render("apply");
  }
  
  private function controle_apply(array $data){
    $db = Database::get();
    $errcount = 0;
    $query = $db->query("SELECT * FROM `category_item` WHERE `cid`='".$data["id"]."'");
    $fields = [];
    $saver = new SaveInputs($data["id"]);
    while($row = $query->fetch()){
      if(!array_key_exists($row->id, $_POST)){
        Report::error(Language::get("A_MISSING", [htmlentities($row->text)]));
        $errcount++;
      }elseif($row->type != 3 && !trim($_POST[$row->id])){
        Report::error(Language::get("A_MISSING", [htmlentities($row->text)]));
        $errcount++;
      }elseif($row->type == 3){
        $count = count(($option = explode(",", $row->placeholder)))-1;
        $value = intval($_POST[$row->id]);
        if($value < 0 || $value > $count){
          Report::error(Language::get("A_MISSING", [htmlentities($row->text)]));
          $errcount++;
        }else{
          $fields[] = [
            "text"  => $row->text,
            "type"  => $row->type,
            "value" => $option[$value]
            ];
          $saver->put($row->id, $value);
        }
      }else{
        $fields[] = [
          "text"  => $row->text,
          "type"  => $row->type,
          "value" => $_POST[$row->id]
          ];
        $saver->put($row->id, $_POST[$row->id]);
      }
    }
    
    if($errcount !== 0 || count($fields) == 0){
      if(count($fields) == 0 && $errcount == 0){
        Report::error(Language::get("EMPTY_TICKET"));
      }
      $saver->save();
      header("location: ?view=apply&to=".$data["id"]);
      exit;
    }else{
      $id = Ticket::createTicket(user["id"], $data["id"], $fields);
      Report::okay(Language::get("TICKET_SAVED"));
      header("location: ?view=tickets&ticket_id=".$id);
      exit;
    }
  }
  
  private function select_to(Tempelate $tempelate){
    if(!empty($_GET["to"])){
      notfound();
      return;
    }
    
    $query = Database::get()->query("SELECT `id`, `name`, `age` FROM `catogory` WHERE `open`='1' ORDER BY `sort_ordre` ASC");
    
    $options = [];
    $count = 0;
    $lastID = 0;
    while($row = $query->fetch()){
      if($row->age && !empty(user["birth_day"])){
        if(Age::calculate(user["birth_day"], user["birth_month"], user["birth_year"]) < $row->age){
         continue; 
        }
      }
      $options[] = [
        "id"   => $row->id,
        "name" => $row->name
        ];
      $lastID = $row->id;
      $count++;
    }
         
    if($count == 0){
      $tempelate->put("apply_error", Language::get("NO_CAT"));
      $tempelate->render("apply_error", $page);
      return;   
    }
    
    if($count == 1){
      header("location: ?view=apply&to=".$lastID);
      exit;
    }
    
    $tempelate->put("category", $options);
    $tempelate->render("select_to");
  }
  
  private function data(){
    $db = Database::get();
    if($data = $db->query("SELECT * FROM `catogory` WHERE `id`='".$db->escape($_GET["to"])."'")->fetch()){
      return $data->toArray();
    }
    return null;
  }
  
  private function sendEamilToAdmin(string $catName){
    $email = new Email();
    $db = Database::get();
    $query = $db->query("SELECT user.username, user.email
                         FROM `user`
                         LEFT JOIN `access` ON user.groupid=access.gid
                         WHERE access.name='TICKET_OTHER'
                         AND user.id<>'".user["id"]."'");
    while($row = $query->fetch()){
      $email->pushArg("username",        $row->username);
      $email->pushArg("ticket_category", $catName);
      $email->send("new_ticket",         $row->email);
    }
  }
}
