<?php
namespace Lib\Ext\Page\Error;

use Lib\Controler\Page\PageView as P;
use Lib\Database;
use Lib\Database\DatabaseFetch;

class PageView implements P{
  function body(){
    $info = new Info();
    if(!$info->menuVisible()){
      notfound();
    }
    
    $query = Database::get()->query("SELECT * FROM `error`");
    
    if($query->count() === 0){
      echo "<h3>No error detected</h3>";
      return;
    }
    
    $table = new \Table();
    $table->className("style");
    $table->newColummen();
    $this->setHeader($table);
    $self = $this;
    $query->render(function($row) use($table, $self){
      $self->setBody($table, $row);
    });
    $table->output();
  }
  
  private function setBody(\Table $table, DatabaseFetch $item){
    $table->newColummen();
    $table->td($item->errno);
    $table->td($item->errstr);
    $table->td($item->errfile);
    $table->td($item->errline);
    $table->td($item->errtime);
  }
  
  private function setHeader(\Table $table){
    $table->th("Type");
    $table->th("Message");
    $table->th("File");
    $table->th("Line");
    $table->th("Reported");
  }
}