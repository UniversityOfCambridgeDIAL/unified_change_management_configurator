<?php
require_once 'membership.php';
$membership=new membership();
if($require_login)$membership->confirm_member();//redirects unauthorised users if a login exists

//save changes to database
$id=$_GET['id'];
$column_index_fileupload=null;
$includes_fileupload=in_array('fileupload',$dashboard_column_types);
if($includes_fileupload)$column_index_fileupload=array_search('fileupload',$dashboard_column_types);
if(isset($_POST['save_to_database'])){
	//create temporary copy of the image column if there is an image upload field
	if($includes_fileupload){
		$image_column_name=$dashboard_column_names[$column_index_fileupload];
		$stored_images=$membership->fetch_images($id,$image_column_name);
	}
	
	//save changes
	$change_table_deleted=$membership->delete_change_table($id);//reset auto-id increment
	if(isset($_POST[str_replace(' ','_','%22'.$dashboard_column_names[0])])){
		$number_of_rows=sizeof($_POST[str_replace(' ','_','%22'.$dashboard_column_names[0])]);
		$checkbox_on=array_fill(0,$number_of_rows,0);
		for($i=0;$i<$number_of_rows;$i++){//for each change
			//get checkbox occupancy if there is a checkbox
			if(in_array('checkbox',$dashboard_column_types)){
				$column_index_checkbox=array_search('checkbox',$dashboard_column_types);
				$checkbox_column_name=$dashboard_column_names[$column_index_checkbox];
				if(isset($_POST[str_replace(' ','_','`'.$dashboard_column_names[$column_index_checkbox])][$i])){
					if($_POST[str_replace(' ','_','`'.$dashboard_column_names[$column_index_checkbox])][$i]=="on"){
						$checkbox_on[$i]=1;
					}
				}
			}
			for($j=0;$j<sizeof($dashboard_column_names);$j++){//for each column
				if(in_array('checkbox',$dashboard_column_types)&&$dashboard_column_names[$j]==$dashboard_column_names[$column_index_checkbox]){
					$change[$j]=$checkbox_on[$i];
				}else{
					if(in_array('fileupload',$dashboard_column_types)&&$dashboard_column_names[$j]==$dashboard_column_names[$column_index_fileupload]){
						continue;
					}else{
						$change[$j]=$_POST[str_replace(' ','_','%22'.$dashboard_column_names[$j])][$i];
					}
				}
			}
			$change_submitted=$membership->submit_change($id,$dashboard_column_names,$change,$column_index_fileupload);
			if($includes_fileupload&&!empty($stored_images[$i][$image_column_name])){
				$image_submitted=$membership->upload_image($id,$image_column_name,$stored_images[$i][$image_column_name],$i+1);//deal with images separately
			}
		}
		if($change_submitted)$response="All changes have been stored successfully.";
	}else{
		$response="There are no changes that can be stored.";
	}
}

//create new table, if it does not exist
$table_exists=$membership->check_table_existance($id);
if(!$table_exists){
	$membership->create_table($id,$dashboard_column_names,$dashboard_column_types);
}

//fetch stored changes from database
$stored_changes=$membership->fetch_changes($id,$dashboard_column_names,$column_index_fileupload);

//check admin rights of current user
if($require_login)$admin_rights=$membership->check_admin_rights($_SESSION['username'])['admin_rights'];

//fetch project members
$users=$membership->fetch_users();
if($require_login)$project_members=$users;
/*
if($require_projects_overview){
	$project_members=$membership->fetch_project_members($project_member_names,$id);
	$i=0;
	foreach(array_unique($project_members) as $member){
		$array[$i]['username']=$member;
		$i++;
	}
	$project_members=$array;
}
$users=$membership->fetch_users();
if($require_login&&!$require_projects_overview)$project_members=$users;//take all users as select options if there are no projects
*/

//export to csv
if(isset($_POST['export_to_csv'])){
	header('Content-Type:application/csv');
    header('Content-Disposition:attachment;filename="dashboard.csv";');
    $f=fopen('php://output','w');
    foreach($stored_changes as $line){
        fputcsv($f,$line,";");
    }
	fclose($f);
	die();//close connection to the output file
}

//download images
if(isset($_POST['download_images'])){
	if($includes_fileupload){
		$column_index_fileupload=array_search('fileupload',$dashboard_column_types);
		$image_column_name=$dashboard_column_names[$column_index_fileupload];
		$stored_images=$membership->fetch_images($id,$image_column_name);
		$zip=new ZipArchive();
		$zip_name="images.zip";
		if($zip->open($zip_name,ZIPARCHIVE::CREATE|ZIPARCHIVE::OVERWRITE)!==true)echo "Cannot open *.zip for writing.";
		for($j=0;$j<sizeof($stored_changes);$j++){
			if(!empty($stored_images[$j][$image_column_name])){
				$image_name=str_replace(':','_',$stored_changes[$j][$dashboard_column_names[0]]).".jpg";
				$image_content=$stored_images[$j][$image_column_name];
				$zip->addFromString($image_name,$image_content);
			}
		}
		$zip->close();
		header("Content-type:application/zip");
		header("Content-Disposition:attachment;filename=$zip_name");
		header("Pragma:no-cache");
		header("Expires:0");
		readfile($zip_name);
	}
}

//upload image
if($includes_fileupload){
	$column_index_fileupload=array_search('fileupload',$dashboard_column_types);
	$image_column_name=$dashboard_column_names[$column_index_fileupload];
	for($j=1;$j<=sizeof($stored_changes);$j++){//table and button IDs start with 1
		//loop over each row and check which uploads buttons have been pressed
		if(isset($_POST["{$j}"])&&!empty($_FILES["image_file_{$j}"]['name'])){
			$image_data=file_get_contents($_FILES["image_file_{$j}"]['tmp_name']);
			$image_uploaded=$membership->upload_image($id,$image_column_name,$image_data,$j);//pass current row number j
			if($image_uploaded)$response="The image upload was successful.";
		}
	}
}

//convert blob to string array if there is an image upload field
$includes_fileupload=in_array('fileupload',$dashboard_column_types);
if($includes_fileupload){
	$column_index_fileupload=array_search('fileupload',$dashboard_column_types);
	$image_column_name=$dashboard_column_names[$column_index_fileupload];
	$stored_images=$membership->fetch_images($id,$image_column_name);//fetch stored images
	$stored_images_base64=array_fill(0,sizeof($stored_images),'');
	$image_column_occupancy=array_fill(0,sizeof($stored_images),0);
	if(!empty($stored_images)){//if there is at least one change
		for($k=0;$k<sizeof($stored_images);$k++){
			$stored_images_base64[$k]=base64_encode($stored_images[$k][$image_column_name]);
			if($stored_images[$k][$image_column_name]!=null)$image_column_occupancy[$k]=1;//get image column occupancy
		}
	}
}

///*NEW CODE HERE*///
//fetch and print only selected image
if(isset($_POST['row_of_image'])){
	$row_count=$_POST['row_of_image'];
	$stored_image_base64=$stored_images_base64[$row_count];
	echo $stored_image_base64;//ECHO PASSES DATA TO CLIENT DO NOT DELETE
	echo 'ENDOFIMAGE';//ECHO PASSES DATA TO CLIENT DO NOT DELETE
}
///*END*///
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
	
	<!--dashboard-->
	<h2>Dashboard</h2>
	<?php if($require_login)echo "<p>User: ".$_SESSION['username'];?>
	<link rel="stylesheet" href="projects.css">
	<link rel="stylesheet" href="select.css">
	<form action="" method="POST" enctype="multipart/form-data">
		<div id="table_container" class="container">
			<div class="row">
				<div class="col-sm-6">
					<table id="project_table" class="table table-bordered">
						<thead>
							<tr id="header_row">
								<script type="text/javascript">
									var dashboard_column_names=<?php echo json_encode($dashboard_column_names);?>;
									var header_row=document.getElementById('header_row');
									for(var i=0;i<dashboard_column_names.length;i++){
										header_row.appendChild(document.createElement("th")).
											appendChild(document.createTextNode(dashboard_column_names[i]));
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
			<button type="button" id="add" class="button_add" onclick="add_change('tbody')">+ Add</button>
			<button type="submit" id="submit" class="button_submit" name="save_to_database">Save</button>
			<button type="submit" id="export" class="button_export" name="export_to_csv">Export</button>
			<button type="submit" id="download" class="button_download" name="download_images">Images</button>
		</div>
	</form>
	
	<!--scripts-->
	<script src="projects.js"></script>
	<script src="dashboard.js"></script>
	<script type="text/javascript">
		//initialise
		var includes_fileupload=<?php echo json_encode($includes_fileupload);?>;
		if(!includes_fileupload)document.getElementById("download").style.visibility="hidden";
		var changes=<?php echo json_encode($stored_changes);?>;
		var image_column_occupancy=<?php echo in_array('fileupload',$dashboard_column_types)?json_encode($image_column_occupancy):json_encode(null);?>;
		
		///*NEW CODE HERE*///
		//var stored_images=<?php echo in_array('fileupload',$dashboard_column_types)?json_encode($stored_images_base64):json_encode(null);?>;
		///*END*//
		
		var current_user=<?php echo $require_login?json_encode($_SESSION['username']):json_encode(null);?>;
		var admin_rights=<?php echo $require_login?json_encode($admin_rights):json_encode(1);?>;//everyone is admin if a login is not required
		var project_members=<?php echo ($require_login||$require_projects_overview)?json_encode($project_members):json_encode(null);?>;
		var users=<?php echo json_encode($users)?>;
		var dashboard_column_names=<?php echo json_encode($dashboard_column_names);?>;
		var dashboard_column_types=<?php echo json_encode($dashboard_column_types);?>;
		var dashboard_select_options=<?php echo json_encode($dashboard_select_options);?>;
		
		//create the project table
		create_change_table();
	</script>
	<?php if(isset($response)) echo "<h4 class='alert'>".$response;?>
</body>
</html>