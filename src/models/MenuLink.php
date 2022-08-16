<?php

namespace gorriecoe\Menu\Models;

use gorriecoe\Link\Models\Link;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Security\Member;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * MenuLink
 *
 * @property int $MenuSetID
 * @property int $ParentID
 * @property int $Sort
 * @method MenuLink Parent()
 * @method HasManyList|MenuLink[] Children()
 * @package silverstripe-menu
 */
class MenuLink extends Link
{
    /**
     * Defines the database table name
     * @var string
     */
    private static $table_name = 'MenuLink';

    /**
     * Singular name for CMS
     * @var string
     */
    private static $singular_name = 'Link';

    /**
     * Plural name for CMS
     * @var string
     */
    private static $plural_name = 'Links';

    /**
     * Database fields
     * @var array
     */
    private static $db = [
        'Sort' => 'Int'
    ];

    /**
     * Has_one relationship
     * @var array
     */
    private static $has_one = [
        'MenuSet' => MenuSet::class,
        'Parent'  => MenuLink::class
    ];

    /**
     * Has_many relationship
     * @var array
     */
    private static $has_many = [
        'Children' => MenuLink::class
    ];

    /**
     * Defines summary fields commonly used in table columns
     * as a quick overview of the data for this dataobject
     * @var array
     */
    private static $summary_fields = [
        'Title'          => 'Title',
        'TypeLabel'      => 'Type',
        'LinkURL'        => 'Link',
        'Children.Count' => 'Children'
    ];

    /**
     * Default sort ordering
     * @var array
     */
    private static $default_sort = 'Sort ASC';

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        if (!$this->isAllowedChildren()) {
            return $fields;
        }
        $fields->addFieldsToTab(
            'Root.' . _t(__CLASS__ . '.CHILDREN', 'Children'),
            [
                GridField::create(
                    'Children',
                    _t(__CLASS__ . '.CHILDREN', 'Children'),
                    $this->Children(),
                    GridFieldConfig_RecordEditor::create()
                        ->addComponent(new GridFieldOrderableRows())
                )
            ]
        );

        return $fields;
    }

    /**
     * Inherit menuset from parent, if not directly assigned
     *
     * @return MenuSet
     */
    public function MenuSet()
    {
        if ($this->ParentID) {
            return $this->Parent()->MenuSet();
        }
        /** @var MenuSet $menuSet */
        $menuSet = $this->getComponent('MenuSet');
        return $menuSet;
    }

    /**
     * Checks if the menu allows child links.
     * @return Boolean
     */
    public function isAllowedChildren()
    {
        return $this->isInDB() && $this->MenuSet()->AllowChildren;
    }

    /**
     * Relationship accessor for Graphql
     *
     * @return MenuLink|null
     */
    public function getParent()
    {
        if ($this->ParentID) {
            return $this->Parent();
        }
        return null;
    }

    /**
     * Returns the classes for this link.
     * @return string
     */
    public function getClass()
    {
        $this->setClass($this->LinkingMode());
        return parent::getClass();
    }

    /**
     * DataObject view permissions
     * @param Member $member
     * @return boolean
     */
    public function canView($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }
        return true;
    }

    /**
     * DataObject edit permissions
     * @param Member $member
     * @return boolean
     */
    public function canEdit($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }
        return $this->MenuSet()->canEdit($member);
    }

    /**
     * DataObject delete permissions
     * @param Member $member
     * @return boolean
     */
    public function canDelete($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }
        return $this->MenuSet()->canEdit($member);
    }

    /**
     * DataObject create permissions
     * @param Member $member
     * @param array $context Additional context-specific data which might
     *                        affect whether (or where) this object could be created.
     * @return boolean
     */
    public function canCreate($member = null, $context = [])
    {
        $extended = $this->extendedCan(__FUNCTION__, $member, $context);
        if ($extended !== null) {
            return $extended;
        }
        if (isset($context['Parent'])) {
            return $context['Parent']->canEdit();
        }
        return $this->MenuSet()->canEdit();
    }

    /**
     * Return the first menulink matching the given MenuSet and SiteTreeID.
     *
     * @param gorriecoe\Menu\Models\MenuSet|String
     * @param Int
     *
     * @return gorriecoe\Menu\Models\MenuLink|Null
     */
    public static function get_by_sitetreeID($menuSet, int $siteTreeID)
    {
        if (!$menuSet instanceof MenuSet) {
            $menuSet = MenuSet::get_by_slug($menuSet);
        }
        if (!$menuSet) {
            return;
        }
        return DataObject::get_one(self::class, [
            'Type'       => 'SiteTree', // Ensures the admin hasn't intentionally changed this link
            'MenuSetID'  => $menuSet->ID,
            'SiteTreeID' => $siteTreeID
        ]);
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        // When writing initial record, set position to last in menu
        if (!$this->isInDB() && is_null($this->Sort)) {
            $this->Sort = $this->getSiblings()->max('Sort') + 1;
        }
    }

    /**
     * Get sibling links
     *
     * @return DataList|MenuLink[]
     */
    public function getSiblings(): DataList
    {
        $siblings = static::get();
        if ($this->ParentID) {
            $siblings = $siblings->filter('ParentID', $this->ParentID);
        }
        if ($this->MenuSetID) {
            $siblings = $siblings->filter('MenuSetID', $this->MenuSetID);
        }
        return $siblings;
    }
}
