<?php
/* Determine background automatically */

$bg = "school_bg.jpg"; // default for index

if(isset($_SESSION['role'])){

    if($_SESSION['role'] == 'admin'){
        $bg = "admin_bg.jpg";
    }

    elseif($_SESSION['role'] == 'teacher'){
        $bg = "teacher_bg.jpg";
    }

    elseif($_SESSION['role'] == 'parent'){
        $bg = "parent_bg.jpg";
    }
}
?>

<style>
body {
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;

    background: url('images/<?php echo $bg; ?>') no-repeat center center fixed;
    background-size: cover;
}

.overlay {
    background: rgba(0,0,0,0.6);
    min-height: 100vh;
    padding: 20px;
    color: white;
}

.container-box {
    background: rgba(255,255,255,0.95);
    padding: 20px;
    border-radius: 12px;
    max-width: 900px;
    margin: auto;
    margin-top: 50px;
}
</style>