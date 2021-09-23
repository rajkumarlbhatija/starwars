<?php
require_once("./assets/config/config.php");
$conn = $GLOBALS['conn']= new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
       if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 
   
   $user_check = $_SESSION['login_user'];
   
   $ses_sql = $conn->query("select username from admin where username = '".$user_check."' ");
   
   $row = mysqli_fetch_array($ses_sql,MYSQLI_ASSOC);
   
   $login_session = $row['username'];
   
   if(!isset($_SESSION['login_user'])){
      header("location:app.php?page_type=home");
      die();
   }
?>
