<?php
require_once 'create_database.php';
require 'mysql.php';

class membership{
	
	function validate_user($username,$pwd){
		$mysql=new mysql();
		$ensure_credentials=$mysql->verify_user_and_pwd($username,$pwd);
		if($ensure_credentials){
			$_SESSION['status']='authorised';
			REQUIRE_PROJECTS?header('location: projects.php?notification=true'):header('location: dashboard.php?id=1');
		}else return 'Please enter a correct username and password.';
	}
	
	function set_login_time($user){
		$mysql=new mysql();
		$mysql->set_login_time($user);
	}
	
	function log_user_out(){
		if(isset($_SESSION['status'])){
			unset($_SESSION['status']);
			if(isset($_COOKIE[session_name()])){
				setcookie(session_name(),'',time()-1000);
				session_destroy();
			}
		}
	}
	
	function confirm_member(){
		session_start();
		if($_SESSION['status']!='authorised'){
			header('location: login.php');
		}
	}
	
	function user_exists($username){
		$mysql=new mysql();
		$exists=$mysql->user_exists($username);
		if($exists){
			return true;
		}
	}
	
	function register_user($username,$pwd){
		$mysql=new mysql();
		$ensure_credentials=$mysql->register_user($username,$pwd);
		if($ensure_credentials){
			return true;
		}else return 'Error while registering!';
	}
	
	function delete_projects_table(){
		$mysql=new mysql();
		$table_deleted=$mysql->delete_projects_table();
		if($table_deleted){
			return true;
		}else return 'Error while deleting the old table!';
	}
	
	function delete_dashboard($id){
		$mysql=new mysql();
		$table_deleted=$mysql->delete_dashboard($id);
		if($table_deleted){
			return true;
		}else return 'Error while deleting the old table!';
	}
	
	function submit_project($project_column_names,$project){
		$mysql=new mysql();
		$project_submitted=$mysql->submit_project($project_column_names,$project);
		if($project_submitted){
			return true;
		}else return 'Error while submitting project!';
	}
	
	function fetch_projects(){
		$mysql=new mysql();
		$stored_projects=$mysql->fetch_projects();
		return $stored_projects;
	}
	
	function fetch_users(){
		$mysql=new mysql();
		$users=$mysql->fetch_users();
		return $users;
	}
	
	function check_admin_rights($user){
		$mysql=new mysql();
		$admin_rights=$mysql->check_admin_rights($user);
		return $admin_rights;
	}
	
	function fetch_project_members($project_member_names,$id){
		$mysql=new mysql();
		$project_members=$mysql->fetch_project_members($project_member_names,$id);
		return $project_members;
	}
	
	function fetch_last_login($user){
		$mysql=new mysql();
		$last_login=$mysql->fetch_last_login($user);
		return $last_login;
	}
	
	function check_table_existance($id){
		$mysql=new mysql();
		$table_exists=$mysql->check_table_existance($id);
		return $table_exists;
	}
	
	function create_table($id,$dashboard_column_names,$dashboard_column_types){
		$mysql=new mysql();
		$mysql->create_table($id,$dashboard_column_names,$dashboard_column_types);
	}
	
	function fetch_changes($id,$dashboard_column_names,$column_index_fileupload){
		$mysql=new mysql();
		$stored_changes=$mysql->fetch_changes($id,$dashboard_column_names,$column_index_fileupload);
		return $stored_changes;
	}
	
	function fetch_images($id,$image_column_name){
		$mysql=new mysql();
		$stored_images=$mysql->fetch_images($id,$image_column_name);
		return $stored_images;
	}
	
	function delete_change_table($id){
		$mysql=new mysql();
		$table_deleted=$mysql->delete_change_table($id);
		if($table_deleted){
			return true;
		}else return 'Error while deleting the old table!';
	}
	
	function submit_change($id,$dashboard_column_names,$change,$column_index_fileupload){
		$mysql=new mysql();
		$change_submitted=$mysql->submit_change($id,$dashboard_column_names,$change,$column_index_fileupload);
		if($change_submitted){
			return true;
		}else return 'Error while submitting changes!';
	}
	
	function upload_image($id,$image_column_name,$image_data,$current_row){
		$mysql=new mysql();
		$image_uploaded=$mysql->upload_image($id,$image_column_name,$image_data,$current_row);
		if($image_uploaded){
			return true;
		}else return 'Error while uploading the image!';
	}
}
?>