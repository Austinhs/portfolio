<?php
$column_test=Database::$type=='postgres'?'column':'';

if (!Database::columnExists('test_history_tests','POST_SECONDARY'))
{
    Database::query('ALTER TABLE test_history_tests add '.$column_test.' POST_SECONDARY varchar(1)');
}

if (!Database::columnExists('test_history_tests','INTER_DISTRICT'))
{
    Database::query('ALTER TABLE test_history_tests add '.$column_test.' INTER_DISTRICT varchar(1)');
}

if (!Database::columnExists('test_history_tests','ALLOW_PROFILES_MODIFY')){
    Database::query('ALTER TABLE test_history_tests add '.$column_test.' ALLOW_PROFILES_MODIFY text');

    Database::query('UPDATE 
                        test_history_tests
                    SET 
                        allow_profiles_modify = concat(\'["\',th.id,\'"]\') 
                    FROM 
                        (SELECT 
                            id 
                        FROM 
                            user_profiles 
                        WHERE 
                        LOWER(profile) = \'teacher\' 
                        AND LOWER(title) = \'teacher\') th
                    WHERE 
                        allow_teacher_modify = \'Y\' 
                        OR allow_teacher_modify = \'y\';');
}

if (Database::columnExists('test_history_tests','ALLOW_TEACHER_MODIFY')){
    Database::query('ALTER TABLE test_history_tests drop column ALLOW_TEACHER_MODIFY');
}