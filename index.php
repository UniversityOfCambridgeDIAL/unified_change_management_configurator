<?php
require_once 'create_database.php';

/**script invokes the database creation and redirects user based on the selected configuration**/
//configurations: database ...
if($require_login){//... + login + projects + dashboard
	header('location: login.php');
	die();
}
if(!$require_projects_overview&&$require_login){//... + login + dashboard
	header('location: login.php');
	die();
}
if($require_projects_overview&&!$require_login){//... + projects + dashboard
	header('location: projects.php?notification=false');//there are no notifications as there are no users
	die();
}
if(!$require_projects_overview&&!$require_login){//... + dashboard
	header('location: dashboard.php?id=1');
	die();
}
?>