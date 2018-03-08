<?php

namespace gorriecoe\Menu\GraphQL;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use SilverStripe\GraphQL\OperationResolver;
use SilverStripe\GraphQL\QueryCreator;
use gorriecoe\Menu\Models\MenuLink;
use gorriecoe\Link\GraphQL\LinkQueryCreator;

/**
 * MenuLinkQueryCreator
 *
 * @package silverstripe-menu
 */
class MenuLinkQueryCreator extends LinkQueryCreator implements OperationResolver
{
    public function attributes()
    {
        return [
            'name' => 'menulink'
        ];
    }

    public function args()
    {
        return [
            'ID' => [
                'type' => Type::int()
            ]
        ];
    }

    public function type()
    {
        return $this->manager->getType('menulink');
    }

    public function resolve($object, array $args, $context, ResolveInfo $info)
    {
        $link = MenuLink::singleton();
        if (!$link->canView($context['currentUser'])) {
            throw new \InvalidArgumentException(sprintf(
                '%s view access not permitted',
                MenuLink::class
            ));
        }
        if (isset($args['ID'])) {
            return MenuSet::get()->find('ID', $args['ID']);
        }
    }
}
