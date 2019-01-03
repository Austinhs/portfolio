<?php

Database::query("UPDATE custom_fields 
				SET system = 1 
				WHERE column_name = 'custom_300000022'
");