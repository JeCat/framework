<?php
namespace jc\mvc\model\db ;

use jc\mvc\model\db\orm\ModelPrototype;

use jc\mvc\model\IModel as IModelBase ;

interface IModel extends IModelBase
{

	/**
	 * @return jc\mvc\model\db\orm\ModelPrototype
	 */
	public function prototype() ;

	public function setPrototype(ModelPrototype $aPrototype) ;

}

?>