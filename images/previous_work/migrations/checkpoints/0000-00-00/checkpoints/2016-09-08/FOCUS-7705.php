<?php

if (Database::$type === 'postgres'){
	Database::query('create or replace function isdate(s varchar) returns boolean as $$
						begin
						  perform s::date;
						  return true;
						exception when others then
						  return false;
						end;
						$$ language plpgsql;'
	);
}
