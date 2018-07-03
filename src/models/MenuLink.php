<?php

namespace gorriecoe\Menu\Models;

use gorriecoe\Link\Models\Link;
use gorriecoe\Menu\Admin\MenuSetAdmin;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\Security\Permission;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * MenuLink
 *
 * @package silverstripe-menu
 *
 * @property int ParentID
 * @property int MenuSetID
 * @method \SilverStripe\ORM\DataList|MenuLink[] Children()
 * @method MenuSet|null MenuSet()
 * @method MenuLink|null Parent()
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
        'Sort' => DBInt::class,
    ];

    /**
     * Has_one relationship
     * @var array
     */
    private static $has_one = [
        'MenuSet' => MenuSet::class,
        'Parent' => MenuLink::class
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
        'Title' => 'Title',
        'TypeLabel' => 'Type',
        'LinkURL' => 'Link',
        'Children.Count' => 'Children'
    ];

    /**
     * Default sort ordering
     * @var array
     */
    private static $default_sort = ['Sort' => 'ASC'];

    /**
     * CMS Fields
     * @return \SilverStripe\Forms\FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        if (!$this->isNestable()) {
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
     * Event handler called after writing to the database.
     */
    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if ($this->ParentID > 0) {
            $this->MenuSetID = $this->Parent()->MenuSetID;
        }
    }

    /**
     * Checks if the menu allows nested links.
     * @return Boolean
     */
    public function isNestable()
    {
        return $this->MenuSet()->Nested;
    }

    /**
     * Relationship accessor for Graphql
     * @return MenuLink|null
     */
    public function getParent()
    {
        return $this->ParentID
            ? $this->Parent()
            : null;
    }

    /**
     * Relationship accessor for Graphql
     * @return \SilverStripe\ORM\ManyManyList|MenuLink[]
     */
    public function getChildren()
    {
        return $this->Children();
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
     * @inheritDoc
     */
    public function canView($member = null)
    {
        return Permission::check(MenuSetAdmin::CMS_ACCESS_PERMISSION, 'any', $member);
    }

    /**
     * @inheritDoc
     */
    public function canEdit($member = null)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function canDelete($member = null)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function canCreate($member = null, $context = [])
    {
        return Permission::check(MenuSetAdmin::CMS_ACCESS_PERMISSION, 'any', $member);
    }
}
