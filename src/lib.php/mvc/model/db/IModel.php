<?php
namespace jc\mvc\model\db ;

use jc\db\recordset\IRecordSet;
use jc\mvc\model\db\orm\PrototypeInFragment;
use jc\mvc\model\IModel as IModelBase ;

interface IModel extends IModelBase
{
	/**
	 * @return jc\mvc\model\db\orm\PrototypeInFragment
	 */
	public function prototype() ;

	public function setPrototype(PrototypeInFragment $aPrototype) ;
	
	public function loadData( IRecordSet $aRecordSet, $bSetSerialized=false ) ;
	
	/**
	 * @return jc\mvc\model\db\orm\Criteria;
	 */
	public function criteria() ;

	
	public function totalCount() ;
	
//	public function setLimit($nLength=1,$nFrom=0) ;
//	
//	public function limitFrom() ;
//	
//	public function limitLength() ;
}

?>