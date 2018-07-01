# Silverstripe menus


## Installation
Composer is the recommended way of installing SilverStripe modules.
```
composer require gorriecoe/silverstripe-menu
```

## Requirements

- silverstripe/framework ^4.0
- symbiote/silverstripe-gridfieldextensions ^3.1
- gorriecoe/silverstripe-link ^1.1

## Maintainers

- [Gorrie Coe](https://github.com/gorriecoe)

## Creating custom menus

As it is common to reference MenuSets by name in templates, you can configure sets to be created automatically during the /dev/build task. These sets cannot be deleted through the CMS.

```
gorriecoe\Menu\Models\MenuSet:
  sets:
    main: Main menu
    footer:
      title: Footer menu
      nested: false
```

## Adding links to menus

Once you have created your menus you can add links.

## Usage in template

```
<ul>
    <% loop MenuSet('footer') %>
        <li>
            {$Me}
        </li>
    <% end_loop %>
</ul>
```

See [silverstripe link](https://github.com/gorriecoe/silverstripe-link) for more template options.
