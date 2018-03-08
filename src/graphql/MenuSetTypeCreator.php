<?php

namespace gorriecoe\Menu\GraphQL;

use GraphQL\Type\Definition\Type;
use SilverStripe\GraphQL\TypeCreator;
use SilverStripe\GraphQL\Pagination\Connection;

/**
 * MenuSetTypeCreator
 *
 * @package silverstripe-menu
 */
class MenuSetTypeCreator extends TypeCreator
{
    public function attributes()
    {
        return [
            'name' => 'menuset'
        ];
    }

    public function fields()
    {
        return [
            'ID' => [
                'type' => Type::nonNull(Type::id())
            ],
            'ClassName' => [
                'type' => Type::string()
            ],
            'Title' => [
                'type' => Type::string()
            ],
            'Slug' => [
                'type' => Type::string()
            ],
            'Links' => [
                'type' => Type::listOf($this->manager->getType('menulink'))
            ]
        ];
    }
}
