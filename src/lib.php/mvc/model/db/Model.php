<?php
namespace jc\mvc\model\db ;

use jc\db\sql\MultiTableStatement;

use jc\mvc\model\db\orm\AssociationPrototype;

use jc\mvc\model\db\orm\ModelPrototype ;
use jc\db\sql\IDriver ;
use jc\mvc\model\Model as ModelBase ;

class Model extends ModelBase implements IModel
{
	
	/**
	 * @return jc\mvc\model\db\orm\ModelPrototype
	 */
	public function prototype()
	{
		return $this->aPrototype ;
	}

	public function setPrototype(ModelPrototype $aPrototype=null)
	{
		$this->aPrototype = $aPrototype ;
	}

	public function insert(IDriver $aDB,ModelPrototype $aPrototype=null,$bReplace=false) 
	{
		
	}

	public function delete(IDriver $aDB,ModelPrototype $aPrototype=null,$sWhere=null) 
	{}

	public function select(IDriver $aDB,ModelPrototype $aPrototype=null,$primaryKeyValues=null,$sWhere=null) 
	{

		
		
	}
	
	public function update(IDriver $aDB,ModelPrototype $aPrototype=null,$sWhere=null) 
	{}
	
	
	private $aPrototype ;
}

?>