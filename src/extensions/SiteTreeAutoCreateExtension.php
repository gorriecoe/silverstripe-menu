<?php

namespace gorriecoe\Menu\Extensions;

use SilverStripe\Dev\Debug;
use gorriecoe\Menu\Models\MenuSet;
use gorriecoe\Menu\Models\MenuLink;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataExtension;

/**
 * Provides the option to automatically create a menu link
 * after creating a page in the sitetree
 *
 * @package silverstripe
 * @subpackage silverstripe-menu
 */
class SiteTreeAutoCreateExtension extends DataExtension
{
    /**
     * Event handler called after writing to the database.
     */
    public function onAfterWrite()
    {
        parent::onAfterWrite();
        $owner = $this->owner;

        $autoCreateList = $owner->config()->get('owns_menu') ? : [];

        foreach ($autoCreateList as $key => $slug) {
            if ($menuSet = MenuSet::get_by_slug($slug)) {
                $menuLink = DataObject::get_one(MenuLink::class, [
                    'MenuSetID' => $menuSet->ID,
                    'SiteTreeID' => $owner->ID
                ]);
                if ($menuLink) {
                    $menuLink->setField('Title', $owner->Title);
                } else {
                    $menuLink = MenuLink::create([
                        'Type' => 'SiteTree',
                        'MenuSetID' => $menuSet->ID,
                        'SiteTreeID' => $owner->ID
                    ]);
                };
                $menuLink->write();
            }
        }
    }
}
