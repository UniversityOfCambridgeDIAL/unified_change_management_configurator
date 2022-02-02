<?php
require_once 'create_database.php';

class mysql{
	private $conn;
	
	function __construct(){
		$this->conn=new mysqli(DB_SERVER,DB_USER,DB_PASSWORD,DB_NAME) 
			or die('There was a problem connecting to the database.');
	}
	
	function verify_user_and_pwd($username,$pwd){
		$query='SELECT * FROM users WHERE username=? AND password=? LIMIT 1';
		if($stmt=$this->conn->prepare($query)){
			$stmt->bind_param('ss',$username,$pwd);
			$stmt->execute();
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
		}
	}
	
	function set_login_time($user){
		date_default_timezone_set('Europe/London');
		$t=date('Y-m-d').'T'.date('H:i');
		$query="UPDATE users SET last_login='{$t}' WHERE username='{$user}'";
		if($stmt=$this->conn->prepare($query)){
			$stmt->execute();
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
		}
	}
	
	function user_exists($username){
		$query="SELECT 1 FROM users WHERE username='{$username}'";
		if($stmt=$this->conn->prepare($query)){
			$stmt->execute();
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
		}
	}
	
	function register_user($username,$pwd){
		$query="INSERT INTO `users` (`username`,`password`,`last_login`,`admin_rights`) 
			VALUES (?,?,'2000-01-01T00:00','0')";
		if($stmt=$this->conn->prepare($query)){
			$stmt->bind_param("ss",$username,$pwd);
			$stmt->execute();
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
		}
	}
	
	function delete_projects_table(){
		$query="TRUNCATE TABLE `projects`";
		if($stmt=$this->conn->prepare($query)){
			$stmt->execute();
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
		}
	}
	
	function delete_dashboard($id){
		$query="DROP TABLE `{$id}`";
		if($stmt=$this->conn->prepare($query)){
			$stmt->execute();
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
		}
	}
	
	function submit_project($project_column_names,$project){
		$query="INSERT INTO `projects` (";
		$project_ref[0]='';
		for($i=0;$i<sizeof($project_column_names);$i++){
			$query=$query."`".$project_column_names[$i]."`";
			if($i==sizeof($project_column_names)-1){
				$query=$query.") VALUES (";
			}else{
				$query=$query.",";
			}
			$project_ref[0]=$project_ref[0].'s';//type for bind parameters
			$project_ref[$i+1]=&$project[$i];//pass parameters by reference
		}
		for($j=0;$j<sizeof($project_column_names);$j++){
			$query=$query."?";
			if($j==sizeof($project_column_names)-1){
				$query=$query.")";
			}else{
				$query=$query.",";
			}
		}
		if($stmt=$this->conn->prepare($query)){
			call_user_func_array(array($stmt,'bind_param'),$project_ref);
			$stmt->execute();
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
		}
	}
	
	function fetch_projects(){
		$query="SELECT * FROM projects";
		if($stmt=$this->conn->prepare($query)){
			$stmt->execute();
			$result=$stmt->get_result();
			$array=$result->fetch_all(MYSQLI_ASSOC);
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
			return $array;
		}
	}
	
	function fetch_users(){
		$query="SELECT username FROM users";
		if($stmt=$this->conn->prepare($query)){
			$stmt->execute();
			$result=$stmt->get_result();
			$array=$result->fetch_all(MYSQLI_ASSOC);
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
			return $array;
		}
	}
	
	function check_admin_rights($user){
		$query="SELECT admin_rights FROM users WHERE username=? LIMIT 1";
		if($stmt=$this->conn->prepare($query)){
			$stmt->bind_param("s",$user);
			$stmt->execute();
			$result=$stmt->get_result();
			$admin_rights=$result->fetch_assoc();
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
			return $admin_rights;
		}
	}
	
	function fetch_project_members($project_member_names,$id){
		$query="SELECT ";
		for($i=0;$i<sizeof($project_member_names);$i++){
			$query=$query."`".$project_member_names[$i]."`";
			if($i==sizeof($project_member_names)-1){
				$query=$query." FROM projects WHERE id={$id}";
			}else{
				$query=$query.",";
			}
		}
		if($stmt=$this->conn->prepare($query)){
			$stmt->execute();
			$result=$stmt->get_result();
			$project_members=$result->fetch_assoc();
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
			return $project_members;
		}
	}
	
	function fetch_last_login($user){
		$query="SELECT last_login FROM users WHERE username=? LIMIT 1";
		if($stmt=$this->conn->prepare($query)){
			$stmt->bind_param("s",$user);
			$stmt->execute();
			$result=$stmt->get_result();
			$last_login=$result->fetch_assoc();
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
			return $last_login;
		}
	}
	
	function check_table_existance($id){
		$query="SELECT ID FROM `{$id}`";
		if($stmt=$this->conn->prepare($query)){
			$stmt->execute();
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
			return true;
		}else{
			return false;
		}
	}
	
	function create_table($id,$dashboard_column_names,$dashboard_column_types){
		$query="CREATE TABLE `{$id}`(
			`id` int(11) AUTO_INCREMENT PRIMARY KEY";
		for($i=0;$i<sizeof($dashboard_column_names);$i++){
			$type=$dashboard_column_types[$i];
			if($type=='textfield'||$type=='select'||$type=='date')$query=$query.",`{$dashboard_column_names[$i]}` varchar(300) NOT NULL";
			if($type=='fileupload')$query=$query.",`{$dashboard_column_names[$i]}` mediumblob NOT NULL";
			if($type=='checkbox')$query=$query.",`{$dashboard_column_names[$i]}` BOOLEAN NOT NULL";
		}
		$query=$query.")";
		if($stmt=$this->conn->prepare($query)){
			$stmt->execute();
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
		}
	}
	
	function fetch_changes($id,$dashboard_column_names,$column_index_fileupload){
		$query="SELECT id";
		$length=sizeof($dashboard_column_names);
		for($i=0;$i<$length;$i++){
			if($column_index_fileupload!=null&&$i==$column_index_fileupload)continue;//skip image column if it is a file upload
			$query=$query.", `{$dashboard_column_names[$i]}`";
		}
		$query=$query." FROM `{$id}`";
		if($stmt=$this->conn->prepare($query)){
			$stmt->execute();
			$result=$stmt->get_result();
			$array=$result->fetch_all(MYSQLI_ASSOC);
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
			return $array;
		}
	}
	
	function fetch_images($id,$image_column_name){
		$query="SELECT `{$image_column_name}` FROM `{$id}`";
		if($stmt=$this->conn->prepare($query)){
			$stmt->execute();
			$result=$stmt->get_result();
			$array=$result->fetch_all(MYSQLI_ASSOC);
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
			return $array;
		}
	}
	
	function delete_change_table($id){
		$query="TRUNCATE TABLE `{$id}`";
		if($stmt=$this->conn->prepare($query)){
			$stmt->execute();
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
		}
	}
	
	function submit_change($id,$dashboard_column_names,$change,$column_index_fileupload){
		$query="INSERT INTO `{$id}` (";
		$change_ref[0]='';
		$length=sizeof($dashboard_column_names);
		for($i=0;$i<$length;$i++){
			if($column_index_fileupload!=null&&$i==$column_index_fileupload)continue;//skip image column if it is a file upload
			$query=$query."`".$dashboard_column_names[$i]."`";
			if($i==$length-1){
				$query=$query.") VALUES (";
			}else{
				$query=$query.",";
			}
			$change_ref[0]=$change_ref[0].'s';//type for bind parameters
			$change_ref[$i+1]=&$change[$i];//pass parameters by reference
		}
		if($column_index_fileupload!=null)array_splice($change_ref,$column_index_fileupload+1,0);//remove image reference if it is a fileupload
		for($j=0;$j<$length;$j++){
			if($column_index_fileupload!=null&&$j==$column_index_fileupload)continue;//skip image column if it is a file upload
			$query=$query."?";
			if($j==$length-1){
				$query=$query.")";
			}else{
				$query=$query.",";
			}
		}
		if($stmt=$this->conn->prepare($query)){
			call_user_func_array(array($stmt,'bind_param'),$change_ref);
			$stmt->execute();
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
		}
	}
	
	function upload_image($id,$image_column_name,$image_data,$current_row){
		$query="UPDATE `{$id}` SET `{$image_column_name}`=? WHERE id={$current_row}";//`{$id}` = table id, whereas id = row id
		if($stmt=$this->conn->prepare($query)){
			$stmt->bind_param("s",$image_data);
			$stmt->execute();
			if($stmt->fetch()){
				$stmt->close();
				return true;
			}
		}
	}
}	
?>