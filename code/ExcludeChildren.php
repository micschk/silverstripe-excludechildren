<?php
/**
 * Provides an extension to limit subpages shown in sitetree,
 * adapted from: http://www.dio5.com/blog/limiting-subpages-in-silverstripe/
 *
 * Features:
 * - Configure page classes to hide under current page
 * 
 * Example from within a class:
 * <code>
 * class SubPageHolder extends Page {
 *		...
 *		static $extensions = array("ExcludeChildren");
 *		static $excluded_children = array('SubPage', 'Another');
 *		...
 * </code>
 * 
 * Or externally via _config.php:
 * 
 * <code>
 * 	Object::add_extension("BlogHolder", "ExcludeChildren");
 * 	Config::inst()->update("BlogHolder", "excluded_children", array("BlogEntry"));
 * </code>
 * 
 * @author Michael van Schaik, Restruct. <substr($firstname,0,3)@restruct-web.nl>
 * @author Tim Klein, Dodat Ltd <$firstname@dodat.co.nz>
 * @package Hierarchy
 * @subpackage HideChildren
 */

class ExcludeChildren extends Hierarchy{
	
	protected $hiddenChildren = array();

	public function getExcludedClasses(){
		$this->hiddenChildren = $this->owner->config()->get("excluded_children");
		return $this->hiddenChildren;
	}
	
	public function stageChildren($showAll = false) {
		$staged = parent::stageChildren($showAll);
		$staged->exclude('ClassName', $this->getExcludedClasses());
		return $staged;
	}
	
	public function liveChildren($showAll = false, $onlyDeletedFromStage = false) {
		$staged = parent::liveChildren($showAll, $onlyDeletedFromStage);
		$staged->exclude('ClassName', $this->getExcludedClasses());
		return $staged;
	}
	
}