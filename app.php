<?php

//Header
require_once('./template/header.php');
//Config file
require_once("./assets/config/config.php");
$conn = $GLOBALS['conn']= new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

//Cache array
$GLOBALS["cache_array"]=array();

//Query Params
$action=isset($_GET["action"]) ? $_GET["action"] : "view";
$id=isset($_GET["id"]) ? (int) $_GET["id"] : "";
$pid=isset($_GET["pid"]) ? (int) $_GET["pid"] : "";
$pageNo=isset($_GET["page"]) ? (int) $_GET["page"] : 1;
$page_type=isset($_GET["page_type"]) ? $_GET["page_type"] : "home";	

$home_page='<div class="topnav">
  <a class="active" href="#">Home</a>
  <a href="?page_type=people&action=view">People</a>
  <a href="?page_type=films&action=view">Films</a>
  
  <div class="login-container">
    <form id="loginForm" method="post">
      <input type="text" placeholder="Username" name="username" required>
      <input type="password" placeholder="Password" name="password" required>
      <button id="login" type="submit">Login</button>
    </form>
  </div>
  
</div>';


//Validate Config, Query Params and show error   
if(!in_array($page_type,API_TYPE) || !in_array($action,ACTIONS) || (isset($_GET["id"]) && $id==0) || ($pageNo==0) || (isset($_GET["pid"]) && $pid==0))
{
echo $home_page;
	
echo '<div class="page-data" style="padding-left:16px">
  <h2>Invalid Request </h2>
</div>';

require_once('./template/footer.php'); exit;	
}

session_start();
$error="Login to View the Data";
 if($_SERVER["REQUEST_METHOD"] == "POST") {
      // username and password sent from form 
      
      if(isset($_POST['logout']))
      {
		header("location: logout.php");  
	  }
      
      $myusername = $_POST['username'];
      $mypassword = $_POST['password']; 
      
      if(strlen($myusername)>64 || empty($myusername))
      {
		 $error="Your Login Name or Password is invalid"; 
	  } 
      if(strlen($mypassword)>64 || empty($mypassword))
      {
		 $error="Your Login Name or Password is invalid";
	  } 
      $sql = "SELECT id FROM admin WHERE username = '".$myusername."' and password = '".md5($mypassword)."'";
      $result = $conn->query($sql);
      $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
      
      $count = mysqli_num_rows($result);
      		
      if($count == 1) {
         $_SESSION['login_user'] = $myusername;
         
         header("location: ?page_type=people&action=view");
      }else {
         $error = "Your Login Name or Password is invalid";
      }
   }

$people_page='<div class="topnav">
  <a class="active" href="?page_type=people&action=view">People</a>
  <a href="?page_type=films&action=view">Films</a>  
  <div class="login-container">
  <form id="loginForm" method="post">
  <input type="hidden" id="logout" name="logout" value="logout">
      <button id="login" type="submit">Logout</button>
    </form>  
	</div>
	</div>';
	
$films_page='<div class="topnav">
  <a href="?page_type=people&action=view">People</a>
  <a class="active" href="?page_type=films&action=view">Films</a>  
  <div class="login-container">
  <form id="loginForm" method="post">
  <input type="hidden" id="logout" name="logout" value="logout">
      <button id="login" type="submit">Logout</button>
    </form>
	</div>
	</div>';	
	    
$table_size=isset($_GET["pid"]) ? "style='width:25%!important;'" : (($page_type=="films") && !isset($_GET["pid"])) ? "style='width:40% !important;'" : "";
$table_start="<div id='table-data' class='animate-bottom' style='overflow-x:auto;'><table id='table' ".$table_size.">";
$table_head="";
$table_data="";
$table_end="</table></div>";
	
//Prev Page Value
$prev=empty($pageNo) ? 1 : (($pageNo-1)==0) ? "" : $pageNo-1;
$pagination_data="";
$limit=API_LIMIT;

//Final External URL without Page
$urlLink = API_URL.$page_type.'/'.$id;

//Final External URL with specifc page
if ($pageNo && !isset($_GET["pid"])) 
{
	$urlLink.='?page='.$pageNo;
}

//Curl function for getting contents from given URL
function get_data_from_url($url)
{
$ch = curl_init();   
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   
curl_setopt($ch, CURLOPT_URL, $url);   
$res = curl_exec($ch);   
return $res;   
}

//Getting Name or title from Specific URL
function get_data_from_id($url_with_id)
{
	//Get data if new URL for single request
	if(!in_array($url_with_id,array_keys($GLOBALS["cache_array"])))
	{
	$jsonSWID = get_data_from_url($url_with_id);
	if($jsonSWID)
	{
	$jsonSWID = json_decode($jsonSWID);
	//people insert start	
	$urlArray= explode("/",$url_with_id);	
	if($urlArray[4]=="people")
	{
	$people_id = $urlArray[5];	
$conn = $GLOBALS['conn'];		
$sql="SELECT api_id from people as p WHERE api_id = ".$people_id."";

$result = $conn->query($sql);
$rowcount=mysqli_num_rows($result);
if($rowcount == 0)
{
	$headings=array();
	$insert_values=array();
	foreach($jsonSWID as $key => $value)
	{
	if(!in_array($key,$headings) && ($key !='url' && $key !='created' && $key !='edited'))
	{
	$columns[]=$key;	
	}
	$headings[]=$key;
	if (is_array($value))
	{
	  $names_array=array();
	  foreach($value as $url)
	  {	  
      if(!empty($url))
	  {
	  $urlArray= explode("/",$url);	
	  $names_array[]=$urlArray[5];
	  //Insert associations
$sql="SELECT people_api_id from `people_".$key."_assoc` WHERE ".substr($key, 0, -1)."_api_id IN(".$urlArray[5].") AND people_api_id='".$people_id."'";
$result = $conn->query($sql); 
$rowcount=mysqli_num_rows($result);

if($rowcount == 0)
{
$insert = "INSERT INTO `people_".$key."_assoc`(`people_api_id`,`".substr($key, 0, -1)."_api_id`) VALUES ('".$people_id."',$urlArray[5])";
$conn->query($insert);
}	    	  
	  }
	  }

	}
	else if(filter_var($value, FILTER_VALIDATE_URL) && $key !='url') 
	{
	  $name="";	  
	  $name=get_data_from_id($value);
	  $insert_values[]=$name;	
	}
	else if($key !='url' && ($key !='url' && $key !='created' && $key !='edited'))
	{
	$insert_values[]=$value;	
	}
	else if($key =='url')
	{	
	$urlArray= explode("/",$value);		
	$insert_values[]=$urlArray[5];	
	}
		  
   }
$inserted_values=implode('","',$insert_values);   
$insert = "INSERT INTO `people`(`name`, `height`, `mass`, `hair_color`, `skin_color`, `eye_color`, `birth_year`, `gender`, `homeworld`, `api_id`) VALUES (\"".$inserted_values."\")";
$conn->query($insert);	
}	
}	    
	//people insert end	
	if (json_last_error() === JSON_ERROR_NONE && ((isset($jsonSWID->name)) || (isset($jsonSWID->title)))) {
	$name_title = isset($jsonSWID->title) ? $jsonSWID->title : $jsonSWID->name;
	$GLOBALS["cache_array"][$url_with_id]=$name_title;
	return $name_title;
	}
	}
	}
	else{
	return $GLOBALS["cache_array"][$url_with_id];
	}
	return false;
}

//Home Page
if($page_type=='home')
{
echo $home_page;
	
echo '<div class="page-data" style="padding-left:16px">
  <h2>'.$error.'</h2>
</div>';
}

//People page in table
if($page_type=='people' && $action=='view' && !isset($_GET["pid"]))
{
include('session.php');		
echo $people_page;
echo "<div class='wrapper'><h2 style='padding-left:10px;'>".ucfirst($page_type)." list</h2>";

$offset=($pageNo-1)*10;

$sql="SELECT p.name,p.height,p.mass,p.hair_color,p.skin_color,p.eye_color,p.birth_year,p.gender,p.homeworld,
GROUP_CONCAT(DISTINCT pf.film_api_id ORDER BY pf.film_api_id) as films, 
GROUP_CONCAT(DISTINCT psp.specie_api_id ORDER BY psp.specie_api_id) as species,
GROUP_CONCAT(DISTINCT pst.starship_api_id ORDER BY pst.starship_api_id) as starships,
GROUP_CONCAT(DISTINCT pv.vehicle_api_id ORDER BY pv.vehicle_api_id) as vehicles,
p.api_id as pid 
from people as p 
LEFT JOIN people_films_assoc as pf ON pf.people_api_id=p.api_id
LEFT JOIN people_species_assoc as psp ON psp.people_api_id=p.api_id
LEFT JOIN people_starships_assoc as pst ON pst.people_api_id=p.api_id
LEFT JOIN people_vehicles_assoc as pv ON pv.people_api_id=p.api_id 
WHERE p.is_deleted=0
GROUP BY p.api_id ORDER BY p.api_id ASC LIMIT ".API_LIMIT." OFFSET ".$offset."";

$result = $conn->query($sql);
$rowcount=mysqli_num_rows($result);
if($rowcount > 0)
{
$number_of_page = ceil(PAGE_API_TOTAL / $limit);
$next=($pageNo==$number_of_page) ? "" : $pageNo+1;	
$table_head.= "<tr>";
$headings=array();
while($data = mysqli_fetch_assoc($result)){
$type_id=$data['pid'];	
$table_data .="<tr>"; 	
foreach($data as $key => $value)
{	
if(!in_array($key,$headings) && $key !='pid')
{
$heading_name = ucwords(str_replace("_"," ",$key));
$headings[]=$key;		
$table_head.= "<td>".ucwords($heading_name)."</td>";
}
if(($key=='films' || $key=='species' || $key=='starships' || $key=='vehicles') && $key !='pid')
{	
$table_data .= ($value) ? "<td><a href='?page_type={$key}&page={$pageNo}&pid={$type_id}&action=view'><button class='viewData'>View ".ucfirst($key)."</button></a></td>" : "<td>-</td>";	
}
else if($key !='pid')
{			
$table_data.= "<td>".ucwords($value)."</td>";
}
else if($key =='pid')
{	
$table_data .= "<td style='width:10%;'><a style='padding:5px;' href='edit.php?id={$value}&type={$page_type}&action=edit&page={$pageNo}'><i class='fa fa-edit' aria-hidden='true'></i></a> <a style='padding:5px;' href='delete.php?id={$value}&type={$page_type}&action=delete'><i class='fa fa-trash-o' aria-hidden='true'></i></a></td>";
}
}
$table_data .="</tr>"; 
}
$table_head .= "<th>Action</th></tr>";
}
else
{	
$jsonSW = get_data_from_url($urlLink);
if($jsonSW)
{	
$jsonSW = json_decode($jsonSW);
$count=$jsonSW->count;		
// Then put it into JSON format so we can use it
if (json_last_error() === JSON_ERROR_NONE && isset($jsonSW->results)) {
$table_head .= "<tr>";	
$headings=array();
$number_of_page = ceil($count / $limit);
$next=($pageNo==$number_of_page) ? "" : $pageNo+1;
foreach($jsonSW->results as $item){
	$urlArray= explode("/",$item->url);
	$type_id=$urlArray[5];	
	$columns=array();
	$table_data .="<tr>"; 
	$insert_values=array();
    foreach ($item as $key=>$value){
	if($key =='name')
	{
	  $type_name=$value;	
	}	
	if(!in_array($key,$headings) && ($key !='url' && $key !='created' && $key !='edited'))
	{
	$columns[]=$key;	
	$heading_name = ucwords(str_replace("_"," ",$key));		 
	$table_head.= "<th>{$heading_name}</th>";
	}
	$headings[]=$key;
	if (is_array($value))
	{
	  $names_array=array();
	  foreach($value as $url)
	  {	  
      if(!empty($url))
	  {
	  $urlArray= explode("/",$url);	
	  $names_array[]=$urlArray[5];
	  //Insert associations
$sql="SELECT people_api_id from `people_".$key."_assoc` WHERE ".substr($key, 0, -1)."_api_id IN(".$urlArray[5].") AND people_api_id='".$type_id."'";
$result = $conn->query($sql); 
$rowcount=mysqli_num_rows($result);

if($rowcount == 0)
{
$insert = "INSERT INTO `people_".$key."_assoc`(`people_api_id`,`".substr($key, 0, -1)."_api_id`) VALUES ('".$type_id."',$urlArray[5])";
$conn->query($insert);
}	    	  
	  }
	  }
	  $names=implode(",",$names_array);
	  $table_data .= ($names) ? "<td><a href='?page_type={$key}&page={$pageNo}&pid={$type_id}&action=view'><button class='viewData'>View ".ucfirst($key)."</button></a></td>" : "<td>-</td>";

	}
	else if(filter_var($value, FILTER_VALIDATE_URL) && $key !='url') 
	{
	  $name="";	  
	  $name=get_data_from_id($value);
	  $insert_values[]=$name;
	  $table_data .= "<td>".ucwords($name)."</td>";		
	}
	else if($key !='url' && ($key !='url' && $key !='created' && $key !='edited'))
	{
	$insert_values[]=$value;	
	$table_data .= "<td>".ucwords($value)."</td>";
	}
	else if($key =='url')
	{	
	$urlArray= explode("/",$value);		
	$insert_values[]=$urlArray[5];	
	$table_data .= "<td style='width:10%;'><a style='padding:5px;' href='edit.php?id={$urlArray[5]}&type={$page_type}&action=edit&page={$pageNo}'><i class='fa fa-edit' aria-hidden='true'></i></a> <a style='padding:5px;' href='delete.php?id={$urlArray[5]}&type={$page_type}&action=delete'><i class='fa fa-trash-o' aria-hidden='true'></i></a></td>";
	}
		  
   }
$inserted_values=implode('","',$insert_values);   
$insert = "INSERT INTO `people`(`name`, `height`, `mass`, `hair_color`, `skin_color`, `eye_color`, `birth_year`, `gender`, `homeworld`, `api_id`) VALUES (\"".$inserted_values."\")";
$conn->query($insert);
$table_data .= "</tr>";
}
$table_head .= "<th>Action</th></tr>"; 
}
}
}

echo $table_start.$table_head.$table_data.$table_end;


$pagination_start='<div class="wrapper">';
$pagination_end ='</div>';

if($prev){
	$pagination_data .= '<a href="?page_type=people&action=view&page='.$prev.'"><button id="prev">Previous</button></a>';
}
if($next)
{
	$pagination_data .= '<a href="?page_type=people&action=view&page='.$next.'"><button id="next">Next</button></a>';
}
echo $pagination_start.$pagination_data.$pagination_end;	
}

//Films with Character names
if($page_type=='films' && $action=='view' && !isset($_GET["pid"]))
{
include('session.php');	
echo $films_page;
echo "<h2 style='padding-left:10px;'>".ucfirst($page_type)." list</h2>";

$sql="SELECT f.name, GROUP_CONCAT(DISTINCT p.name ORDER BY p.api_id SEPARATOR '<br>') as people, GROUP_CONCAT(DISTINCT p.api_id ORDER BY p.api_id) as pid, f.api_id as fid
from ".$page_type." as f
LEFT JOIN people_".$page_type."_assoc as assoc ON assoc.`".substr($page_type, 0, -1)."_api_id`=f.api_id
LEFT JOIN people as p ON p.api_id=assoc.people_api_id AND p.is_deleted=0
GROUP BY f.api_id ORDER BY f.api_id ASC";

$result = $conn->query($sql);
$rowcount=mysqli_num_rows($result);
if($rowcount==0)
{
$jsonSW = get_data_from_url($urlLink);
if($jsonSW)
{	
$jsonSW = json_decode($jsonSW);
$count=$jsonSW->count;		
// Then put it into JSON format so we can use it
if (json_last_error() === JSON_ERROR_NONE && isset($jsonSW->results)) {
$table_head .= "<tr> <th>Film Name</th> <th>Characters</th> </tr>";
foreach($jsonSW->results as $item){	
	$table_data .="<tr>"; 
    foreach ($item as $key=>$value){
	if($key =='title')
	{
	  $table_data .= "<td>".ucwords($value)."</td>";		
	}
	if($key =='characters')
	{
	$names=array();	
	foreach($value as $people_api){	
	  if(filter_var($people_api, FILTER_VALIDATE_URL)) 
	{	
	  $names[]="<i class='fa fa-user-o' aria-hidden='true'></i> ".ucwords(get_data_from_id($people_api));
	}		
	}
	$name=implode('<br>',$names);
	$table_data .= "<td style='margin-top:5px !important;text-align:left !important;'>{$name}</td>";	
	}
}
$table_data .="</tr>";	
}
}
}
}
/*else if($rowcount == FILMS_API_TOTAL)
{
$table_head .= "<tr> <th>Film Name</th> <th>Characters</th> </tr>";	
while($data = mysqli_fetch_assoc($result))
{
foreach ($data as $key=>$value){
	if($key =='name')
	{
	  $table_data .= "<td>".ucwords($value)."</td>";		
	}
	if($key =='people')
	{
	$name=implode('<br>',$value);
	$table_data .= "<td style='margin-top:5px !important;text-align:left !important;'>{$name}</td>";	
	}
}
$table_data .="</tr>";	
}
} */
else if($rowcount > 0)
{	
while($data = mysqli_fetch_assoc($result))
{
$pids[]=$data['pid'];
$fids[]=$data['fid'];
$pnamez = explode('<br>',$data['people']);
$pidz=explode(',',$data['pid']);
foreach($pnamez as $key=>$val)
{
$people_array[$pidz[$key]]=$val;	
}	
}		
$jsonSW = get_data_from_url($urlLink);
if($jsonSW)
{	
$jsonSW = json_decode($jsonSW);
$count=$jsonSW->count;		
// Then put it into JSON format so we can use it
if (json_last_error() === JSON_ERROR_NONE && isset($jsonSW->results)) {
$table_head .= "<tr> <th>Film Name</th> <th>Characters</th> </tr>";
foreach($jsonSW->results as $item){
	$urlArray= explode("/",$item->url);
	$filmid=$urlArray[5];			
	$table_data .="<tr>"; 
	if(!in_array($filmid,$fids))
	{		
	$film_name=$item->title;

$insert = "INSERT INTO `films`(`api_id`,`name`) VALUES ('".$filmid."','".$film_name."')";
$conn->query($insert);
}	
    foreach ($item as $key=>$value){
	if($key =='title')
	{
	  $table_data .= "<td>".ucwords($value)."</td>";		
	}
	if($key =='characters')
	{
	$names=array();	
	foreach($value as $people_api){
	$urlArray= explode("/",$people_api);
	$people_id=$urlArray[5];	
	if(isset($people_array[$people_id]))
	{
	   $names[]="<i class='fa fa-user-o' aria-hidden='true'></i> ".ucwords($people_array[$people_id]);
	} 		
	else if(filter_var($people_api, FILTER_VALIDATE_URL)) 
	{	
	  $names[]="<i class='fa fa-user-o' aria-hidden='true'></i> ".ucwords(get_data_from_id($people_api));
	  //Insert associations
	$sql="SELECT people_api_id from `people_films_assoc` WHERE film_api_id = '".$filmid."' AND people_api_id='".$people_id."'";
	$result = $conn->query($sql); 
	$rowcount=mysqli_num_rows($result);

	if($rowcount == 0)
	{
	$insert = "INSERT INTO `people_films_assoc`(`people_api_id`,`film_api_id`) VALUES ('".$people_id."','".$filmid."')";
if ($conn->query($insert) === TRUE) {
  echo "New record created successfully";
} else {
  echo "Error: " . $insert . "<br>" . $conn->error;
}
	}	  
	}		
	}
	$name=implode('<br>',$names);
	$table_data .= "<td style='margin-top:5px !important;text-align:left !important;'>{$name}</td>";	
	}
}
$table_data .="</tr>";	
}
}
}	
}
echo $table_start.$table_head.$table_data.$table_end;
}
//People associate data in table
if(!empty($pid) && $action=='view')
{
include('session.php');	
echo $people_page;

$sql="SELECT p.name, GROUP_CONCAT(DISTINCT assoc.".substr($page_type, 0, -1)."_api_id ORDER BY assoc.".substr($page_type, 0, -1)."_api_id) as ".$page_type."
from people as p 
JOIN people_".$page_type."_assoc as assoc ON assoc.people_api_id='".$pid."'
WHERE p.is_deleted=0 AND p.api_id='".$pid."' GROUP BY p.api_id ORDER BY p.api_id ASC";

$result = $conn->query($sql);
$rowcount=mysqli_num_rows($result);
if($rowcount > 0)
{
$data = mysqli_fetch_assoc($result);

echo "<h2 style='padding-left:10px;'>".ucfirst($page_type)." of ".$data['name']."</h2>";
$ids_array=explode(",",$data[$page_type]);
$table_head_name = ($page_type=='films') ? "Title" : "Name";
$table_head .= "<tr> <th>".ucfirst($page_type)." Id</th> <th>{$table_head_name}</th> </tr>";

$sql="SELECT api_id as id,name from ".$page_type." WHERE api_id IN($data[$page_type])";
$result = $conn->query($sql);
$rowcount=mysqli_num_rows($result);

if($rowcount > 0)
{
while($data = mysqli_fetch_assoc($result)){
$id_val = $data['id'];
$name = $data['name'];		
$table_data .= "<tr><td>{$id_val}</td> <td>".ucwords($name)."</td></tr>";		
}
echo $table_start.$table_head.$table_data.$table_end;
echo "<a href='?page_type=people&action=view&page=".$_GET['page']."'><button id='next'>Back to People</button></a>";	
}
else 
{	
foreach($ids_array as $id_val)
{
$sql="SELECT api_id as id,name from ".$page_type." WHERE api_id IN(".$id_val.")";
$result = $conn->query($sql);
$rowcount=mysqli_num_rows($result);

if($rowcount > 0)
{
$data = mysqli_fetch_assoc($result);
$id_val = $data['id'];
$name = $data['name'];		
$table_data .= "<tr><td>{$id_val}</td> <td>".ucwords($name)."</td></tr>";
}		
else
{
$name = get_data_from_id($urlLink.$id_val);
//Type name
$insert = "INSERT INTO `".$page_type."`(`name`,`api_id`) VALUES ('".$name."','".$id_val."')";
$conn->query($insert);
$table_data .= "<tr><td>{$id_val}</td> <td>".ucwords($name)."</td></tr>";		
}
}
echo $table_start.$table_head.$table_data.$table_end;
echo "<a href='?page_type=people&action=view&page=".$_GET['page']."'><button id='next'>Back to People</button></a>";	
}
}
}

require_once('./template/footer.php');
