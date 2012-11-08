# ExcludeChildren Module

## Requirements

 * SilverStripe 3.0 or newer

## Introduction

Provides an extension to limit subpages shown in sitetree,
adapted from: http://www.dio5.com/blog/limiting-subpages-in-silverstripe/

## Features

  * Configure page classes to hide under current page

## Usage

	class SubPageHolder extends Page {
		...
		static $extensions = array("ExcludeChildren");
		static $excluded_children = array('SubPage', 'AnotherPageType_Extending_Page');
		...

Then, add a GridField instead to create/edit subpages:

	$gridFieldConfig = GridFieldConfig::create()->addComponents(
		new GridFieldToolbarHeader(),
		new GridFieldAddNewButton('toolbar-header-right'),
		new GridFieldSortableHeader(),
		new GridFieldDataColumns(),
		new GridFieldPaginator(20),
		new GridFieldEditButton(),
		new GridFieldDeleteAction(),
		new GridFieldDetailForm()
	);
	$gridField = new GridField("SubPages", "SubPages of this page", 
			$this->SubPages(), $gridFieldConfig);
	$fields->addFieldToTab("Root.SubPages", $gridField);

## @TODO

If anyone knows of a way to have the page's edit form appear with the normal Settings & History tab, that'd be awesome!