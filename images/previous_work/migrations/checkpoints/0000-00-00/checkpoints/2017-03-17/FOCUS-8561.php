<?php

//make sure all positions of icons on attendance chart are positive
//so they appear on screen
$sql = "UPDATE attendance_info SET int2 = 100 WHERE int2 < 0;";
Database::query($sql);

$sql = "UPDATE attendance_info SET int3 = 100 WHERE int3 < 0;";
Database::query($sql);