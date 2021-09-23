<?php
require_once('./template/header.php');

//Config file
require_once("./assets/config/config.php");
$conn = new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

$id = (int) $_GET['id']; // get id through query string
$page_type=isset($_GET["page_type"]) ? $_GET["page_type"] : "home";	
$action=isset($_GET["action"]) ? $_GET["action"] : "view";

//Validate Config, Query Params and show error   
if(!in_array($page_type,API_TYPE) || !in_array($action,ACTIONS) || (isset($_GET["id"]) && $id==0))
{
$home_page='<div class="topnav">
  <a class="active" href="#">Home</a>
  <a href="app.php?page_type=people&action=view">People</a>
  <a href="app.php?page_type=films&action=view">Films</a>
  
  <div class="login-container">
    <form id="loginForm" method="post">
      <input type="text" placeholder="Username" name="username">
      <input type="password" placeholder="Password" name="psw">
      <button id="login" type="submit">Login</button>
    </form>
  </div>
  
</div>';
	
echo $home_page;
	
echo '<div class="page-data" style="padding-left:16px">
  <h2>Invalid Request </h2>
</div>';

require_once('./template/footer.php'); exit;	
}

echo $people_page='<div class="topnav">
  <a class="active" href="app.php?page_type=people&action=view">People</a>
  <a href="app.php?page_type=films&action=view">Films</a>  
  <div class="login-container">
  <form id="loginForm" method="post">
  <input type="hidden" id="logout" name="logout" value="logout">
      <button id="login" type="submit">Logout</button>
    </form>  
	</div>
	</div>';

// Soft delete	
 $sql = "UPDATE `people` SET is_deleted=1 WHERE id='".$id."'";
// Since Doing soft delete not deleting associations data


if ($conn->query($sql) === TRUE) {
  echo '<h2 style="padding-left:10px;">Selected People updated successfully </h2>';
} else {
  echo "Error updating record: " . $conn->error;
}	
	

?>
