<?php 

namespace jc\db\sql ;

interface ISQLStatementFrom extends ISQLStatement
{
	const JOIN_LEFT = "JOIN LEFT" ;
	const JOIN_RIGHT = "JOIN RIGHT" ;
	const JOIN_INNER = "JOIN INNER" ;
	
	
}

?>