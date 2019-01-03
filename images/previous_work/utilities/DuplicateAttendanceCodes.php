<?php
require_once("../Warehouse.php");

$table = 'attendance_codes';
$id_column = 'ID';

$fields_RET = DBGet(DBQuery("SELECT * FROM ".$table." WHERE SYEAR = 2012"));

$years = array('2011','2010','2009','2008','2007','2006','2005','2004','2003','2002','2001','2000','1999','1998','1997','1996','1995','1994','1993','1992','1991','1990','1989','1988','1987','1986','1985','1984','1983','1982','1981','1980');
echo "<pre>";
foreach($fields_RET as $field)
{
	foreach($years as $year)
	{
		$cols = $vals = '';
		foreach($field as $col=>$val)
		{
			if(isset($val) && $col != 'SYEAR')
			{
				$cols .= ','.$col;
				if ($col == $id_column)
					$vals .= ",nextval('".$table."_seq')";
				else
					$vals .= ",'".$val."'";
			}
		}
		$cols = substr($cols,1);
		$vals = substr($vals,1);

		echo "INSERT INTO ".$table." (".$cols.",SYEAR) values(".$vals.",'".$year."');\n";
	}
}
echo "<\pre>";
?>