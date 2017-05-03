<?php
function two_container(string $first, string $two, array $options = []) : string{
  $tag = !empty($options["tag"]) ? $option["tag"] : "span";
  $tag2class = !empty($options["tag2class"]) ? " class='".$options["tag2class"]."'" : "";
  return "<div class='two_container'><{$tag}>{$first}</{$tag}><{$tag}{$tag2class}>{$two}</{$tag}></div>";
}

function html_okay(string $message){
  if(empty($_SESSION["okay"])){
    $_SESSION["okay"] = [];
  }
  $_SESSION["okay"][] = $message;
}

function html_error(string $message){
  if(empty($_SESSION["error"])){
    $_SESSION["error"] = [];
  }
  $_SESSION["error"][] = $message;
}

function html_error_count(){
  return empty($_SESSION["error"]) ? 0 : count($_SESSION["error"]);
}

function getHtmlError(){
  if(!empty($_SESSION["error"])){
    echo "<div class='msg error'>";
    foreach($_SESSION["error"] as $error){
      echo "<div>{$error}</div>";
    }
    echo "</div>";
    unset($_SESSION["error"]);
  }
}

function getHtmlOkay(){
  if(!empty($_SESSION["okay"])){
    echo "<div class='msg okay'>";
    foreach($_SESSION["okay"] as $okay)
      echo "<div>{$okay}</div>";
    echo "</div>";
    unset($_SESSION["okay"]);
  }
}

function tickets_overview_open(bool $isOther){
  ?>
   <table class='ticket_overview'>
     <tr>
       <th></th>
       <th>Category</th><?php if($isOther){?>
       <th>From</th>
       <?php } ?><th>Created</th>
     </tr>
  <?php
}

function tickets_overview_context(array $data, bool $isOther){
  ?>
     <tr>
       <td class='<?php echo (strtotime($data["visit"]) < strtotime($data["changed"]) ? "unread" : "read"); ?>'></td>
       <td><a href='?view=ticket&ticket_id=<?php echo $data["id"];?>'><?php echo htmlentities($data["name"]);?></a></td><?php if($isOther){ ?>
       <td><?php echo $data["username"]; ?></td>
       <?php } ?><td><?php echo $data["created"]; ?>
     </tr>
  <?php
}

function tickets_overview_close(){
  echo "</table>";
}
