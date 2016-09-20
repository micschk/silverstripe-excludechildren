<?php
/**
 * Provides an extension to limit subpages shown in sitetree,
 * adapted from: http://www.dio5.com/blog/limiting-subpages-in-silverstripe/
 * 
 * @author Michael van Schaik, Restruct. <mic@restruct.nl>
 * @author Tim Klein, Dodat Ltd <$firstname@dodat.co.nz>
 */

class ExcludeChildren extends DataExtension {
	
	protected $hiddenChildren = array();

	public function getExcludedClasses(){
		$hiddenChildren = array();
		if ($configClasses = $this->owner->config()->get("excluded_children")) {
			foreach ($configClasses as $class) {
				$hiddenChildren = array_merge($hiddenChildren, array_values(ClassInfo::subclassesFor($class)));
			}
		}
		$this->hiddenChildren = $hiddenChildren; 
		return $this->hiddenChildren;
	}
	
	public function getFilteredChildren($children) {
		// Optionally force exclusion beyond CMS (eg. exclude from $Children as well)
		$controller = Controller::curr();
		$action = $controller->getAction();

		// Check for TreeDropdownField's "tree" allowed_action
		$allParams = ($controller) ? $controller->getRequest()->allParams() : array();
		$treeDropdownFieldAction = ($allParams && isset($allParams['Action'])) ? $allParams['Action'] : null;

		if ($this->owner->config()->get("force_exclusion_beyond_cms")
			|| ($controller instanceof LeftAndMain
				&& ($treeDropdownFieldAction === 'tree' || in_array($action, array('treeview', 'listview', 'getsubtree'))))
		) {
			//if the page class has a getExcludedChildren function, use it to supply the list of children
			if ($this->owner->hasMethod('getExcludedChildren')) {
				return $this->owner->getExcludedChildren($children);
			}

			return $children->exclude('ClassName', $this->getExcludedClasses());
		}

		return $children;
	}

	public function stageChildren($showAll = false){
		$children = $this->hierarchyStageChildren($showAll);
		return $this->getFilteredChildren($children);
	}

	public function liveChildren($showAll = false, $onlyDeletedFromStage = false){
		$children = $this->hierarchyLiveChildren($showAll, $onlyDeletedFromStage);
		return $this->getFilteredChildren($children);
	}
	
	/**
	 * Duplicated & renamed from the Hierarchy::tageChildren() because we're overriding the original method:
	 * Return children from the stage site
	 * 
	 * @param showAll Inlcude all of the elements, even those not shown in the menus.
	 *   (only applicable when extension is applied to {@link SiteTree}).
	 * @return DataList
	 */
	public function hierarchyStageChildren($showAll = false) {
		$baseClass = ClassInfo::baseDataClass($this->owner->class);
		$staged = $baseClass::get()
			->filter('ParentID', (int)$this->owner->ID)
			->exclude('ID', (int)$this->owner->ID);
		if (!$showAll && $this->owner->db('ShowInMenus')) {
			$staged = $staged->filter('ShowInMenus', 1);
		}
		$this->owner->extend("augmentStageChildren", $staged, $showAll);
		return $staged;
	}
	
	/**
	 * Duplicated & renamed from the Hierarchy::liveChildren() because we're overriding the original method:
	 * Return children from the live site, if it exists.
	 * 
	 * @param boolean $showAll Include all of the elements, even those not shown in the menus.
	 *   (only applicable when extension is applied to {@link SiteTree}).
	 * @param boolean $onlyDeletedFromStage Only return items that have been deleted from stage
	 * @return SS_List
	 */
	public function hierarchyLiveChildren($showAll = false, $onlyDeletedFromStage = false) {
		if(!$this->owner->hasExtension('Versioned')) {
			throw new Exception('Hierarchy->liveChildren() only works with Versioned extension applied');
		}

		$baseClass = ClassInfo::baseDataClass($this->owner->class);
		$children = $baseClass::get()
			->filter('ParentID', (int)$this->owner->ID)
			->exclude('ID', (int)$this->owner->ID)
			->setDataQueryParam(array(
				'Versioned.mode' => $onlyDeletedFromStage ? 'stage_unique' : 'stage',
				'Versioned.stage' => 'Live'
			));
		
		if(!$showAll) $children = $children->filter('ShowInMenus', 1);

		return $children;
	}
	
}
