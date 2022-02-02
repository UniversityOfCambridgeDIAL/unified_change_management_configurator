<?php
//session
session_start();
require_once 'membership.php';
$membership=new membership();

//user logout
if(isset($_GET['status'])&&$_GET['status']=='loggedout'){
	$membership->log_user_out();
}

//validate user
if(isset($_POST['submit'])&&!empty($_POST['username'])&&!empty($_POST['pwd'])){
	$response=$membership->validate_user($_POST['username'],$_POST['pwd']);
}

//assign username to session
if(isset($_POST['submit'])){
	$_SESSION['username']=$_POST['username'];
}

//register new user
if(isset($_POST['submit_register'])){
	if(!empty($_POST['username'])&&!empty($_POST['pwd'])){
		$exists=$membership->user_exists($_POST['username']);
		if(!$exists){
			$isRegistered=$membership->register_user($_POST['username'],$_POST['pwd']);
			if($isRegistered)$response="The user has successfully been registered.";
		}else{
			$response="This user does already exist. Please use a different username.";
		}
	}else{
		$response="Please fill in the empty fields.";
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<!--Head-->
	<title><?php echo $application_name?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="background.css">
</head>
<body>
	<!--Navigation-->
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
	
	<!--Login-->
	<h2>Login</h2>
	<link rel="stylesheet" href="login.css">
	<form action="" method="post">
		<div class="container">
			<label for="username"><b>Username</b></label>
			<input type="text" autocomplete="off" placeholder="Enter Username" name="username">
			<label for="pwd"><b>Password</b></label>
			<input type="password" placeholder="Enter Password" name="pwd">
			<div style="flex-direction:row">
				<button type="submit" class="button_login" name="submit">Login</button>
				<button type="submit" class="button_register" name="submit_register" id="SignUp">Register</button>
			</div>
		</div>
	</form>
	<script type="text/javascript">
		document.cookie="dashboard_id=0";//initialise cookie
	</script>
	<?php if(isset($response))echo "<h4 class='alert'>".$response;?>
</body>
</html>