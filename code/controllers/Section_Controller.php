<?php

class Section_Controller extends Controller
{
    /**
     * @var $section
     */
    protected $section;

    /*
     * @param Section $section
     */
     public function __contruct($section = null)
     {
         if ($section) {
             $this->section = $section;
             $this->failover = $section;
         }

         parent::__contruct();
     }

     public function index()
     {
         return;
     }

     /**
     * @param string $action
     *
     * @return string
     */
    public function Link($action = null)
    {
        $id = ($this->section) ? $this->section->ID : null;
        $segment = Controller::join_links('section', $id, $action);
        if ($page = Director::get_current_page()) {
            return $page->Link($segment);
        }
        return Controller::curr()->Link($segment);
    }
    /**
     * @return string - link to page this section is on
     */
    public function pageLink()
    {
        $parts = explode('/section/', $this->Link());
        return isset($parts[0]) ? $parts[0] : null;
    }

     public function getSection()
     {
         return $this->section;
     }
}
