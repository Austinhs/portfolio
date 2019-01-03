<?php
if(!Database::columnExists('portal_pages', 'main')) {
	$profile_RET = DBGet(DBQuery('SELECT distinct profile_id from portal_layout where portal_page_id = 0'));
	Database::createColumn('portal_pages', 'main', 'numeric default 0');

	foreach($profile_RET as $row) {
		$profile_id = $row['PROFILE_ID'];
		$profile_name = DBGet(DBQuery('SELECT title from user_profiles where id = \''.$profile_id.'\''));
		$profile_name = $profile_name[1]['TITLE'];

		$page_id = DB::getNextVal('portal_pages_seq');
		$title = $profile_name.' Portal';
		DBQuery(
			DB::insertObject()
			->into('portal_pages')
			->column('ID', 'TITLE', 'MAIN')
			->value($page_id, $title, 1)
		);

		DBQuery(
			DB::insertObject()
			->into('portal_pages_to_profile')
			->column('PORTAL_PAGE_ID', 'PROFILE_ID')
			->value($page_id, $profile_id)
		);

		DBQuery(
			DB::updateObject()
			->update('portal_layout')
			->set('portal_page_id', $page_id)
			->where(DB::col('portal_page_id'), '=', 0)
			->_and(DB::col('profile_id'), '=', $profile_id)
		);
	}

	DBQuery('ALTER TABLE portal_layout DROP COLUMN profile_id');
}
?>