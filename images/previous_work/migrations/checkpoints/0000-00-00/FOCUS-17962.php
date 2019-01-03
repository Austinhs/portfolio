<?php
if (empty($GLOBALS["FocusFinanceConfig"]["enabled"])) {
	return false;
}

if (Database::tableExists("gl_wo_request_product")) {
	return true;
}

Database::begin();

$sql =
	"CREATE TABLE gl_wo_request_product (
		id BIGINT PRIMARY KEY,
		deleted INT,
		request_id BIGINT,
		product_id BIGINT,
		quantity NUMERIC(28, 10)
	)";

Database::query($sql);

$sql =
	"INSERT INTO
		gl_wo_request_product
			(id, request_id, product_id, quantity)
	SELECT
		{{next:gl_maint_seq}},
		id AS request_id,
		product_id,
		1 AS quantity
	FROM
		gl_wo_request
	WHERE
		COALESCE(product_id, 0) != 0";

Database::query(Database::preprocess($sql));
Database::dropColumn("gl_wo_request", "product_id");
Database::commit();
return true;
?>