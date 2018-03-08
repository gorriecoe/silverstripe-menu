<?php

namespace gorriecoe\Menu\Extensions;

use SilverStripe\Subsites\Model\Subsite;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HiddenField;
use SilverStripe\ORM\DataExtension;

/**
 * Adds subsite support if installed
 *
 * @package silverstripe
 * @subpackage silverstripe-menu
 */
class MenuSetSubsiteExtension extends DataExtension
{
    /**
     * Has_one relationship
     * @var array
     */
    private static $has_one = array(
        'Subsite' => Subsite::class
    );

    /**
     * Update Fields
     * @return FieldList
     */
    public function updateCMSFields(FieldList $fields)
    {
        $owner = $this->owner;
        $fields->addFieldToTab(
            "Root.Main",
            HiddenField::create(
                'SubsiteID',
                'SubsiteID',
                Subsite::currentSubsiteID()
            )
        );
        return $fields;
    }

    /**
     * Event handler called before writing to the database.
     */
    public function onBeforeWrite()
    {
        $owner = $this->owner;
        if(!$owner->ID && !$owner->SubsiteID){
            $owner->SubsiteID = Subsite::currentSubsiteID();
        }
        parent::onBeforeWrite();
    }
}
