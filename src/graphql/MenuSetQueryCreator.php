<?php

namespace gorriecoe\Menu\GraphQL;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use SilverStripe\GraphQL\OperationResolver;
use SilverStripe\GraphQL\QueryCreator;
use gorriecoe\Menu\Models\MenuSet;

/**
 * MenuSetQueryCreator
 *
 * @package silverstripe-menu
 */
class MenuSetQueryCreator extends QueryCreator implements OperationResolver
{
    public function attributes()
    {
        return [
            'name' => 'menuset'
        ];
    }

    public function args()
    {
        return [
            'ID' => [
                'type' => Type::int()
            ],
            'Slug' => [
                'type' => Type::string()
            ]
        ];
    }

    public function type()
    {
        return $this->manager->getType('menuset');
    }

    public function resolve($object, array $args, $context, ResolveInfo $info)
    {
        $link = MenuSet::singleton();
        if (!$link->canView($context['currentUser'])) {
            throw new \InvalidArgumentException(sprintf(
                '%s view access not permitted',
                MenuSet::class
            ));
        }
        if (isset($args['ID'])) {
            return MenuSet::get()->find('ID', $args['ID']);
        }
        if (isset($args['Slug'])) {
            return MenuSet::get()->find('Slug', $args['Slug']);
        }
    }
}
