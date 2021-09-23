<?php
   session_start();
   
   if(session_destroy()) {
      header("Location: app.php?page_type=home");
   }
?>
