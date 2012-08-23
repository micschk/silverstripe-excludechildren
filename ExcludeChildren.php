<?php
/**
 * Provides an extension to limit subpages shown in sitetree,
 * adapted from: http://www.dio5.com/blog/limiting-subpages-in-silverstripe/
 *
 * Features:
 * - Configure page classes to hide under current page
 * 
 * Example:
 * <code>
 * class SubPageHolder extends Page {
 *		...
 *		static $extensions = array("ExcludeChildren");
 *		static $ExcludeChildren = array('SubPage', 'Another');
 *		...
 * </code>
 * 
 * @author Michael van Schaik, Restruct. <substr($firstname,0,3)@restruct-web.nl)
 * @package Hierarchy
 * @subpackage HideChildren
 */

class ExcludeChildren extends Hierarchy{
	
	protected $hiddenChildren = array();
    
	public function getExcludedClasses(){
		$owner = $this->owner;
		if(property_exists ( $owner , 'ExcludeChildren' )){
			$this->hiddenChildren = $owner::$ExcludeChildren;
		}
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