<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">	
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="assets/css/style.css">
<?php $value = isset($_GET['page_type']) ? $_GET['page_type'] : $_GET['action']; ?>
<title>Star Wars - <?php echo ucfirst($value);?></title>
</head>
<body>
