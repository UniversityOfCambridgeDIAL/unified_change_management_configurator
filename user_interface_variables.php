<?php
/**set template variables**/
//framework building block
$application_name='UnifiedChangeManagement';

//login building block
$users_table_name='users';

//project columns building blocks
$project_column_names=['ProjectName', 'ProjectDescription', 'ProjectManager', 'Operator'];
$project_column_types=['textfield', 'textfield', 'select', 'select'];

//dashboard columns building blocks
$dashboard_column_names=['Date', 'Description', 'Image', 'PeopleToNotify', 'Resolved'];
$dashboard_column_types=['date', 'textfield', 'fileupload', 'select', 'checkbox'];
?>