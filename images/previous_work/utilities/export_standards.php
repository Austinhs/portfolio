<?php

require_once('./Warehouse.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if(Database::$type === 'mssql') {
	throw new Exception("This utility does not support SQL Server");
}

if(!empty($_POST['programs'])) {
	$programs = $_POST['programs'];

	// Get the categories
	$categories = [];

	foreach([1, 2, 3, 4] as $level) {
		if($level === 1) {
			$where = "
				id IN (" . join(', ', $programs) . ")
			";
		}
		else {
			$parent_level      = $level - 1;
			$parent_categories = array_column($categories[$parent_level], 'ID');

			if(empty($parent_categories)) {
				continue;
			}

			$where = "
				parent_id IN (" . join(', ', $parent_categories) . ")
			";
		}

		$sql = "
			SELECT
				*
			FROM
				standard_categories_{$level}
			WHERE
				{$where}
		";

		$categories[$level] = Database::get($sql);
	}

	// Get the standards
	$where = [];

	foreach($categories as $level => $tmp_categories) {
		$category_ids = array_column($tmp_categories, 'ID');

		if(!empty($category_ids)) {
			$where[] = "
				category_{$level}_id IN (" . join(', ', $category_ids) . ")
			";
		}
	}

	$sql = "
		SELECT
			*
		FROM
			standards
		WHERE
			" . join(' OR ', $where) . "
	";

	$standards = Database::get($sql);

	header('Content-Type: application/json');
	header('Content-Disposition: attachment; filename="standards.json"');

	echo json_encode([
		'categories' => $categories,
		'standards'  => $standards,
	]);
}
else {
	$categories = Database::get("SELECT id, title FROM standard_categories_1 ORDER BY title ASC");

	?>

	<form method="POST">
		<?php
			foreach($categories as $category) {
				?>
					<p>
						<label>
							<input type="checkbox" name="programs[]" value="<?= $category['ID'] ?>">
							<span><?= $category['TITLE'] ?></span>
						</label>
					</p>
				<?php
			}
		?>
		<p>
			<button type="submit">Export</button>
		</p>
	</form>

	<?php
}