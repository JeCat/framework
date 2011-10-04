<?php
namespace jc\mvc\model\db ;

use jc\db\recordset\IRecordSet;
use jc\mvc\model\db\orm\PrototypeInFragment;
use jc\mvc\model\IModel as IModelBase ;

interface IModel extends IModelBase
{
	public function __construct($prototype=null) ;
	
	/**
	 * @return jc\mvc\model\db\orm\PrototypeInFragment
	 */
	public function prototype() ;

	public function setPrototype(PrototypeInFragment $aPrototype) ;
	
	public function loadData( IRecordSet $aRecordSet, $bSetSerialized=false ) ;
	
	/**
	 * @return jc\mvc\model\db\orm\Criteria;
	 */
	public function criteria($bAutoCreate=true) ;
}

?>
