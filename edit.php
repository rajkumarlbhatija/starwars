<?php
require_once('./template/header.php');
echo "<style>
tr{background-color: #f2f2f2}
input {
    padding: 6px;
    margin-top: 8px;
    font-size: 17px;
    border: none;
    width: 150px;
}
.error {color: #FF0000; padding:2px;}
</style>";
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
	
// define variables and set to empty values
$name = $nameErr = $height = $heightErr = $mass = $massErr = $hair_color = $hair_colorErr = $skin_color = $skin_colorErr = $eye_color = $eye_colorErr = $birth_year = $birth_yearErr = $gender = $genderErr ="";

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
$error_flag=0;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	
if (empty($_POST["name"])) {
    $nameErr = "Name is required";
    $error_flag=1;
  } 
  else if (!empty($_POST["name"]) && strlen($_POST["name"])>50) {
	  $nameErr = "Enter the Name less than or equal to 50 Characters";
	  $error_flag=1;
  }
  else {
    $name = test_input($_POST["name"]);
  }

if (empty($_POST["height"])) {
    $heightErr = "Height is required";
    $error_flag=1;
  }
   else if (!empty($_POST["height"]) && strlen($_POST["height"])>10) {
	  $heightErr = "Enter the Height less than or equal to 10 Characters";
	  $error_flag=1;
  }
   else {
    $height = test_input($_POST["height"]);
  }
  
  if (empty($_POST["mass"])) {
    $massErr = "Mass is required";
    $error_flag=1;
  }
   else if (!empty($_POST["mass"]) && strlen($_POST["mass"])>10) {
	  $massErr = "Enter the Mass less than or equal to 10 Characters";
	  $error_flag=1;
  }  
   else {
    $mass = test_input($_POST["mass"]);
  }
  
    if (empty($_POST["hair_color"])) {
    $hair_colorErr = "Hair color is required";
    $error_flag=1;
  }
   else if (!empty($_POST["hair_color"]) && strlen($_POST["hair_color"])>50) {
	  $hair_colorErr = "Enter the Hair Color less than or equal to 50 Characters";
	  $error_flag=1;
  }   
   else {
    $hair_color = test_input($_POST["hair_color"]);
  }
  
      if (empty($_POST["skin_color"])) {
    $skin_colorErr = "Skin color is required";
    $error_flag=1;
  }
   else if (!empty($_POST["skin_color"]) && strlen($_POST["skin_color"])>50) {
	  $skin_colorErr = "Enter the Skin Color less than or equal to 50 Characters";
	  $error_flag=1;
  }   
   else {
    $skin_color = test_input($_POST["skin_color"]);
  }
  
        if (empty($_POST["eye_color"])) {
    $eye_colorErr = "Eye color is required";
    $error_flag=1;
  }
   else if (!empty($_POST["eye_color"]) && strlen($_POST["eye_color"])>50) {
	  $eye_colorErr = "Enter the Eye Color less than or equal to 50 Characters";
	  $error_flag=1;
  }  
   else {
    $eye_color = test_input($_POST["eye_color"]);
  }       

        if (empty($_POST["birth_year"])) {
    $birth_yearErr = "Birth_year is required";
    $error_flag=1;
  }
   else if (!empty($_POST["birth_year"]) && strlen($_POST["birth_year"])>10) {
	  $birth_yearErr = "Enter the Birth year less than or equal to 10 Characters";
	  $error_flag=1;
  }  
   else {
    $birth_year = test_input($_POST["birth_year"]);
  }    	

        if (empty($_POST["gender"])) {
    $genderErr = "Gender is required";
    $error_flag=1;
  }
  else if (!empty($_POST["gender"]) && strlen($_POST["gender"])>50) {
	  $genderErr = "Enter the Gender less than or equal to 50 Characters";
	  $error_flag=1;
  }
   else {
    $gender = test_input($_POST["gender"]);
  } 
 
 if($error_flag==0)
 {
 $sql = "UPDATE `people` SET name='".$name."',height='".$height."',mass='".$mass."',hair_color='".$hair_color."',skin_color='".$skin_color."',eye_color='".$eye_color."',birth_year='".$birth_year."',gender='".$gender."' WHERE id='".$id."'";


if ($conn->query($sql) === TRUE) {
	    $error_flag=2;
  echo '<h2 style="padding-left:10px;">'.$name.' updated successfully </h2>';
} else {
  echo "Error updating record: " . $conn->error;
}
}
   
}


if($error_flag!=2) 
{	
	
$sql="SELECT name,height,mass,hair_color,skin_color,eye_color,birth_year,gender from people where api_id IN('".$id."') AND is_deleted=0";

$result = $conn->query($sql);
$rowcount=mysqli_num_rows($result);
if($rowcount > 0)
{

$data = mysqli_fetch_assoc($result);


echo '<h2 style="padding-left:10px;">Update '.$data["name"].'</h2>';


echo'<form method="POST">
<div id="table-data" class="animate-bottom" style="overflow-x:auto;"><table id="table" style="width:50%!important">
<tr><td>
			<label> Name </td> </td>
			<td> <input type="text" name="name" value="'.$data["name"].'" placeholder="Name" maxlength="50" Required>  <span class="error">*</span> </td>  <td class="error">'.$nameErr.'</td>
		</tr>

		<tr> <td>
			<label> Height </td>
			<td> <input type="text" name="height" value="'.$data["height"].'" placeholder="Height" maxlength="10" Required> <span class="error">*</span> </td> <td class="error">'.$heightErr.'</td>
		</tr>

		<tr> <td>
			<label> Mass </td>
			<td> <input type="text" name="mass" value="'.$data["mass"].'" placeholder="Mass" maxlength="10" Required> <span class="error">*</span> </td> <td class="error">'.$massErr.'</td>
		</tr>

		<tr> <td>
			<label> Hair Color </td>
		 <td>	<input type="text" name="hair_color" value="'.$data["hair_color"].'" placeholder="Hair Color" maxlength="50" Required> <span class="error">*</span> </td> <td class="error">'.$hair_colorErr.'</td>
		</tr>

		<tr> <td>
			<label> Skin Color </td>
		<td> <input type="text" name="skin_color" value="'.$data["skin_color"].'" placeholder="Skin Color" maxlength="50" Required> <span class="error">*</span> </td> <td class="error">'.$skin_colorErr.'</td>
		</tr>

		<tr> <td>
			<label> Eye Color </td>
		 <td> <input type="text" name="eye_color" value="'.$data["eye_color"].'" placeholder="Eye Color" maxlength="50" Required> <span class="error">*</span> </td> <td class="error">'.$eye_colorErr.'</td>
		</tr>

		<tr> <td>
			<label> Birth Year </td>
		<td> <input type="text" name="birth_year" value="'.$data["birth_year"].'" placeholder="Birth Year" maxlength="50" Required> <span class="error">*</span> </td>  <td class="error">'.$birth_yearErr.'</td>
		</tr>

		<tr> <td>
			<label> Gender </td>
		 <td> <input type="text" name="gender" value="'.$data["gender"].'" placeholder="Gender" Required> <span class="error">*</span> </td> <td class="error">'.$genderErr.'</td>
		</tr>

		<tr> <td>
		<label> </td>
		 <td> <input style="background-color: #2196F3;color:white;" type="submit" name="update" value="Update"> </td> <td> </td>
		</tr>

</table></div>
</form>';
}
}
echo "<a href='app.php?page_type=people&action=view&page=".$_GET['page']."'><button id='prev'>Back to People</button></a>";
require_once('./template/footer.php');

?>
