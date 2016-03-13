<?php
class SectionContentControllerExtension extends Extension {

    /**
    * @var array
    */
    private static $allowed_actions = array(
        'handleSection'
    );

    /**
     * Handles section attached to a page
     * Assumes URLs in the following format: <URLSegment>/section/<section-ID>.
     *
     * @return RequestHandler
     */
    public function handleSection() {
        if ($id = $this->owner->getRequest()->param('ID')) {
            $sections = $this->owner->data()->Sections();
            if ($section = $sections->find('ID', $id)) {
                return $section->getController();
            }
        }
        // return $section->getController();
    }

}
