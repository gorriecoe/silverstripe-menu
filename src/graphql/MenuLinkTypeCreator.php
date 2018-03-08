<?php

namespace gorriecoe\Menu\GraphQL;

use GraphQL\Type\Definition\Type;
use SilverStripe\GraphQL\TypeCreator;
use SilverStripe\GraphQL\Pagination\Connection;
use gorriecoe\Link\GraphQL\LinkTypeCreator;

/**
 * MenuLinkTypeCreator
 *
 * @package silverstripe-menu
 */
class MenuLinkTypeCreator extends LinkTypeCreator
{
    public function attributes()
    {
        return [
            'name' => 'menulink'
        ];
    }

    public function fields()
    {
        $fields = [
            'Sort' => [
                'type' => Type::int()
            ],
            'ParentID' => [
                'type' => Type::int()
            ],
            'Parent' => [
                'type' => $this->manager->getType('menulink')
            ],
            'Children' => [
                'type' => Type::listOf($this->manager->getType('menulink'))
            ]
        ];

        return array_merge(parent::fields(), $fields);
    }
}
