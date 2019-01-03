<?php

Database::query("
	UPDATE integration_export_batches 
	SET data = REPLACE(data, '<property name=\"remoteDirectory\"><value type=\"string\"><![CDATA[/]]></value></property>', '<property name=\"remoteDirectory\"><value type=\"string\"><![CDATA[]]></value></property>') 
	WHERE data like '%<property name=\"remoteDirectory\"><value type=\"string\"><![CDATA[/]]></value></property>%';
	");