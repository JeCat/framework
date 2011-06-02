<?php
namespace jc\mvc\model\db ;

use jc\db\IRecordSet;
use jc\mvc\model\db\orm\ModelPrototype;
use jc\mvc\model\IModel as IModelBase ;

interface IModel extends IModelBase
{
	/**
	 * @return jc\mvc\model\db\orm\ModelPrototype
	 */
	public function prototype() ;

	public function setPrototype(ModelPrototype $aPrototype) ;
	
	public function loadData( IRecordSet $aRecordSet, $nRowIdx=0, $sClmPrefix=null) ;
	
}

?>