<?php
// This portion of the FOCUS-13927 branch is Finance-specific, so it can only be run if Finance
// is enabled.

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Migrations::depend('FOCUS-13927');

MigrationFOCUS13927a::run();

class MigrationFOCUS13927a {
	public static function run() {
		self::convertFinanceSettingToHTMLPartial('receipt_footer_text', 'Default POS Receipt Footer Text');
		self::convertFinanceSettingToHTMLPartial('ar_invoice_header', 'Default POS Invoice Header Text');
		self::convertFinanceSettingToHTMLPartial('ar_invoice_footer', 'Default POS Invoice Footer Text');
	}

	private static function convertFinanceSettingToHTMLPartial($setting_key, $title) {
		$setting = Settings::get($setting_key) ?: '';

		if(!is_string($setting)) {
			return;
		}

		$html_partial = (new HTMLPartial())
							->setPackage('Finance')
							->setTitle($title)
							->setHtml(nl2br(trim($setting)))
							->setAvailableFor('["ar_pos"]')
							->persist();

		$facilities = Facility::getAllAndLoad();

		$new_setting = [];

		foreach ($facilities as $facility) {
			$new_setting[$facility->getId()] = $html_partial->getAlias();
		}

		Settings::set($setting_key, $new_setting);
	}
}
