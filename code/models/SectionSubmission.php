<?php
class SectionSubmission extends DataObject {
	private static $db = array(
		'Name' => 'Text',
		'Email' => 'Varchar(250)',
		'Phone' => 'Varchar(50)',
		'Message' => 'Text'
	);

	private static $has_one = array(
        'Page' => 'SiteTree'
	);

	private static $summary_fields = array(
		'Name' => 'Name',
        'Email' => 'Email',
        'Phone' => 'Phone',
        'Page.Title' => 'Page'
	);

	static $default_sort = "ID DESC";

	public function getCMSFields() {
		$fields = new FieldList(
			LiteralField::create('', '<h3>Enquiry Information:</h3>'),
			ReadonlyField::create('Name', 'Persons Name'),
			ReadonlyField::create('Email', 'Email address'),
			ReadonlyField::create('Phone', 'Phone number'),
			TextareaField::create('Message', 'Enquiry Message')->setAttribute('disabled', 'disabled'),
            ReadonlyField::create('NewOne', 'Enquiry from page', $this->Page()->Title)
		);

		return $fields;
	}

	function canView($member = false) {
		return true;
	}

	function canEdit($member = false) {
		return true;
	}

	function canDelete($member = NULL) {
		return true;
	}

	function canCreate($member = NULL) {
		return false;
	}
}
