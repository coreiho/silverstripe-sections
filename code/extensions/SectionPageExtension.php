<?php
/**
 * Adds sections to each page.
 *
 * @package silverstripe-sections
 */
class SectionPageExtension extends DataExtension {
    /**
     * Has_one relationship
     * @var array
     */
    private static $has_one = array(
        "PreviewImage" => "Image"
    );

    /**
     * Has_many relationship
     * @var array
     */
    private static $many_many = array(
        'Sections' => 'Section',
        'SectionSubmissions' => 'SectionSubmission'
    );

    private static $many_many_extraFields = array(
        'Sections' => array(
            'Sort' => 'Int'
        )
    );

    /**
     * CMS Fields
     * @return FieldList
     */
    public function updateCMSFields(FieldList $fields) {
        if (!Permission::check("VIEW_SECTIONS")) {
            return $fields;
        }

        $SectionGrid = GridFieldConfig_RelationEditor::create()
            ->removeComponentsByType('GridFieldAddNewButton')
            ->addComponent(new GridFieldAddNewMultiClass())
            ->addComponent(new GridFieldOrderableRows());

        $SectionGrid->getComponentByType('GridFieldAddExistingAutocompleter')
            ->setSearchFields(array('AdminTitle', 'MenuTitle'))
            ->setResultsFormat('$AdminTitle - $Type');

        $AvailableTypes = $this->AvailableSectionTypes();

        foreach ($AvailableTypes as $key => $value) {
            if($value['selectable_option'] && !$value['limit_reached']){
                $AvailableTypes[$key] = $value['type'];
            }
        }

        $SectionGrid->getComponentByType('GridFieldAddNewMultiClass')
            ->setClasses($AvailableTypes);

        // Limit total sections
        $LimitSectionTotal = Config::inst()->get($this->owner->ClassName, 'LimitSectionTotal');
        if (isset($LimitSectionTotal) && $this->owner->Sections()->Count() >= $LimitSectionTotal) {
            // remove the buttons if we don't want to allow more records to be added/created
            $SectionGrid->removeComponentsByType('GridFieldAddNewButton');
            $SectionGrid->removeComponentsByType('GridFieldAddExistingAutocompleter');
            $SectionGrid->removeComponentsByType('GridFieldAddNewMultiClass');
        }

        if (!Permission::check("LINK_SECTIONS")) {
            $SectionGrid->removeComponentsByType('GridFieldAddExistingAutocompleter');
        }

        if (!Permission::check("REORDER_SECTIONS")) {
            $SectionGrid->removeComponentsByType('GridFieldOrderableRows');
        }

        if (!Permission::check("UNLINK_SECTIONS")) {
            $SectionGrid->removeComponentsByType('GridFieldDeleteAction');
        }

        $fields->addFieldToTab(
            'Root.Section',
            GridField::create(
                'Sections',
                'Current Section(s)',
                $this->owner->Sections(),
                $SectionGrid
            )
        );

        $fields->addFieldToTab(
            'Root.Preview',
            UploadField::create(
                'PreviewImage',
                'Preview image'
            )->setFolderName('Preview')
        );

        return $fields;
    }

    public function onAfterWrite() {
        parent::onAfterWrite();

        $AvailableTypes = $this->AvailableSectionTypes();
        foreach ($AvailableTypes as $key => $value) {
            $ClassName = $AvailableTypes[$key]['classname'];
            if($AvailableTypes[$key]['presets'] !== null){
                foreach ($AvailableTypes[$key]['presets'] as $AdminTitle => $ShareStatus) {
                    $Section = $this->owner->Sections()
                        ->filter(
                            array(
                                'ClassName' => $ClassName,
                                'UniqueConfigTitle' => $AdminTitle
                            )
                        );
                    if ($Section->Count()){
                            continue;
                    }
                    $ExistingSection = $ClassName::get()->filter(
                        array(
                            'ClassName' => $ClassName,
                            'UniqueConfigTitle' => $AdminTitle
                        )
                    )->first();
                    if($ExistingSection && $ShareStatus == 'shared') {
                        $this->owner->Sections()->add($ExistingSection);
                    }else{
                        $newSection = $ClassName::create();
                        $newSection->UniqueConfigTitle = $AdminTitle;
                        $newSection->AdminTitle = $AdminTitle;
                        $newSection->Public = true;
                        $newSection->Write();
                        $this->owner->Sections()->add($newSection);
                    }
                }
            }
        }
    }

    /**
     * Lists all sections types and their settings relative to the current page type.
     * @return array
     */
    public function AvailableSectionTypes() {
        $AvailableTypes = ClassInfo::subclassesfor('Section');
        unset($AvailableTypes['Section']);

        # Get section options from each page type.
        $pageTypeOptions = Config::inst()->get($this->owner->ClassName, 'section_options');

        foreach ($AvailableTypes as $key => $value) {
            $Config = Config::inst();
            $selectable_option = true;
            if ($Config->get($value, 'selectable_option') !== null) {
                $selectable_option = $Config->get($value, 'selectable_option');
            }
            $AvailableTypes[$key] = array(
                'classname' => $value,
                'type' => Section::Type($value),
                'presets' => $Config->get($value, 'presets'),
                'selectable_option' => $selectable_option,
                'limit' => $Config->get($value, 'limit'),
            );

            if (isset($pageTypeOptions[$key])) {
                $AvailableTypes[$key] = array_merge($AvailableTypes[$key], $pageTypeOptions[$key]);
            }

            $AvailableTypes[$key]['limit_reached'] = false;
            if(isset($AvailableTypes[$key]['limit'])){
                if ($AvailableTypes[$key]['limit'] == 0) {
                    $AvailableTypes[$key]['limit_reached'] = true;
                }

                $CurrentSectionCount = $this->owner
                    ->Sections()
                    ->filter('ClassName', $AvailableTypes[$key]['type'])
                    ->count();
                if ($CurrentSectionCount >= $AvailableTypes[$key]['limit']) {
                    $AvailableTypes[$key]['limit_reached'] = true;
                }
            }
        }

        return $AvailableTypes;
    }

    /**
     * Used for link section so template does have to check for 2 variables
     * @return string
     */
    public function LinkURL() {
        return $this->owner->Link();
    }
}

class SectionsPage_Controller extends DataExtension {
    private static $allowed_actions = array (
		'SectionForm'
	);

	// Contact Form
	public function SectionForm(){
		$form = new Form(
			$this->owner,
			'SectionForm',
			new FieldList(
				TextField::create('Name', 'Name')->setAttribute('required data-parsley-error-message', 'Please enter your name.'),
				EmailField::create('Email', 'Email address')->setAttribute('required data-parsley-error-message', 'Please enter your email address.'),
				TextField::create('Phone', 'Phone number'),
                TextareaField::create('Message', 'Enquiry')->setRows(8)->setAttribute('required data-parsley-error-message', 'Please enter your enquiry.')
			),
			new FieldList(
	            FormAction::create("doSendSectionForm")->setTitle("Send Enquiry")
	        ),
			new RequiredFields(array(
    			'Name', 'Email', 'Message'
			))
		);

		$form->setAttribute('data-parsley-validate', true);
		$form->setFormMethod('POST', true);

        $this->owner->extend('UpdateSectionForm', $form);

		return $form;
	}

	// Send the Enquiry
	public function doSendSectionForm($data, $form){
        $config = SiteConfig::current_site_config();
        $section = $this->owner->Sections()->filter(array('ClassName' => 'FormSection'))->first();

		// Create the new contact submission
		$SectionSubmission = new SectionSubmission();
		$form->saveInto($SectionSubmission);
        $SectionSubmission->PageID = $this->owner->ID;
		$SectionSubmission->write();

		// Send email confirmation
		$emailTo = $section->EmailTo ? $section->EmailTo : 'web@platocreative.co.nz';
		$emailFrom = $section->EmailFrom ? $section->EmailFrom : 'noreply@' . Director::baseURL();
		$subject = $config->Title . ' - Online Enquiry';
        $message = "<h3>Online Enquiry</h3>";
        $message .= "<p>Name:<br />" . $data['Name'] . "</p>";
        $message .= "<p>Email:<br />" . $data['Email'] . "</p>";
        $message .= "<p>Phone:<br />" . $data['Phone'] . "</p>";
        $message .= "<p>Message:<br />" . $data['Message'] . "</p>";

        $this->owner->extend('UpdateSendSectionForm', $emailTo, $emailFrom, $subject, $message);

		$email = new Email($emailFrom, $emailTo, $subject, $message);
		$email->send();

		// Add success message
		$successMessage = $section->SuccessMessage ? $section->SuccessMessage : "Thank you for your enquiry. A member of our team will get back to you as soon as possible.";
		$form->sessionMessage($successMessage, 'good');

        return $this->owner->redirectBack();
	}
}
