Extension to hide pages from the SiteTree
=========================================

Configure childpages (children) to be hidden from the sitetree under their parent page

## Requirements

 * SilverStripe 3.0 or newer


## Screenshot
*Hide SiteTree items from from the sitetree (and, with some extra code/modules, manage them from a GridField):*
![](images/screenshots/holderscreen.png)


## Installation

```
composer require micschk/silverstripe-excludechildren dev-master
```

## Usage

In config.yml (best):

```yaml
---
Only:
  classexists: 'ExcludeChildren'
---
SubPageHolder:
  extensions:
	- 'ExcludeChildren'
  excluded_children:
	- 'SubPage'
	- 'AnotherPageType'
  # optionally exclude from theme $Children as well (set to true if desired, default only from CMS)
  # eg. to exclude pre-existing child pages with 'show in menus' = true
  force_exclusion_beyond_cms: false
```

Or in your Page class (php):

```php
	class SubPageHolder extends Page {
		...
		static $extensions = array("ExcludeChildren");
		static $excluded_children = array('SubPage', 'AnotherPageType_Extending_Page');
		...
```

Or externally via _config.php:

```php
		Object::add_extension("SubPageHolder", "ExcludeChildren");
		Config::inst()->update("SubPageHolder", "excluded_children", array("BlogEntry"));
```

###Then, add a GridField instead to create/edit subpages
(See Gridfieldpages module below for a turnkey solution/example)
```php
	$gridFieldConfig = GridFieldConfig::create()->addComponents(
		new GridFieldToolbarHeader(),
		new GridFieldAddNewSiteTreeItemButton('toolbar-header-right'), // GridfieldSitetreebuttons module
		new GridFieldSortableHeader(),
		new GridFieldFilterHeader(),
		$dataColumns = new GridFieldDataColumns(),
		new GridFieldPaginator(20),
		new GridFieldEditSiteTreeItemButton(), // GridfieldSitetreebuttons module
		new GridFieldOrderableRows() // Gridfieldextensions module, default 'Sort' is equal to page sort field...
	);
	$dataColumns->setDisplayFields(array(
		'Title' => 'Title',
		'URLSegment'=> 'URL',
		//'getStatus' => 'Status', // Implement getStatus() on child page class, see gridfieldpages module for an example
		'LastEdited' => 'Changed',
	));
	// use gridfield as normal
	$gridField = new GridField(
		"SubPages", # Can be any name, field doesn't have to exist on model...
		"SubPages of this page", 
        SiteTree::get()->filter('ParentID', $this->ID),
		$gridFieldConfig);
    $fields->addFieldToTab("Root.SubPages", $gridField);
```

## Looping over $Children in templates

This module only hides child pages from the CMS sitetree by default. So you can just use $Children as usual in your theme. Child pages will also be available when creating links to pages from the CMS editor. 

When excluding pages from the front-end as well (force_exclusion_beyond_cms), you can add an alternative getter to your Holder:

```php
	public function SortedChildren(){
		return SiteTree::get()->filter('ParentID', $this->ID)->sort('Sort');
	}
```

Or, paginated:

```php
	public function PaginatedChildren(){
		$children = SiteTree::get()->filter('ParentID', $this->ID);
		$ctrlr = Controller::curr();
		$children = new PaginatedList($children, $ctrlr->request);
		$children->setPageLength(10);
		return $children;
	}
```

Things to check if your pages are not showing up in $Children:
- is force_exclusion_beyond_cms set to false (or use custom getter)?
- are your child pages set to appear in menu's (show in menu's)?

## Customising your children

If you need to customise your hidden children by more than just classname you can implement the `getExcludedChildren` which needs to return a `DataList` of the children *to show* in the SiteTree.


## Pro tip

Add GridfieldSitetreebuttons to your gridfieldconfig to edit the pages in their regular edit forms:
* [silverstripe-gridfieldsitetreebuttons](https://github.com/micschk/silverstripe-gridfieldsitetreebuttons)

Or use/subclass the preconfigured GridfieldPages module, which contains both excludechildren, sitetreebuttons, sorting and publication status:
* [silverstripe-gridfieldpages](https://github.com/micschk/silverstripe-gridfieldpages)
