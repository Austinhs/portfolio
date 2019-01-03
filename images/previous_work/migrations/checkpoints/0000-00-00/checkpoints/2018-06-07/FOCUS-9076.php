<?php

if(Database::$type == 'postgres') {
    Database::query("
            UPDATE 
                custom_field_select_options cfs
            SET 
                deleted = 1 
            FROM 
                (
                SELECT 
                    MIN(cfso.id) AS id, 
                    cflc.id AS column_id, 
                    cfso.label 
                FROM 
                    custom_field_select_options cfso 
                    JOIN custom_field_log_columns cflc ON cfso.source_class = 'CustomFieldLogColumn' 
                    AND cfso.source_id = cflc.id 
                    JOIN custom_fields cf ON cf.id = cflc.field_id 
                WHERE 
                    cf.source_class = 'SISStudent' 
                    AND cf.alias = 'letter_log' 
                    AND cflc.title = 'Recipient' 
                GROUP BY 
                    cflc.id, 
                    cfso.label
            ) as keep 
            WHERE 
                cfs.source_class = 'CustomFieldLogColumn' 
                AND cfs.source_id = keep.column_id 
                AND cfs.label = keep.label 
                AND cfs.id != keep.id;
           ");
}else{
    Database::query("
            UPDATE 
                cfs 
            SET 
                deleted = 1 
            FROM 
                custom_field_select_options cfs 
                JOIN (
                    SELECT 
                        MIN(cfso.id) AS id, 
                        cflc.id AS column_id, 
                        cfso.label 
                    FROM 
                        custom_field_select_options cfso 
                        JOIN custom_field_log_columns cflc ON cfso.source_class = 'CustomFieldLogColumn' 
                        AND cfso.source_id = cflc.id 
                        JOIN custom_fields cf ON cf.id = cflc.field_id 
                    WHERE 
                        cf.source_class = 'SISStudent' 
                        AND cf.alias = 'letter_log' 
                        AND cflc.title = 'Recipient' 
                    GROUP BY 
                        cflc.id, 
                        cfso.label
                ) AS keep ON cfs.source_class = 'CustomFieldLogColumn' 
                AND cfs.source_id = keep.column_id 
                AND cfs.label = keep.label 
                AND cfs.id != keep.id;
    ");
}