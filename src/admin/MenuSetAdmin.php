<?php

namespace gorriecoe\Menu\Admin;

use gorriecoe\Menu\Models\MenuSet;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldImportButton;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\Security\Member;

/**
 * CMS Admin area to maintain menus
 *
 * @package    silverstripe
 * @subpackage silverstripe-menu
 */
class MenuSetAdmin extends ModelAdmin
{
    /**
     * Managed data objects for CMS
     * @var array
     */
    private static $managed_models = [
        MenuSet::class
    ];

    /**
     * URL Path for CMS
     * @var string
     */
    private static $url_segment = 'menus';

    /**
     * Menu title for Left and Main CMS
     * @var string
     */
    private static $menu_title = 'Menus';

    /**
     * @var int
     */
    private static $menu_priority = 9;

    /**
     * @param Int       $id
     * @param FieldList $fields
     * @return Form
     */
    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);
        $form->Fields()
            ->fieldByName($this->sanitiseClassName($this->modelClass))
            ->getConfig()
            ->removeComponentsByType([
                GridFieldImportButton::class,
                GridFieldExportButton::class,
                GridFieldPrintButton::class,
                GridFieldDeleteAction::class
            ]);
        return $form;
    }

    /**
     * @param Member $member
     * @return boolean
     */
    public function canView($member = null)
    {
        $sets = Config::inst()->get(MenuSet::class, 'sets');
        if (!isset($sets) || !count($sets)) {
            return false;
        }
        return parent::canView($member);
    }
}
