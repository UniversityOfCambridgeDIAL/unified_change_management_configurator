function create_project_table(table_body_id){
	//create table and display database content
	var table_ref=document.getElementById(table_body_id);
	for(var i=0;i<=array.length-1;i++){//for all projects
		//set dashboard id
		id=i+1;
		
		//add a new row
		var new_row=table_ref.insertRow(-1);
		
		//get members of current project
		var members=users;//[];
		//for(var k=0;k<project_member_names.length;k++){
		//	members.push(array[i][project_member_names[k]]);
		//}

		//check project involvement of current user
		if(admin_rights==0){
			document.getElementById("add").style.visibility="hidden";//hide buttons if ther user has no admin rights
			document.getElementById("submit").style.visibility="hidden";
			if(0){//!members.includes(current_user)){
				continue;//only display projects of the current user
			}else{//user does not have edit rights
				create_button(new_row,0,id,"button_open","Open","open_project(this)");
				for(var m=0;m<project_column_names.length;m++){
					create_text_field(new_row,m+1,"300",'"'+project_column_names[m]+'[]'+'"',array[i][project_column_names[m]],true);
				}
			}
		}else{//user is admin
			create_button(new_row,0,id,"button_open","Open","open_project(this)");
			for(var n=0;n<project_column_names.length;n++){
				if(require_login){//project_member_names.includes(project_column_names[n])){
					if(project_column_types[n]=='select'){//only use select if it is needed
						create_select(new_row,n+1,'"'+project_column_names[n]+'[]'+'"',users,array[i][project_column_names[n]]);
					}else{
						create_text_field(new_row,n+1,"300",'"'+project_column_names[n]+'[]'+'"',array[i][project_column_names[n]],false);
					}
				}else{
					create_text_field(new_row,n+1,"300",'"'+project_column_names[n]+'[]'+'"',array[i][project_column_names[n]],false);
				}
			}
			create_button(new_row,n+1,id,"button_remove","- Remove","remove_project(this,id)");
		}
	}
}

function create_button(row,idx,id,style,value,event){
	//create a button
	var cell=row.insertCell(idx);
	var element=document.createElement('input');
	element.setAttribute("id",id);
	element.setAttribute("type","button");
	element.setAttribute("class",style);
	element.setAttribute("value",value);
	element.setAttribute("onclick",event);
	cell.appendChild(element);
}

function create_text_field(row,idx,maxlength,name,value,readonly){
	//create a text field
	var cell=row.insertCell(idx);
	var element=document.createElement('textarea');
	element.setAttribute("name",name);
	element.setAttribute("maxlength",maxlength);
	element.setAttribute("cols","18");
	element.setAttribute("rows","6")
	if(maxlength=="50")element.setAttribute("rows","2");
	element.value=value;
	if(readonly==true)element.setAttribute("readonly","readonly");
	cell.appendChild(element);
}

function create_select(row,idx,name,users,value){
	//create a select field
	var cell=row.insertCell(idx);
	var element=document.createElement('select');
	element.setAttribute("name",name);
	populate_drop_down(users,element,value);
	cell.appendChild(element);
}

function populate_drop_down(users,element,select){
	//insert options for the drop down menu
	for(var i=0;i<users.length;i++){
		var option=document.createElement("option");
		option.textContent=users[i].username;
		option.value=users[i].username;
		element.add(option);
		if(select!=null){
			if(option.value==select)element.value=option.value;
		}
	}
	return element;
}

function add_project(table){
	//add table row
	id++;//increment due to new table row
	
	//create new table row
	var table_ref=document.getElementById(table);
	var new_row=table_ref.insertRow(-1);
	create_button(new_row,0,id,"button_open","Open","open_project(this)");
	for(var i=0;i<project_column_names.length;i++){
		if(project_column_types[i]=='select'&&require_login){
			create_select(new_row,i+1,'"'+project_column_names[i]+'[]'+'"',users,null);
		}else{
			create_text_field(new_row,i+1,"300",'"'+project_column_names[i]+'[]'+'"',"");
		}
	}
	create_button(new_row,i+1,id,"button_remove","- Remove","remove_project(this)");
	document.getElementById("submit").click();//auto-save
}

window.remove_project=function remove_project(node){
	//remove table row
	var result=confirm("Are you sure? Removing this project will also delete the dashboard.");
	if(result){
		var p=node.parentNode.parentNode;
		p.parentNode.removeChild(p);
		document.cookie="dashboard_id="+String(node.id);//set new cookie value
	}
	document.getElementById("submit").click();//auto-save
}

function open_project(node){
	//open dashboard
	window.location.assign("dashboard.php?id="+node.id);
}