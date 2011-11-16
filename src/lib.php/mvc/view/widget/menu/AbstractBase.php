<?php
namespace jc\mvc\view\widget\menu;

use jc\mvc\view\widget\FormWidget;

abstract class AbstractBase extends FormWidget
{
	abstract public function depth();
	abstract protected function parent();
	abstract protected function showdepth();
}
