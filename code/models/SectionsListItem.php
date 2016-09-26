<?php
/**
 *
 *
 * @package silverstripe
 * @subpackage sections
 */
class SectionsListItem extends DataObject
{
    /**
     * Singular name for CMS
     * @var string
     */
    private static $singular_name = 'Item';

    /**
     * Plural name for CMS
     * @var string
     */
    private static $plural_name = 'Items';

    /**
     * Database fields
     * @var array
     */
    private static $db = array(
        "AdminTitle" => "Varchar(50)",
        "Status" => "Boolean",
        "Title" => "Text",
        "Content" => "HTMLText",
    );

    private static $summary_fields = array(
        "AdminTitle" => "Title",
        "NiceStatus" => "Status"
    );

    public function getCMSFields() {

        $fields = parent::getCMSFields();
        $fields->addFieldsToTab(
            "Root.Main",
            array(
                OptionsetField::create(
                    'Status',
                    'Status',
                    array(
                        "1" => "Active",
                        "0" => "Disabled"
                    ),
                    1
                ),
                TextField::create(
                    'AdminTitle'
                )
                ->setDescription('This field is for adminisration use only and will not display on the site.'),
                TextareaField::create(
                    'Title',
                    'Title'
                )
                ->setRows(2),
                HtmlEditorField::create(
                    'Content',
                    'Content'
                )
            )
        );
        return $fields;
    }

    public function getNiceStatus() {
        return ($this->Status == 1 ? "Active" : "Disabled");
    }

    public function Anchor(){
        if ($this->Title) {
            return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-',$this->Title), '-'));
        }
        return false;
    }

    public function AnchorAttr(){
        if ($this->Anchor()) {
            return 'id="'.$this->Anchor().'"' ;
        }
        return false;
    }

}
