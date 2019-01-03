<?php


// Add Student Addresses Template
Database::query("delete
                  from importer_templates
                    where name = 'Student Addresses';");


Database::query('INSERT INTO importer_templates (NAME, TYPE, SETTINGS)
                 VALUES
                 (
		         \'Student Addresses\', \'main\', \'{"destinationTable":"address","temporaryTable":"studentaddressTempImporter","errorTable":"studentaddressTempImporter_error","primaryKeys":[],"identityColumn":""}\'
	);');

// Add Student Addresses Keys
Database::query("delete
                  from importer_keys
                    where table_name = 'address';");

if (Database::$type === 'mssql') {
    Database::query("INSERT INTO importer_keys (
	            table_name, IDENTITY_COLUMN, PRIMARY_KEYS,REQUIRED_FIELDS,PRE_SQL, POST_SQL)
                VALUES
	            ('address', 'address_id', '[]','[\"student_id_for_import\"]',
	             '[\"alter table address add student_id_for_import varchar\",\"alter table address add mailing_for_import varchar\",\"alter table address add residence_for_import varchar\",\"alter table address add bus_pickup_for_import varchar\",\"alter table address add bus_dropoff_for_import varchar\"]',
                                     '[\"
                    INSERT INTO students_join_address(
                         student_id, address_id, mailing, residence,
                        bus_pickup, bus_dropoff, imported
                    )
                    SELECT
                    cast (student_id_for_import as numeric),
                    address_id,
                    left(mailing_for_import,1),
                    left(residence_for_import,1),
                    left(bus_pickup_for_import,1),
                    left(bus_dropoff_for_import,1),
                    ''I''
                    FROM
                        address
                    WHERE
                        student_id_for_import IS NOT NULL;\",
                        \"alter table address drop column student_id_for_import;\",
                        \"alter table address drop column mailing_for_import;\",
                        \"alter table address drop column residence_for_import;\",
                        \"alter table address drop column bus_pickup_for_import;\",
                        \"alter table address drop column bus_dropoff_for_import;\"
                        ]'
	             );"
    );
}
else{
    Database::query("INSERT INTO importer_keys (
	            table_name, IDENTITY_COLUMN, PRIMARY_KEYS,REQUIRED_FIELDS,PRE_SQL, POST_SQL)
                VALUES
	            ('address', 'address_id', '[]','[\"student_id_for_import\"]',
	             '[\"alter table address add student_id_for_import varchar\",\"alter table address add mailing_for_import varchar\",\"alter table address add residence_for_import varchar\",\"alter table address add bus_pickup_for_import varchar\",\"alter table address add bus_dropoff_for_import varchar\"]',
                                     '[\"
                    INSERT INTO students_join_address(
                        id, student_id, address_id, mailing, residence,
                        bus_pickup, bus_dropoff, imported
                    )
                    SELECT
                    nextval(''address_seq''),
                    cast (student_id_for_import as numeric),
                    address_id,
                    left(mailing_for_import,1),
                    left(residence_for_import,1),
                    left(bus_pickup_for_import,1),
                    left(bus_dropoff_for_import,1),
                    ''I''
                    FROM
                        address
                    WHERE
                        student_id_for_import IS NOT NULL;\",
                        \"alter table address drop column student_id_for_import;\",
                        \"alter table address drop column mailing_for_import;\",
                        \"alter table address drop column residence_for_import;\",
                        \"alter table address drop column bus_pickup_for_import;\",
                        \"alter table address drop column bus_dropoff_for_import;\"
                        ]'
	             );"
    );
}


// Add Student Contacts Template
Database::query("delete
                  from importer_templates
                    where name = 'Student Contacts';");


Database::query('INSERT INTO importer_templates (NAME, TYPE, SETTINGS)
                 VALUES
                 (
                 \'Student Contacts\', \'main\', \'{"destinationTable":"student_contacts_importer_table","temporaryTable":"student_contacts_importer_tableTempImporter","errorTable":"student_contacts_importer_tableTempImporter_error","primaryKeys":[],"identityColumn":""}\'
    );');

// Add Student Contacts Keys
Database::query("delete
                  from importer_keys
                    where table_name = 'student_contacts_importer_table';");


 Database::query("INSERT INTO importer_keys (
                table_name, IDENTITY_COLUMN, PRIMARY_KEYS,REQUIRED_FIELDS)
                VALUES
                ('student_contacts_importer_table', 'none', '[]','[\"student_id\",\"first_name\",\"last_name\",\"priority\"]')");