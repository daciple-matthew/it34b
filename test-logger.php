<?php
require_once 'config/config.php';


$user_id = "root" ?? null;
$user_email = "root" ?? null;

$sucess = logActivity($pdo,$user_id,$user_email,'test_activity','success');

if($sucess){
    echo "Activity Log Insert Success";
}else{
    echo "Failed To Insert Activity Log";
}
?>    