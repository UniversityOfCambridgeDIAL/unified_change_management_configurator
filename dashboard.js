function create_change_table(){
	//create table and display database content
	var table_ref=document.getElementById('tbody');
	for(var i=0;i<=changes.length-1;i++){//for all changes
		//add a new row
		var new_row=table_ref.insertRow(-1);

		//check change involvement of current user
		if(admin_rights==0){
			document.getElementById("export").style.visibility="hidden";//hide buttons if ther user has no admin rights
			document.getElementById("download").style.visibility="hidden";
		}
		opt=0;//counter to loop over select options array
		for(var n=0;n<dashboard_column_types.length;n++){
			type=dashboard_column_types[n];
			//console.log(type);
			if(type=='date')create_date(new_row,n,'"'+dashboard_column_names[n]+'[]'+'"',changes[i][dashboard_column_names[n]]);
			if(type=='textfield')create_text_field(new_row,n,"300",'"'+dashboard_column_names[n]+'[]'+'"',changes[i][dashboard_column_names[n]],false);
			if(type=='fileupload')create_upload_image_field(new_row,n,i,image_column_occupancy[i],true);
			if(type=='select'){
				if(dashboard_select_options[opt]=='users'||(dashboard_select_options[opt]=='projectmembers'&&project_members!=null)){
					if(dashboard_select_options[opt]=='users')create_select(new_row,n,'"'+dashboard_column_names[n]+'[]'+'"',users,changes[i][dashboard_column_names[n]]);
					if(dashboard_select_options[opt]=='projectmembers')create_select(new_row,n,'"'+dashboard_column_names[n]+'[]'+'"',project_members,changes[i][dashboard_column_names[n]]);
				}else{
					create_text_field(new_row,n,"300",'"'+dashboard_column_names[n]+'[]'+'"',changes[i][dashboard_column_names[n]],false);
				}
				opt++;//update counter
			}
			if(type=='checkbox')create_checkbox(new_row,n,'`'+dashboard_column_names[n]+`[${i}]`+'`',changes[i][dashboard_column_names[n]]);
		}
		create_button(new_row,n,null,"button_remove","- Remove","remove_change(this)");
	}
}

function create_upload_image_field(row,idx,row_count,image_column_occupancy,modifier_is_current_user_or_admin){
	//create an "upload image" field or a link to an existing image
	if(image_column_occupancy!=1){//if there is no image
		if(modifier_is_current_user_or_admin){//if the user is the admin or the modifier
			var cell=row.insertCell(idx);
			var element=document.createElement('input');
			element.setAttribute('type','file');
			element.setAttribute('name',`image_file_${row_count+1}`);//helps to identify which row needs the image data
			cell.appendChild(element);
			var element=document.createElement('input');
			element.setAttribute('type','submit');
			element.setAttribute('name',`${row_count+1}`);//helps to identify which row needs the image data
			element.setAttribute('value','Upload');
			cell.appendChild(element);
		}else{
			var cell=row.insertCell(idx);
			var text=document.createTextNode('No image');
			console.log(text);
			cell.appendChild(text);
		}
	}else{
		var cell=row.insertCell(idx);
		var element=document.createElement('a');
		element.addEventListener('click',function(){
			
			///*NEW CODE HERE*///
			event.preventDefault();//prevent reloading the page
			var url=window.location.href;
			var stored_image;
			var formdata=new FormData();
			formdata.append('row_of_image',row_count);
			fetch(url,{method:'POST',body:formdata})
				.then(function(response){
					return response.text();
				})
				.then(function(body){
					stored_image=body.slice(0,body.indexOf('ENDOFIMAGE'));
					let image=new Image();
					image.src='data:image/png;base64,'+stored_image;
					setTimeout(function(){
						let w=window.open('about:blank');
						w.document.write(image.outerHTML);
						},0);
				});
			});
			///*END*///
			/*
			let w=window.open('about:blank');
			let image=new Image();
			image.src='data:image/png;base64,'+stored_images[row_count];
			setTimeout(function(){
				w.document.write(image.outerHTML);
				},0);
			});
			*/
			
		element.href="";//for the style
		var text=document.createTextNode('Open image');
		element.appendChild(text);
		cell.appendChild(element);
	}
}

function create_date(row,idx,name,value){
	//create a datetime-local node
	var cell=row.insertCell(idx);
	var element=document.createElement('input');
	element.setAttribute("name",name);
	element.setAttribute("type","datetime-local");//date
	element.setAttribute("value",value);
	cell.appendChild(element);
}

function create_checkbox(row,idx,name,value){
	//create a checkbox for closing the issue
	var cell=row.insertCell(idx);
	var element=document.createElement('input');
	element.setAttribute("name",name);
	element.setAttribute("type","checkbox");
	element.setAttribute("onclick","set_date_automatically(this)");
	element.checked=value;
	cell.appendChild(element);
}

function set_date_automatically(node){
	//set issue closed date if the checkbox is checked
	/**
	var date=new Date();
	date.setMinutes(date.getMinutes()-date.getTimezoneOffset());
	node.parentNode//get parent node
		.previousSibling//move to previous table cell
		.firstChild//access date input
		.setAttribute("value",date.toISOString().slice(0,16));//set value
	**/
	document.getElementById("submit").click();//auto-save
}

function add_change(table){
	//create new table row
	var table_ref=document.getElementById(table);
	var new_row=table_ref.insertRow(-1);
	
	//add empty columns
	opt=0;//counter to loop over select options array
	auto_date=true;
	for(var j=0;j<dashboard_column_types.length;j++){
		type=dashboard_column_types[j];
		//console.log(type);
		if(type=='date'){
			if(auto_date==true){
				var date=new Date();
				date.setMinutes(date.getMinutes()-date.getTimezoneOffset());
				date=date.toISOString().slice(0,16)
				auto_date=false;//only set first date of the row automatically
			}else{
				date="";
			}
			create_date(new_row,j,'"'+dashboard_column_names[j]+'[]'+'"',date);
		}
		if(type=='textfield')create_text_field(new_row,j,"300",'"'+dashboard_column_names[j]+'[]'+'"',"");
		if(type=='fileupload')create_upload_image_field(new_row,j,table_ref.rows.length,0,true);
		if(type=='select'){
			if(dashboard_select_options[opt]=='users'||(dashboard_select_options[opt]=='projectmembers'&&project_members!=null)){
				if(dashboard_select_options[opt]=='users')create_select(new_row,j,'"'+dashboard_column_names[j]+'[]'+'"',users,null);
				if(dashboard_select_options[opt]=='projectmembers')create_select(new_row,j,'"'+dashboard_column_names[j]+'[]'+'"',project_members,null);
			}else{
				create_text_field(new_row,j,"300",'"'+dashboard_column_names[j]+'[]'+'"',"");
			}
			opt++;//update counter
		}
		if(type=='checkbox')create_checkbox(new_row,j,'`'+dashboard_column_names[j]+`[${table_ref.rows.length}]`+'`',0);
	}
	create_button(new_row,j,null,"button_remove","- Remove","remove_change(this)");
	document.getElementById("submit").click();//auto-save
}

window.remove_change=function remove_change(node){
	//remove table row
	var result=confirm("Are you sure?");
	if(result){
		var p=node.parentNode.parentNode;
		p.parentNode.removeChild(p);
	}
	document.getElementById("submit").click();//auto-save
}