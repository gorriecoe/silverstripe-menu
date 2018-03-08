<?php

namespace gorriecoe\Menu\View;

use gorriecoe\Menu\Models\MenuSet;
use SilverStripe\View\TemplateGlobalProvider;

/**
 * Adds MenuSet variable to templates
 *
 * @package silverstripe-menu
 */
class MenuManagerTemplateProvider implements TemplateGlobalProvider
{
    /**
     * @return array|void
     */
    public static function get_template_global_variables()
    {
        return array(
            'MenuSet' => 'MenuSet'
        );
    }

    /**
     * @param $slug
     * @return DataObject
     */
    public static function MenuSet($slug)
    {
        if (!$slug) {
            return;
        }
        if ($MenuSet = MenuSet::get()->find('Slug', $slug)) {
            return $MenuSet->Links();
        }
    }
}
