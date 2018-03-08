<?php

namespace gorriecoe\Menu\GraphQL;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use SilverStripe\GraphQL\OperationResolver;
use SilverStripe\GraphQL\QueryCreator;
use gorriecoe\Menu\Models\MenuSet;

/**
 * MenuSetsQueryCreator
 *
 * @package silverstripe-menu
 */
class MenuSetsQueryCreator extends QueryCreator implements OperationResolver
{
    public function attributes()
    {
        return [
            'name' => 'menusets'
        ];
    }

    public function args()
    {
        $args = parent::args();

        return $args;
    }

    public function type()
    {
        return Type::listOf($this->manager->getType('menuset'));
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
        return MenuSet::get();
    }
}
