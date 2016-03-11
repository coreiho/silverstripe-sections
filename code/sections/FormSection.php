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
