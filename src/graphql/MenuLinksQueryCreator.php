<?php

namespace gorriecoe\Menu\GraphQL;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use SilverStripe\GraphQL\OperationResolver;
use SilverStripe\GraphQL\QueryCreator;
use gorriecoe\Menu\Models\MenuLink;
use gorriecoe\Link\GraphQL\LinksQueryCreator;

/**
 * MenuLinksQueryCreator
 *
 * @package silverstripe-menu
 */
class MenuLinksQueryCreator extends LinksQueryCreator implements OperationResolver
{
    public function attributes()
    {
        return [
            'name' => 'menulinks'
        ];
    }

    public function args()
    {
        $args = parent::args();

        return $args;
    }

    public function type()
    {
        return Type::listOf($this->manager->getType('menulink'));
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

        $filters = [
            'ID',
            'Title:PartialMatch',
            'Type',
            'URL:PartialMatch',
            'Email:PartialMatch',
            'Phone',
            'OpenInNewWindow',
        ];

        return $this->Filter(MenuLink::get(), $filters,  $args);
    }
}
