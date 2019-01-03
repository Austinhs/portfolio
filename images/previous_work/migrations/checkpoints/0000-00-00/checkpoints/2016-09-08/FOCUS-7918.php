<?php

if (Database::$type === 'mssql'){

  Database::query("alter table IMPORTER_LOGS drop column filename;");

  Database::query("alter table IMPORTER_LOGS add filename varchar(max) null;");

  Database::query("alter table IMPORTER_LOGS drop column date;");
  
  Database::query("alter table IMPORTER_LOGS add date datetime null;");
  
} 