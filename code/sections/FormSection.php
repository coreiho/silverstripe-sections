<?php
/**
 *
 *
 * @package silverstripe
 * @subpackage sections
 */
class FormSection extends Section
{
    private static $title = "Page userform";

    private static $description = "Displays userform from a specified page";

    private static $minimum_per_page = 0;

    /**
     * Database fields
     * @var array
     */
    private static $db = array();

    /**
     * Has_one relationship
     * @var array
     */
    private static $has_one = array(
        'FormPage' => 'UserDefinedForm'
    );

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab(
            "Root.Main",
            array(
                DropdownField::create(
                    'FormPageID',
                    'Select a page',
                    UserDefinedForm::get()->map('ID', 'Title')
                )
            )
        );
        return $fields;
    }

    public function ReferencedPage(){
        if ($this->FormPage()) {
            $result = new UserDefinedForm_Controller($this->FormPage());
            $result->init();
            return $result;
        }
    }

    public function Title()
    {
        if ($this->ReferencedPage()) {
            return $this->ReferencedPage()->Title;
        }
        return false;
    }

    public function Content()
    {
        if ($this->ReferencedPage()) {
            return $this->ReferencedPage()->Content;
        }
        return false;
    }

    public function Form()
    {
        if ($this->ReferencedPage()) {
            return $this->ReferencedPage()->Form();
        }
        return false;
    }
}
