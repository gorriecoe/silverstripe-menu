<?php

namespace gorriecoe\Menu\Models;

use gorriecoe\Menu\Admin\MenuSetAdmin;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\Security\Permission;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Controller;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * MenuSet
 *
 * @package silverstripe-menu
 * @property string $Title
 * @property string $Slug
 * @property boolean $Nested
 *
 * @method \SilverStripe\ORM\HasManyList|MenuLink[] Links()
 */
class MenuSet extends DataObject
{
    /**
     * Defines the database table name
     * @var string
     */
    private static $table_name = 'MenuSet';

    /**
     * Singular name for CMS
     * @var string
     */
    private static $singular_name = 'Menu';

    /**
     * Plural name for CMS
     * @var string
     */
    private static $plural_name = 'Menus';

    /**
     * Database fields
     * @var array
     */
    /**
     * Database fields
     * @var array
     */
    private static $db = [
        'Title' => DBVarchar::class,
        'Slug' => DBVarchar::class,
        'Nested' => DBBoolean::class,
    ];

    /**
     * Has_many relationship
     * @var array
     */
    private static $has_many = [
        'Links' => MenuLink::class,
    ];

    /**
     * Defines summary fields commonly used in table columns
     * as a quick overview of the data for this dataobject
     * @var array
     */
    private static $summary_fields = [
        'Title' => 'Title',
        'Links.Count' => 'Links'
    ];

    /**
     * Defines a default list of filters for the search context
     * @var array
     */
    private static $searchable_fields = [
        'Title'
    ];

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = FieldList::create(
            TabSet::create(
                'Root',
                Tab::create('Main')
            )
            ->setTitle(_t(__CLASS__ . '.TABMAIN', 'Main'))
        );

        $fields->addFieldToTab(
            'Root.Main',
            GridField::create(
                'Links',
                _t(__CLASS__ . '.FIELDLINKS', 'Links'),
                $this->Links(),
                GridFieldConfig_RecordEditor::create()
                    ->addComponent(new GridFieldOrderableRows('Sort'))
            )
        );

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }

    /**
     * Creating Permissions.
     * This module is not intended to allow creating menus via CMS.
     * @return boolean
     */
    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    /**
     * Deleting Permissions
     * This module is not intended to allow deleting menus via CMS
     * @param mixed $member
     * @return boolean
     */
    public function canDelete($member = null)
    {
        return false;
    }

    /**
     * Editing Permissions
     * @param mixed $member
     * @return boolean
     */
    public function canEdit($member = null)
    {
        return Permission::check(MenuSetAdmin::CMS_ACCESS_PERMISSION, 'any', $member);
    }

    /**
     * Viewing Permissions
     * @param mixed $member
     * @return boolean
     */
    public function canView($member = null)
    {
        return Permission::check(MenuSetAdmin::CMS_ACCESS_PERMISSION, 'any', $member);
    }

    /**
     * Set up default records based on the yaml config
     */
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        $default_menu_sets = $this->config()->get('sets') ?: array();
        foreach ($default_menu_sets as $slug => $options) {
            if (is_array($options)) {
                $title = $options['title'];
                $nested = isset($options['nested']) ? $options['nested'] : true;
            } else {
                $title = $options;
                $nested = true;
            }
            $slug = Convert::raw2htmlid($slug);
            $record = MenuSet::get()->find('Slug', $slug);
            if (!$record) {
                $record = MenuSet::create();
                DB::alteration_message("Menu '$title' created", 'created');
            } else {
                DB::alteration_message("Menu '$title' updated", 'updated');
            }
            $record->Slug = $slug;
            $record->Title = $title;
            $record->Nested = $nested;
            $record->write();
        }
    }

    /**
     * Generates a link to edit this page in the CMS.
     *
     * @return string
     */
    public function CMSEditLink() {
        return Controller::join_links(
            Controller::curr()->Link(),
            'EditForm',
            'field',
            $this->ClassName,
            'item',
            $this->ID
        );
    }

    /**
     * Relationship accessor for Graphql
     * @return \SilverStripe\ORM\ManyManyList MenuLink
     */
    public function getLinks()
    {
        return $this->Links()->filter([
            'ParentID' => 0
        ]);
    }
}
