<?php
namespace org\jecat\framework\mvc\model\db ;

use org\jecat\framework\mvc\model\IModel as IModelBase ;
use org\jecat\framework\mvc\model\db\orm\Prototype;

interface IModel extends IModelBase
{
	/**
	 * @return org\jecat\framework\mvc\model\db\orm\Prototype
	 */
	public function prototype() ;

	public function setPrototype(Prototype $aPrototype) ;
}
