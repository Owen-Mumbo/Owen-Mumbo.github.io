<?php

$dbserver= "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "task_management_system";
$conn = "";

$conn = mysqli_connect($dbserver,$dbusername,$dbpassword,$dbname);

/*if($conn){
    echo "You are connected!";
}else{
    echo "Connection Error!";
}*/

?>