<?php
/**
 * Creates a base object to be extended upon.  This section is not usable by itself.
 *
 * @package silverstripe
 * @subpackage sections
 */
class Section extends DataObject implements PermissionProvider
{
    private static $title = "Section";

    private static $description = "";

    /**
     * Singular name for CMS
     * @var string
     */
    private static $singular_name = 'Section';

    /**
     * Plural name for CMS
     * @var string
     */
    private static $plural_name = 'Sections';

    /**
     * Database fields
     * @var array
     */
    private static $db = array(
        'UniqueConfigTitle' => 'Varchar(100)', // store the original AdminTitle determined by config here for filtering
        'AdminTitle' => 'Varchar(100)',
        'MenuTitle' => 'Varchar(30)',
        'Public' => 'Boolean',
        'Style' => 'Text',
        "ShowInMenus" => "Boolean"
    );

    private static $defaults = array(
        "ShowInMenus" => 1,
        "Public" => 1
    );

    /**
     * Belongs_many_many relationship
     * @var array
     */
    private static $belongs_many_many = array(
        'Pages' => 'Page'
    );

    /**
     * @var SectionController
     */
    protected $controller;

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields() {
        return new FieldList(
            $rootTab = new TabSet(
                "Root",
                $tabMain = new Tab(
                    'Main',
                    HiddenField::create('UniqueConfigTitle'),
                    TextField::create(
                        'AdminTitle',
                        'Admin title'
                    )
                    ->setDescription('This field is for adminisration use only and will not display on the site.'),
                    CheckboxField::create(
                        'ShowInMenus',
                        'Show in menus',
                        0
                    ),
                    DisplayLogicWrapper::create(
                        TextField::create(
                            'MenuTitle',
                            'Navigation label'
                        )
                    )
                    ->displayIf("ShowInMenus")->isChecked()->end()
                ),
                $tabSettings = new Tab(
                    'Settings',
                    CheckboxField::create(
                        'Public',
                        'Public',
                        1
                    )
                    ->setDescription('Is this section publicly accessible?.'),
                    DropdownField::create(
                        'Style',
                        'Select a style',
                        $this->ConfigStyles
                    )
                    ->setEmptyString('Default')
                )
            )
        );
    }

    /**
     * Summary Fields
     * @return array
     */
    public static $summary_fields = array(
        'AdminTitle' => 'Title',
        'Type' => 'Type'
    );

    /**
     * Searchable Fields
     * @return array
     */
    public static $searchable_fields = array(
        'AdminTitle',
        'MenuTitle'
    );


    /**
     * Permissions
     * @return array
     */
    public function providePermissions() {
        return array(
            "VIEW_SECTIONS" => array(
                'name' => 'View any sections',
                'help' => 'Allow users to view any sections on a page',
                'category' => 'Sections',
                'sort' => 88
            ),
            "CREATE_SECTIONS" => array(
                'name' => 'Create sections',
                'help' => 'Allow users to create new sections on pages',
                'category' => 'Sections',
                'sort' => 90
            ),
            "EDIT_SECTIONS" => array(
                'name' => 'Edit sections',
                'help' => 'Allow users to edit existing sections',
                'category' => 'Sections',
                'sort' => 92
            ),
            "DELETE_SECTIONS" => array(
                'name' => 'Delete sections',
                'help' => 'Allow users to delete sections from pages',
                'category' => 'Sections',
                'sort' => 94
            ),
            "LINK_SECTIONS" => array(
                'name' => 'Link existing sections',
                'help' => 'Allow users to link existing sections to a page',
                'category' => 'Sections',
                'sort' => 96
            ),
            "REORDER_SECTIONS" => array(
                'name' => 'Change order of sections',
                'help' => 'Allow users to change the order of existing sections on a page',
                'category' => 'Sections',
                'sort' => 100
            )
        );
    }

    /**
     * Viewing Permissions
     * @return boolean
     */
    public function canView($member = null) {
        return Permission::check('VIEW_SECTIONS', 'any', $member);
    }

    /**
     * Editing Permissions
     * @return boolean
     */
    public function canEdit($member = null) {
        return Permission::check('EDIT_SECTIONS', 'any', $member);
    }

    /**
     * Deleting Permissions
     * @return boolean
     */
    public function canDelete($member = null) {
        if ($this->UniqueConfigTitle){
            return false;
        }
        return Permission::check('DELETE_SECTIONS', 'any', $member);
    }

    /**
     * Creating Permissions
     * @return boolean
     */
    public function canCreate($member = null) {
        return Permission::check('CREATE_SECTIONS', 'any', $member);
    }

    public function getConfigStyles(){
        $config_styles = Config::inst()->get(get_called_class(), 'styles');
        if ($config_styles) {
            foreach ($config_styles as $value) {
                $styles[$value] = preg_replace('/([a-z]+)([A-Z0-9])/', '$1 $2', $value);
            }
            return $styles;
        }
        return array();
    }

    public static function Type($ClassName = NULL){
        if (!$ClassName) {
            $ClassName = get_called_class();
        }
        $niceTitle = Config::inst()->get($ClassName,'title');
        if ($niceTitle) {
            return $niceTitle;
        }
        return trim(preg_replace('/([A-Z])/', ' $1', str_ireplace('Section', '', $ClassName)));

    }

    /**
     * Applies anchor to section in template.
     *
     * @return string $classes
     */
    public function Anchor(){
        if ($this->MenuTitle && $this->ShowInMenus) {
            return strtolower(str_replace(' ','',$this->MenuTitle));
        }
        return false;
    }

    /**
     * Applies anchor to section in template.
     *
     * @return string $classes
     */
    public function AnchorAttr(){
        if ($this->Anchor()) {
            return ' id="'.$this->Anchor().'"';
        }
        return false;
    }

    /**
     * Applies classes to section in template.
     *
     * @return string $classes
     */
    public function Classes(){
        $classes = array('section');
        if ($this->Style) {
            $classes[] = strtolower($this->Style).'-'.strtolower(preg_replace('/([a-z]+)([A-Z0-9])/', '$1-$2', get_called_class()));
        }else{
            $classes[] = strtolower(preg_replace('/([a-z]+)([A-Z0-9])/', '$1-$2', get_called_class()));
        }
        return implode(' ',$classes);
    }

    /**
     * Applies classes to section in template.
     *
     * @return string $classes
     */
    public function ClassAttr(){
        return 'class="'.$this->Classes().'"';
    }

    public function Render(){
        $page = Director::get_current_page();
        $styleType = ($this->Style ? '_'.$this->Style : '');
        $pageType = ($page->ClassName ? '_'.$page->ClassName : '');
        $sectionType = get_called_class();
        return array(
            $sectionType.$pageType.$styleType,
            $sectionType.$styleType,
            $sectionType.$pageType,
            $sectionType,
            'DefaultSection'
        );
    }

    public function Layout()
    {
        $member = Member::currentUser();
        $access = Permission::checkMember($member, 'CMS_ACCESS');
        if($this->Public || $access){
            $this->CurrentPage = Controller::curr();
            return $this->renderWith($this->Render());
        }
    }

    public function getController(){
        if ($this->controller) {
            return $this->controller;
        }

        foreach (array_reverse(ClassInfo::ancestry($this->class)) as $sectionClass) {
            $controllerClass = "{$sectionClass}_Controller";
            if (class_exists($controllerClass)) {
                break;
            }
        }
        if (!class_exists($controllerClass)) {
            throw new Exception("Could not find controller class for $this->classname");
        }

        // TODO: Should be a nicer way of setting the section to the controller
        $this->controller = Injector::inst()->create($controllerClass);
        $this->controller->section = $this;

        return $this->controller;
    }

    public function GridFieldRowClasses(){
        if ($this->UniqueConfigTitle){
            return array('preset');
        }
        return array();
    }
}
