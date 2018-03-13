<?php

namespace gorriecoe\Menu\Models;

use gorriecoe\Link\Models\Link;
use gorriecoe\Menu\Models\MenuSet;
use gorriecoe\Menu\Models\MenuLink;
use SilverStripe\Control\Director;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Core\Convert;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * MenuLink
 *
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
        'LinkType' => 'Type',
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
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        if (!$this->Nested) {
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
     * Checks if the menu allows nested links.
     * @return Boolean
     */
    public function getNested()
    {
        return $this->MenuSet()->Nested;
    }

    /**
     * Relationship accessor for Graphql
     * @return MenuLink
     */
    public function getParent()
    {
        if ($this->ParentID) {
            return $this->Parent();
        }
    }

    /**
     * Relationship accessor for Graphql
     * @return ManyManyList MenuLink
     */
    public function getChildren()
    {
        return $this->Children();
    }

    /**
     * Returns the current page scope
     * @return Controller
     */
    public function getCurrentPage()
    {
        return Director::get_current_page();
    }

    /**
     * Returns the classes for this link.
     * @return string
     */
    public function getClass()
    {
        if ($this->Type == 'SiteTree'){
            if (isset($this->SiteTreeID) && $this->CurrentPage->ID == $this->SiteTreeID) {
                $this->setClass('current');
            } else {
                $this->setClass('link');
            }
        }

        return parent::getClass();
    }
}
