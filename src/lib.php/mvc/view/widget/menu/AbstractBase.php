<?php
namespace jc\mvc\view\widget\menu;

use jc\mvc\view\widget\FormWidget;

abstract class AbstractBase extends FormWidget
{
	abstract public function depth();
	abstract protected function parent();
	abstract protected function showdepth();
	abstract public function getCssClassString();
	protected function cssClassBase(){
		return 'jc-mvc-view-widget-menu-';
	}
	public function showId($bIncrease=false){
		if($bIncrease){
			$this->sShowId =$this->id().'.'.self::$nAutoIncreaseId++;
		}
		return $this->sShowId;
	}
	private $sShowId='';
	static private $nAutoIncreaseId=0;
}
