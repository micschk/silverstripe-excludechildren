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
	static $ExcludeChildren = array('SubPage', 'Another');
	...