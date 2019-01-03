<?php
/**
 * Custom Field Edit Rules Check
 */
include_once "../Warehouse.php";
require_once '../ProgramFunctions/StudentEdits.fnc.php';

if (User('PROFILE') != 'admin') {
	//die();
}

/**
 * Creates a temp file and executes the php syntax check.
 * @param  string $php
 * @return boolean
 */
function check_syntax($php)
{
	$tmp_name = tempnam("/tmp", "CF");

	file_put_contents($tmp_name, $php);

	$o = "";
	$return = null;
	exec('php --syntax-check '.$tmp_name, $o, $return);

	unlink($tmp_name);

	if ($return === 0) {
		return true;
	}
	return false;
}

/**
 * Runs the edit rule through the parser.
 * This will prep the PHP code for execution.
 * @param  string $php   The PHP code without the opening tag
 * @param  string $field  The field id
 * @return string The PHP code.
 */
function parse_rules($php, $field)
{
	$php = "<?php\r\n".$php;
	$php = str_replace('�', '"', $php);
	$php = str_replace(chr(148), '"', $php);
	$php = str_replace('\\"', '"', $php);
	$php = _changeDateField($php);
	$php = _changeFieldNameField($php);
	$php = _changeBorderTag($php, 'students', '');
	$php = str_replace('{FIELD_VALUE}', '"foo"', $php);
	return $php;
}

$query = "SELECT cf.id,
			     cf.title,
			     cf.comment,
			     cf.field_edits,
			     sjc.id AS category_id,
			     sjc.title AS category_title
		  FROM custom_fields AS cf
		  LEFT JOIN student_field_categories AS sjc
		  	ON sjc.id = cf.category_id
		  WHERE cf.field_edits IS NOT NULL
		  ORDER BY sjc.id, cf.sort_order";

$results = DBGet(DBQuery($query), array(), array('CATEGORY_ID', 'ID'));

?>
<!DOCTYPE html>
<html>
<head>
	<title>Focus - Custom Field Edit Rules</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css">
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
	<![endif]-->
</head>
<body style="margin-top:20px;">
	<div class="container">
		<div class="well">
			<h3 style="margin-top:0;">Custom Field Edit Rules</h3>
			<p>
				Custom fields highlighted red do not have valid PHP in the edit rules.
				These records can cause problems with editing, printing, and form validation.
			</p>
			<table class="table table-condensed table-hover">
				<thead>
					<tr>
						<th>CF ID</th>
						<th>Category</th>
						<th>Title</th>
						<th>Rule</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($results as $category_id => $fields) {
						foreach ($fields as $field_id => $field) {
							$field = $field[1];
							$rules = parse_rules($field['FIELD_EDITS']);
							$check = check_syntax($rules, 'CUSTOM_'.$field['ID']);
							?>
					<tr class="<?php echo ($check ? 'success' : 'danger'); ?>">
						<td><a href="../Modules.php?modname=Students/StudentFields.php&amp;category_id=<?php echo $field['CATEGORY_ID']; ?>&amp;id=<?php echo $field['ID']; ?>">CUSTOM_<?php echo $field['ID']; ?></a></td>
						<td><?php echo $field['CATEGORY_TITLE']; ?></td>
						<td><?php echo $field['TITLE']; ?></td>
						<td><textarea style="width:300px;" rows="4"><?php echo htmlentities($rules); ?></textarea></td>
						<td><span class="glyphicon <?php echo ($check ? 'glyphicon-ok-sign' : 'glyphicon-bullhorn'); ?>"></span></td>
					</tr>
							<?php
						}
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
</body>
</html>
