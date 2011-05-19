<?php
namespace jc\mvc\model\db\orm ;

use jc\util\HashTable;

use jc\lang\Object;


/**
 * 模型关系原型图
 * @author alee
 *
 */
class ModelRelationPrototypeMap extends Object
{
	public function __construct()
	{
		parent::__construct() ;
		
		$this->aModelPrototypes = new HashTable() ;		
	}
	
	public function addModelPrototype(ModelPrototype $aPrototype) 
	{
		$this->aModelPrototypes->set(
			$aPrototype->name()
			, $aPrototype
		) ;
	}
	
	public function modelPrototypes()
	{
		return $this->aModelPrototypes ;
	}
	
	
	private $aModelPrototypes ;
}

?>