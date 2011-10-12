<?php
namespace jc\mvc\model\db ;

use jc\mvc\model\IModel as IModelBase ;
use jc\mvc\model\db\orm\Prototype;

interface IModel extends IModelBase
{
	/**
	 * @return jc\mvc\model\db\orm\PrototypeInFragment
	 */
	public function prototype() ;

	public function setPrototype(Prototype $aPrototype) ;
}
