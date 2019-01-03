<?php

if(!Database::columnExists('custom_field_select_options', 'sort_order')) {
	Database::createColumn('custom_field_select_options', 'sort_order', 'numeric');
}