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
        return [
            'MenuSet' => 'MenuSet'
        ];
    }

    /**
     * @param String $slug
     * @return ArrayList|Null
     */
    public static function MenuSet($slug)
    {
        if (!$slug) {
            return;
        }
        if ($menuSet = MenuSet::get_by_slug($slug)) {
            return $menuSet->Links();
        }
    }
}
