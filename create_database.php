<?php
/**fetch variables from data storage and user interface service modules**/
require_once 'data_storage_variables.php';//data storage service
require_once 'user_interface_variables.php';//user interface service

//handle variables
if($users_table_name==''){
	$require_login=FALSE;
}else{
	$require_login=TRUE;
}
$dashboard_select_options=$users_table_name;
if($dashboard_select_options==''&&in_array('select',$dashboard_column_types)){
	$dashboard_column_types=array_replace($dashboard_column_types,
		array_fill_keys(array_keys($dashboard_column_types,'select'),'textfield'));
}
if($project_column_types==[]){
	$require_projects_overview=FALSE;
}else{
	$require_projects_overview=TRUE;
}
if($dashboard_column_types[0]=='date'){//requires a 'date' type as the first column
	$require_notification=TRUE;
}else{
	$require_notification=FALSE;
}

//error handling
if(in_array('?',array_merge($project_column_names,$dashboard_column_names))){
	echo "Error: '?' cannot be a column name!";
	exit();
}
if($require_notification&&!in_array('date',$dashboard_column_types)){
	echo "Error: the notification requires a 'date' field as the first dashboard column!";
	exit();
}
if($require_notification&&$dashboard_column_types[0]!='date'){
	echo "Error: the notification requires a 'date' field as the first dashboard column!";
	exit();
}
if(in_array('fileupload',$dashboard_column_types)&&array_count_values($dashboard_column_types)['fileupload']>1){
	echo "Error: only one 'fileupload' field is allowed to maximise performance!";
	exit();
}
if(in_array('checkbox',$dashboard_column_types)&&array_count_values($dashboard_column_types)['checkbox']>1){
	echo "Error: only one 'checkbox' field is allowed!";
	exit();
}
if(!$require_login&&in_array('select',$dashboard_column_types)){
	echo "Error: options for the 'select' field require a login!";
	exit();
}
if(!$require_projects_overview&&$require_notification){
	echo "Error: notifications require a projects overview!";
	exit();
}
if(!$require_projects_overview&&in_array('select',$dashboard_column_types)&&$dashboard_select_options==['projectmembers']){
	echo "Error: to create select options based on project members, a projects overview is required!";
	exit();
}

/**script**/
//constants
define('DB_SERVER',$database_server);//run on localhost
define('DB_USER',$database_user);
define('DB_PASSWORD',$database_password);
define('DB_NAME',$database_name);
define('REQUIRE_PROJECTS',$require_projects_overview);

//connection
$conn=new mysqli(DB_SERVER,DB_USER,DB_PASSWORD) 
	or die('There was a problem connecting to the server.');
$db_name=DB_NAME;

//check if database exists
$query="SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME='{$db_name}'";
if(empty(mysqli_fetch_assoc($conn->query($query)))){
	//create database
	$query="CREATE DATABASE {$db_name}";//does not overwrite
	if($conn->query($query)===TRUE)echo "Database created successfully.";
	else{echo "Error creating database: ".$conn->error;}
	
	//create users table
	if($require_login){
		$conn=new mysqli(DB_SERVER,DB_USER,DB_PASSWORD,$db_name) 
			or die('There was a problem connecting to the database.');
		$query="CREATE TABLE `{$users_table_name}`(
			`id` int(11) AUTO_INCREMENT PRIMARY KEY,
			`username` varchar(30) NOT NULL,
			`password` varchar(30) NOT NULL,
			`last_login` varchar(100) NOT NULL,
			`admin_rights` BOOLEAN NOT NULL
			)";
		if($stmt=$conn->prepare($query)){
			$stmt->execute();
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
		}
	
		//create admin user
		$query="INSERT INTO `users` (`username`,`password`,`last_login`,`admin_rights`)
			VALUES ('admin','admin','2000-01-01T00:00',1)";
		if($stmt=$conn->prepare($query)){
			$stmt->execute();
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
		}
	}

	//create projects table
	echo $require_projects_overview;
	if($require_projects_overview){
		echo 'X';
		$query="CREATE TABLE `projects`(
			`id` int(11) AUTO_INCREMENT PRIMARY KEY";
		for($i=0;$i<sizeof($project_column_names);$i++){
			$query=$query.",`{$project_column_names[$i]}` varchar(300) NOT NULL";
		}
		$query=$query.")";
		echo $query;
		if($stmt=$conn->prepare($query)){
			$stmt->execute();
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
		}
	}
}
$conn->close();
?>