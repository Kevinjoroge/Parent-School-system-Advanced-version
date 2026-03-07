<?php
function logActivity($conn, $role, $user_id, $activity){

    $role = mysqli_real_escape_string($conn,$role);
    $activity = mysqli_real_escape_string($conn,$activity);

    mysqli_query($conn,"
        INSERT INTO system_logs(user_role,user_id,activity)
        VALUES('$role','$user_id','$activity')
    ");
}
?>