<?php
namespace jc\db\recordset ;

interface IRecordSet
{
	public function rowCount() ;
	
	public function fetch() ;
	
	public function row( $nRow=-1 ) ;
	
	public function allRows() ;

	public function field($sFieldName) ;
	
	public function iterator() ;

	public function fieldIterator() ;
	
}

?>