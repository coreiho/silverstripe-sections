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
         }

         parent::__contruct();
     }

     public function index()
     {
         return;
     }

     public function getSection()
     {
         return $this->section;
     }
}
