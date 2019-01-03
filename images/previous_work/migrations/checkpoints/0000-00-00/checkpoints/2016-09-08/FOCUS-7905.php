<?php
if(Database::$type === 'mssql'){
	Database::query('select * into custom_reports_variables_tmp_7905 from custom_reports_variables');
	Database::query('drop table custom_reports_variables');
	Database::query('	
						CREATE TABLE [dbo].[CUSTOM_REPORTS_VARIABLES](
							[ID] [numeric](18, 0) NOT NULL,
							[VARIABLE_NAME] [varchar](255) NULL,
							[VARIABLE_TYPE] [varchar](255) NULL,
							[DEFAULT_VALUE] [varchar](255) NULL,
							[PULLDOWN_OPTIONS] [varchar](max) NULL,
							[interface_title] [varchar](255) NULL,
						PRIMARY KEY CLUSTERED 
						(
							[ID] ASC
						)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
						) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]'
			 		);
	
	if(!Database::sequenceExists('CUSTOM_REPORTS_VARIABLES_SEQ')){
		Database::createSequence('CUSTOM_REPORTS_VARIABLES_SEQ');
	}
	
	Database::query('
		insert into custom_reports_variables(id, variable_name, variable_type, default_value, pulldown_options, interface_title)
		select id, variable_name, variable_type, default_value, pulldown_options, interface_title
		from custom_reports_variables_tmp_7905;
	');
	
	Database::query('drop table custom_reports_variables_tmp_7905');
}