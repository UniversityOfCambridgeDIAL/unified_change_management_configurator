<?php
require_once 'membership.php';
$membership=new membership();
if($require_login)$membership->confirm_member();//redirects unauthorised users if a login exists

//save projects to database
if(isset($_POST['save_to_database'])){
	$table_deleted=$membership->delete_projects_table();
	if(isset($_POST[str_replace(' ','_','"'.$project_column_names[0])])){
		$number_of_rows=sizeof($_POST[str_replace(' ','_','"'.$project_column_names[0])]);
		for($i=0;$i<$number_of_rows;$i++){//for each project
			for($j=0;$j<sizeof($project_column_names);$j++){//for each column
				$project[$j]=$_POST[str_replace(' ','_','"'.$project_column_names[$j])][$i];
			}
			$project_submitted=$membership->submit_project($project_column_names,$project);
			if($project_submitted)$response="All projects have been successfully stored.";
		}
	}else{
		$response="There are no projects that can be stored.";
	}
}

//remove projects from database
if(isset($_COOKIE['dashboard_id'])&&$_COOKIE['dashboard_id']!=0){
	$dashboard_deleted=$membership->delete_dashboard($_COOKIE['dashboard_id']);
	if($dashboard_deleted) $response="The dashboard of project {$_COOKIE['dashboard_id']} has been removed.";
}

//fetch stored projects from database
$stored_projects=$membership->fetch_projects();

//fetch all users
$users=$membership->fetch_users();

if($require_login){
	//check admin rights of current user
	$admin_rights=$membership->check_admin_rights($_SESSION['username'])['admin_rights'];

	//notification
	if($require_notification){
		$notification=$_GET['notification'];
		if($notification=="true"){//notify on first log in
			$last_login=$membership->fetch_last_login($_SESSION['username'])['last_login'];
			if(!empty($stored_projects)){//if there are projects
				$message='New changes in:\n';//initialise notification message
				$changes=false;//flags if there are any new changes
				for($j=0;$j<count($stored_projects);$j++){//for each project
					if($admin_rights==0){//identify if the non-admin user is part of the project
						$project_members=array_slice($stored_projects[$j],3);
						if(!in_array($_SESSION['username'],$project_members))continue;
					}
					$dashboard_id=$stored_projects[$j]['id'];
					$table_exists=$membership->check_table_existance($dashboard_id);
					if($table_exists){//if dashboard exists
						$column_index_fileupload=null;
						$includes_fileupload=in_array('fileupload',$dashboard_column_types);
						if($includes_fileupload)$column_index_fileupload=array_search('fileupload',$dashboard_column_types);
						$stored_changes=$membership->fetch_changes($dashboard_id,$dashboard_column_names,$column_index_fileupload);
						if(!empty($stored_changes)){//if dashboard table is not empty
							$most_recent_change=end($stored_changes)[$dashboard_column_names[0]];//date is the first column
							$d0=new DateTime($last_login);
							$d1=new DateTime($most_recent_change);
							if($d0<$d1){//if last login is older than most recent change
								$changes=true;//there have been new changes
								$message.="Project {$dashboard_id}";
								$message.='\n';
							}
						}
					}
				}
				if($changes)echo "<script type='text/javascript'>confirm('{$message}')</script>";
			}
			$membership->set_login_time($_SESSION['username']);//set new login time
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<!--head-->
	<title><?php echo $application_name?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="background.css">
</head>
<body>
	<!--navigation-->
	<div id="nav" class="topnav">
	    <img class="logo" src="logo_dcs.jpg" alt="Logo">
		<script type="text/javascript">
			var require_login=<?php echo json_encode($require_login);?>;
			if(require_login){//only add logout link if a login is required
				var a=document.createElement('a');
				a.appendChild(document.createTextNode('Logout'));
				a.href='login.php?status=loggedout';
				a.classList.add('navbar-logout');
				document.getElementById('nav').appendChild(a);
			}
			var require_projects_overview=<?php echo json_encode($require_projects_overview);?>;
			if(require_projects_overview){//only add projects link if it is required
				var a=document.createElement('a');
				a.appendChild(document.createTextNode('Projects'));
				a.href='projects.php?notification=false';
				a.classList.add('navbar-projects');
				document.getElementById('nav').appendChild(a);
			}
		</script>
	</div>
	<h1><?php echo $application_name?></h1>
	
	<!--projects-->
	<h2>List of Projects</h2>
	<?php if($require_login)echo "<p>User: ".$_SESSION['username'];?>
	<link rel="stylesheet" href="projects.css">
	<link rel="stylesheet" href="select.css">
	<form id="form" action="" method="POST">
		<!--table-->
		<div class="container">
			<div class="row">
				<div class="col-sm-6">
					<table id="project_table" class="table table-bordered">
						<thead>
							<tr id="header_row">
								<th></th><!--empty because of open button-->
								<script type="text/javascript">
									var project_column_names=<?php echo json_encode($project_column_names);?>;
									var header_row=document.getElementById('header_row');
									for(var i=0;i<project_column_names.length;i++){
										header_row.appendChild(document.createElement("th")).
											appendChild(document.createTextNode(project_column_names[i]));
									}
								</script>
							</tr>
						</thead>
						<tbody id="tbody"></tbody>
					</table>
				</div>
			</div>
		</div>
		
		<!--buttons-->
		<div class="container">
			<button type="button" id="add" class="button_add" onclick="add_project('tbody')">+ Add</button>
			<button type="submit" id="submit" class="button_submit" name="save_to_database">Save</button>
		</div>
	</form>
	
	<!--scripts-->
	<script src="projects.js"></script>
	<script type="text/javascript">
		//initialise
		document.cookie="dashboard_id=0";
		var id;
		var array=<?php echo json_encode($stored_projects);?>;
		var users=<?php echo $require_login?json_encode($users):json_encode(null);?>;
		var current_user=<?php echo $require_login?json_encode($_SESSION['username']):json_encode(null);?>;
		var admin_rights=<?php echo $require_login?json_encode($admin_rights):json_encode(1);?>;//everyone is admin if a login is not required
		var project_column_names=<?php echo json_encode($project_column_names);?>;
		var project_column_types=<?php echo json_encode($project_column_types);?>;
		//var project_member_names=<?php //echo json_encode($project_member_names);?>;

		//create the project table
		create_project_table('tbody');
	</script>
	<?php if(isset($response))echo "<h4 class='alert'>".$response;?>
</body>
</html>