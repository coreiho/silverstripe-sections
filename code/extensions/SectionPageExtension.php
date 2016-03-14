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
