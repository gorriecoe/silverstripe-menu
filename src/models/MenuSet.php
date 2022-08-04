<?php

namespace gorriecoe\Menu\Models;

use GraphQL\Type\Definition\ResolveInfo;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * MenuSet
 *
 * @property string $Title
 * @property string $Slug
 * @property bool $AllowChildren
 * @method HasManyList|MenuLink[] Links()
 * @package silverstripe-menu
 */
class MenuSet extends DataObject implements PermissionProvider
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
    private static $db = [
        'Title'         => 'Varchar(255)',
        'Slug'          => 'Varchar(255)',
        'AllowChildren' => 'Boolean'
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
        'Title'       => 'Title',
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
                $this->Links,
                GridFieldConfig_RecordEditor::create()
                    ->addComponent(new GridFieldOrderableRows('Sort'))
            )
        );

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }

    /**
     * Return a map of permission codes to add to the dropdown shown in the Security section of the CMS
     * @return array
     */
    public function providePermissions()
    {
        $permissions = [];
        foreach (MenuSet::get() as $menuset) {
            $key = $menuset->PermissionKey();
            $permissions[$key] = [
                'name'     => _t(
                    __CLASS__ . '.EDITMENUSET',
                    "Manage links with in '{name}'",
                    [
                        'name' => $menuset->obj('Title')
                    ]
                ),
                'category' => _t(__CLASS__ . '.MENUSETS', 'Menu sets')
            ];
        }
        return $permissions;
    }

    /**
     * @return string
     */
    public function PermissionKey()
    {
        return $this->obj('Slug')->Uppercase() . 'EDIT';
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
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

        // Restrict permissions based on saved key
        if ($this->isInDB()) {
            return Permission::check($this->PermissionKey(), 'any', $member);
        }

        // If canEdit() is called on an unsaved singleton, default to any users with CMS access
        // This allows MenuLink objects to be created via gridfield,
        // which will call the singleton MenuSet::canEdit()
        return Permission::check('CMS_ACCESS', 'any', $member);
    }

    /**
     * Viewing Permissions
     * @param mixed $member
     * @return boolean
     */
    public function canView($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }
        return Permission::check($this->PermissionKey(), 'any', $member);
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
                $allowChildren = isset($options['allow_children']) ? $options['allow_children'] : false;
            } else {
                $title = $options;
                $allowChildren = false;
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
            $record->AllowChildren = $allowChildren;
            $record->write();
        }
    }

    /**
     * Generates a link to edit this page in the CMS.
     *
     * @return string
     */
    public function CMSEditLink()
    {
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
     * Return the first menuset matching the given slug.
     *
     * @return gorriecoe\Menu\Models\MenuSet|Null
     */
    public static function get_by_slug($slug)
    {
        if ($slug) {
            return self::get()->find('Slug', $slug);
        }
    }

    /**
     * Relationship accessor for Graphql
     * @return ManyManyList MenuLink
     */
    public function getLinks()
    {
        return $this->Links()->filter([
            'ParentID' => 0
        ]);
    }
}
