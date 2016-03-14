<?php
/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class SubmissionsAdmin extends ModelAdmin
{
    /**
     * Managed data objects for CMS
     * @var array
     */
    private static $managed_models = array(
        'SectionSubmission'
    );

    /**
     * URL Path for CMS
     * @var string
     */
    private static $url_segment = 'formsubmissions';

    /**
     * Menu title for CMS
     * @var string
     */
    private static $menu_title = 'Form Submissions';
}
