<?php

Migrations::depend('FOCUS-6359');

/*
Steven Ferretti
11/30/2015
8.0 Import Template Conversion
*/

// Migrate Students CSV Templates
$students_templates_RET = Database::get(Database::query("SELECT * FROM CSV_IMPORT_TEMPLATES WHERE MODNAME = 'Import/ImportStudents.php'"));
foreach ($students_templates_RET as $student_temp) {
    $currentFieldId = $student_temp['ID'];
    if (strpos($student_temp['MAPPED_FIELDS'], 'CUSTOM') !== false) {
        $oldId = trim(str_replace('CUSTOM_', ' ', $student_temp['MAPPED_FIELDS']));
        if (preg_match('/[A-Za-z]/', $oldId)) {
            continue;
        }
        $new_name_RET = Database::get(Database::query("SELECT COLUMN_NAME FROM CUSTOM_FIELDS WHERE SOURCE_CLASS = 'SISStudent' and LEGACY_ID = '{$oldId}';"));
        $newName = isset($new_name_RET[0]['COLUMN_NAME']) ? strtoupper($new_name_RET[0]['COLUMN_NAME']) : null;
        if (!is_null($newName) && $newName != '') {
            Database::query("UPDATE CSV_IMPORT_TEMPLATES SET MAPPED_FIELDS = '{$newName}' WHERE ID ={$currentFieldId};");
            //echo 'UPDATE CSV_IMPORT_TEMPLATES SET MAPPED_FIELDS = '.$newName.' WHERE ID ='.$currentFieldId.';</br>';
        }
    }
}


// Migrate Users CSV Templates
$users_templates_RET = Database::get(Database::query("SELECT * FROM CSV_IMPORT_TEMPLATES WHERE MODNAME = 'Import/ImportStaff.php'"));
foreach ($users_templates_RET as $user_temp) {
    $currentFieldId = $user_temp['ID'];
    if (strpos($user_temp['MAPPED_FIELDS'], 'CUSTOM') !== false) {
        $oldId = trim(str_replace('CUSTOM_', ' ', $user_temp['MAPPED_FIELDS']));
        if (preg_match('/[A-Za-z]/', $oldId)) {
            continue;
        }
        $new_name_RET = Database::get(Database::query("SELECT COLUMN_NAME FROM CUSTOM_FIELDS WHERE SOURCE_CLASS = 'SISUser' and LEGACY_ID = '{$oldId}';"));
        $newName = isset($new_name_RET[0]['COLUMN_NAME']) ? strtoupper($new_name_RET[0]['COLUMN_NAME']) : null;
        if (!is_null($newName) && $newName != '') {
            Database::query("UPDATE CSV_IMPORT_TEMPLATES SET MAPPED_FIELDS = '{$newName}' WHERE ID ={$currentFieldId};");
            //echo 'UPDATE CSV_IMPORT_TEMPLATES SET MAPPED_FIELDS = '.$newName.' WHERE ID ='.$currentFieldId.';</br>';
        }
    }
}

// Migrate Students DOE Templates
$students_templates_RET = Database::get(Database::query("SELECT * FROM DOE_IMPORT_TEMPLATES WHERE MODNAME = 'Import/ImportStudents.php'"));
foreach ($students_templates_RET as $student_temp) {
    $currentFieldId = $student_temp['ID'];
    if (strpos($student_temp['MAPPED_FIELDS'], 'CUSTOM') !== false) {
        $oldId = trim(str_replace('CUSTOM_', ' ', $student_temp['MAPPED_FIELDS']));
        if (preg_match('/[A-Za-z]/', $oldId)) {
            continue;
        }
        $new_name_RET = Database::get(Database::query("SELECT COLUMN_NAME FROM CUSTOM_FIELDS WHERE SOURCE_CLASS = 'SISStudent' and LEGACY_ID = '{$oldId}';"));
        $newName = isset($new_name_RET[0]['COLUMN_NAME']) ? strtoupper($new_name_RET[0]['COLUMN_NAME']) : null;
        if (!is_null($newName) && $newName != '') {
            Database::query("UPDATE DOE_IMPORT_TEMPLATES SET MAPPED_FIELDS = '{$newName}' WHERE ID ={$currentFieldId};");
            //echo 'UPDATE DOE_IMPORT_TEMPLATES SET MAPPED_FIELDS = '.$newName.' WHERE ID ='.$currentFieldId.';</br>';
        }
    }
}


// Migrate Users DOE Templates
$users_templates_RET = Database::get(Database::query("SELECT * FROM DOE_IMPORT_TEMPLATES WHERE MODNAME = 'Import/ImportStaff.php'"));
foreach ($users_templates_RET as $user_temp) {
    $currentFieldId = $user_temp['ID'];
    if (strpos($user_temp['MAPPED_FIELDS'], 'CUSTOM') !== false) {
        $oldId = trim(str_replace('CUSTOM_', ' ', $user_temp['MAPPED_FIELDS']));
        if (preg_match('/[A-Za-z]/', $oldId)) {
            continue;
        }
        $new_name_RET = Database::get(Database::query("SELECT COLUMN_NAME FROM CUSTOM_FIELDS WHERE SOURCE_CLASS = 'SISUser' and LEGACY_ID = '{$oldId}';"));
        $newName = isset($new_name_RET[0]['COLUMN_NAME']) ? strtoupper($new_name_RET[0]['COLUMN_NAME']) : null;
        if (!is_null($newName) && $newName != '') {
            Database::query("UPDATE DOE_IMPORT_TEMPLATES SET MAPPED_FIELDS = '{$newName}' WHERE ID ={$currentFieldId};");
            //echo 'UPDATE DOE_IMPORT_TEMPLATES SET MAPPED_FIELDS = '.$newName.' WHERE ID ='.$currentFieldId.';</br>';
        }
    }
}


//Migrate Student Logs CSV Templates
$students_templates_RET = Database::get(Database::query("SELECT * FROM CSV_IMPORT_TEMPLATES WHERE MODNAME = 'Import/ImportStudentsLog.php'"));
foreach ($students_templates_RET as $student_temp) {
    $currentFieldId = $student_temp['ID'];

    $oldIdInfo = explode('||', $student_temp['MAPPED_FIELDS']);
    $oldId = $oldIdInfo[0];

    if ($oldId == '' || is_null($oldId)) {
        continue;
    }

    $oldLog = $oldIdInfo[1];
    $new_name_RET = Database::get(Database::query("SELECT ID FROM CUSTOM_FIELDS WHERE SOURCE_CLASS = 'SISStudent' and LEGACY_ID = '{$oldId}';"));
    $newName = isset($new_name_RET[0]['ID']) ? strtoupper($new_name_RET[0]['ID']) : null;


    if (!is_null($newName)) {

        if ($oldLog == 'STUDENT_ID') {
            $oldLog = 'SOURCE_ID';
        }

        $newName = $newName . '||' . $oldLog;
        Database::query("UPDATE CSV_IMPORT_TEMPLATES SET MAPPED_FIELDS = '{$newName}' WHERE ID ={$currentFieldId};");
        //echo 'UPDATE CSV_IMPORT_TEMPLATES SET MAPPED_FIELDS = '.$newName.' WHERE ID ='.$currentFieldId.';</br>';
    }

}


//Migrate User Logs CSV Templates
$users_templates_RET = Database::get(Database::query("SELECT * FROM CSV_IMPORT_TEMPLATES WHERE MODNAME = 'Import/ImportUsersLog.php'"));
foreach ($users_templates_RET as $user_temp) {
    $currentFieldId = $user_temp['ID'];

    $oldIdInfo = explode('||', $user_temp['MAPPED_FIELDS']);
    $oldId = $oldIdInfo[0];

    if ($oldId == '' || is_null($oldId)) {
        continue;
    }

    $oldLog = $oldIdInfo[1];
    $new_name_RET = Database::get(Database::query("SELECT ID FROM CUSTOM_FIELDS WHERE SOURCE_CLASS = 'SISUser' and LEGACY_ID = '{$oldId}';"));
    $newName = isset($new_name_RET[0]['ID']) ? strtoupper($new_name_RET[0]['ID']) : null;

    if (!is_null($newName)) {

        if ($oldLog == 'USER_ID') {
            $oldLog = 'SOURCE_ID';
        }

        $newName = $newName . '||' . $oldLog;
        Database::query("UPDATE CSV_IMPORT_TEMPLATES SET MAPPED_FIELDS = '{$newName}' WHERE ID ={$currentFieldId};");
        //echo 'UPDATE CSV_IMPORT_TEMPLATES SET MAPPED_FIELDS = '.$newName.' WHERE ID ='.$currentFieldId.';</br>';
    }

}


//Migrate Student Logs DOE Templates
$students_templates_RET = Database::get(Database::query("SELECT * FROM DOE_IMPORT_TEMPLATES WHERE MODNAME = 'Import/ImportStudentsLog.php'"));
foreach ($students_templates_RET as $student_temp) {
    $currentFieldId = $student_temp['ID'];

    $oldIdInfo = explode('||', $student_temp['MAPPED_FIELDS']);
    $oldId = $oldIdInfo[0];

    if ($oldId == '' || is_null($oldId)) {
        continue;
    }

    $oldLog = $oldIdInfo[1];
    $new_name_RET = Database::get(Database::query("SELECT ID FROM CUSTOM_FIELDS WHERE SOURCE_CLASS = 'SISStudent' and LEGACY_ID = '{$oldId}';"));
    $newName = isset($new_name_RET[0]['ID']) ? strtoupper($new_name_RET[0]['ID']) : null;

    if (!is_null($newName)) {

        if ($oldLog == 'STUDENT_ID') {
            $oldLog = 'SOURCE_ID';
        }

        $newName = $newName . '||' . $oldLog;
        Database::query("UPDATE DOE_IMPORT_TEMPLATES SET MAPPED_FIELDS = '{$newName}' WHERE ID ={$currentFieldId};");
        //echo 'UPDATE DOE_IMPORT_TEMPLATES SET MAPPED_FIELDS = '.$newName.' WHERE ID ='.$currentFieldId.';</br>';
    }

}


//Migrate User Logs DOE Templates
$users_templates_RET = Database::get(Database::query("SELECT * FROM DOE_IMPORT_TEMPLATES WHERE MODNAME = 'Import/ImportUsersLog.php'"));
foreach ($users_templates_RET as $user_temp) {
    $currentFieldId = $user_temp['ID'];

    $oldIdInfo = explode('||', $user_temp['MAPPED_FIELDS']);
    $oldId = $oldIdInfo[0];

    if ($oldId == '' || is_null($oldId)) {
        continue;
    }

    $oldLog = $oldIdInfo[1];
    $new_name_RET = Database::get(Database::query("SELECT ID FROM CUSTOM_FIELDS WHERE SOURCE_CLASS = 'SISUser' and LEGACY_ID = '{$oldId}';"));
    $newName = isset($new_name_RET[0]['ID']) ? strtoupper($new_name_RET[0]['ID']) : null;

    if (!is_null($newName)) {

        if ($oldLog == 'USER_ID') {
            $oldLog = 'SOURCE_ID';
        }

        $newName = $newName . '||' . $oldLog;
        Database::query("UPDATE DOE_IMPORT_TEMPLATES SET MAPPED_FIELDS = '{$newName}' WHERE ID ={$currentFieldId};");
        //echo 'UPDATE DOE_IMPORT_TEMPLATES SET MAPPED_FIELDS = '.$newName.' WHERE ID ='.$currentFieldId.';</br>';
    }

}
?>