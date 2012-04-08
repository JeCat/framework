<?php
namespace org\jecat\framework\db\sql ;

class Delete extends MultiTableSQL
{
	public function __construct($sTableName="")
	{
		$this->arrRawSql = array(
				'expr_type' => 'query' ,
				'subtree' => array( 'DELETE' ) ,
				'command' => 'DELETE' ,
		) ;
		
		$this->addTable($sTableName) ;
	}
}

?>