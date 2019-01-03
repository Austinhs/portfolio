<?php
Migrations::depend('FOCUS-11459');
Migrations::depend('FOCUS-9563');

$has_certification_field = Database::get("SELECT 1 FROM custom_fields WHERE title = 'WDIS Certifications'");
if(strtoupper($_FOCUS['config']['state_name']) == 'FLORIDA') {
	if(empty($has_certification_field)) {

		$new_field = new CustomField();

		//CCAT is specifcally needed in this situation to get
		//expected results instead of using concat
		$new_field
			->setSourceClass('SISStudent')
			->setType('computed_table')
			->setTitle('WDIS Certifications')
			->setComputedQuery(
				"SELECT
					DISTINCT s.student_id,
					CONCAT(
						(s.custom_23 ".CCAT." ' ' ".CCAT." wic.cert_title),
						(s.custom_63 ".CCAT." ' ' ".CCAT." wic.cert_title),
						(s.custom_71 ".CCAT." ' ' ".CCAT." wic.cert_title)
					) AS Certification
				FROM
					schedule s
					JOIN wdis_industry_cert wic ON(
						s.custom_23 = wic.cert_code
						OR s.custom_63 = wic.cert_code
						OR s.custom_71 = wic.cert_code
					)

				WHERE
					(
						s.custom_24 = 'P'
						OR s.custom_64 = 'P'
						OR s.custom_72 = 'P'
					)
					AND s.syear = {syear}
				")
			->persist();

		$new_category = new CustomFieldCategory();

		$new_category
			->setTitle('WDIS Certifications')
			->setSourceClass('SISStudent')
			->setSortOrder('0')
			->persist();

		$new_join_category = new CustomFieldJoinCategory();

		$new_join_category
			->setFieldID($new_field->getID())
			->setSortOrder('0')
			->setCategoryID($new_category->getID())
			->persist();

	}
}
