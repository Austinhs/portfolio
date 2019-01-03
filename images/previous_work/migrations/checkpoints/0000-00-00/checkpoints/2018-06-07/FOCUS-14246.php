<?php

if (Database::tableExists('grad_requirements')) {
	Database::query("DELETE FROM grad_requirements WHERE \"rule\" = 'PassedAlgebraIIEOC'");
}