<?php

Database::query("update custom_fields set system='1',suggestion_query='select short_name as value from resources where {syear} between coalesce(min_syear,1000) and coalesce(max_syear,3000) and school_id=''{school_id}''' where source_class='SISUser' and column_name='custom_100000003'");

?>
