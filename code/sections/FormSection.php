<?php
/**
 *
 *
 * @package silverstripe
 * @subpackage sections
 */
class FormSection extends Section {
    private static $title = "Contact Form";

    private static $description = "Displays a contact form";

    private static $limit = 1;

    /**
     * Database fields
     * @var array
     */
    private static $db = array(
        'EmailFrom' => 'Varchar(50)',
        'EmailTo' => 'Varchar(50)',
        'SuccessMessage' => 'Text',
        'Title' => 'Text',
        'Content' => 'HTMLText'
    );

    /**
     * Has_one relationship
     * @var array
     */
    private static $has_one = array(
    );

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields(){
        $fields = parent::getCMSFields();

        $fields->addFieldsToTab(
            "Root.Main",
            array(
                TextField::create('Title', 'Title'),
                HtmlEditorField::create('Content', 'Content')
            )
        );

        // Form Settings
        $fields->insertBefore('Settings', Tab::create('FormInfo'));
        $fields->addFieldsToTab(
            "Root.FormInfo",
            array(
                HeaderField::create('<h3>Form Settings:</h3>'),
                TextField::create('EmailFrom', 'From email address')->setAttribute('placeholder', 'email@address.com'),
                TextField::create('EmailTo', 'Send emails to address')->setAttribute('placeholder', 'email@address.com'),
                TextareaField::create('SuccessMessage', 'Success message')->setAttribute('placeholder', 'Thank you for your enquiry...')
            )
        );
        
        return $fields;
    }
}

class FormSection_Controller extends Section_Controller {
    private static $allowed_actions = array (
		'SectionForm'
	);

	// Contact Form
	public function SectionForm(){
        $section = $this->getSection();

		$form = new Form(
			$this,
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

        $this->extend('UpdateSectionForm', $form);

		return $form;
	}

	// Send the Enquiry
	public function doSendSectionForm($data, $form){
        $config = SiteConfig::current_site_config();
        $section = $this->getSection();

		// Create the new contact submission
		$SectionSubmission = new SectionSubmission();
		$form->saveInto($SectionSubmission);
        $SectionSubmission->PageID = $this->getPage()->ID;
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

        $this->extend('UpdateSendSectionForm', $emailTo, $emailFrom, $subject, $message);

		$email = new Email($emailFrom, $emailTo, $subject, $message);
		$email->send();

		// Add success message
		$successMessage = $section->SuccessMessage ? $section->SuccessMessage : "Thank you for your enquiry. A member of our team will get back to you as soon as possible.";
		$form->sessionMessage($successMessage, 'good');

        return Controller::curr()->redirectBack();
	}
}
