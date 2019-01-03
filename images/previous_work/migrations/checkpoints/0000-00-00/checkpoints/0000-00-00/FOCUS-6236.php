<?php

if(!Database::columnExists('gradebook_templates','syear')) {
	$sql = "alter table gradebook_templates add syear int null";
	Database::query($sql);

	$sql = "
		update gradebook_templates
		set
			syear='2016'
		where
			syear is null";
	Database::query($sql);

}

if(!Database::columnExists('gradebook_templates','enabled')) {
	$sql = "alter table gradebook_templates add enabled int null";
	Database::query($sql);

	$sql = "
	update gradebook_templates
	set
		enabled=1
	where
		enabled is null";
	Database::query($sql);
}
