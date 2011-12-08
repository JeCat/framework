<?php
namespace org\jecat\framework\mvc\view\widget\menu;

use org\jecat\framework\mvc\view\widget\FormWidget;

abstract class AbstractBase extends FormWidget
{
	abstract public function depth();
	abstract protected function parent();
	abstract protected function showdepth();
	abstract public function getCssClassString();
	
	const CSS_CLASS_BASE = 'jc-widget-menu' ;
}
