<?php
namespace jc\db ;

interface IRecordSet
{
	public function rowCount() ;
	
	public function fetch() ;
	
	public function row( $nRow=0 ) ;
	
	public function allRows() ;

	public function field($nRow,$sFieldName) ;
	
	public function iterator() ;

	public function fieldIterator() ;
	
}

?>