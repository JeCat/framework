<?php
namespace jc\mvc\model\db ;

use jc\mvc\model\IModel as IModelBase ;

interface IModel extends IModelBase
{
	/**
	 * @return jc\mvc\model\db\orm\PrototypeInFragment
	 */
	public function prototype() ;

	public function setPrototype(PrototypeInFragment $aPrototype) ;
}
