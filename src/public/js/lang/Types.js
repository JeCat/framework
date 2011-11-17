jc.lang.Type = {
	
	jc.lang.string = 'string'
	jc.lang.int = 'int'
	jc.lang.string = 'string'
		
	isA : function (type,variable)
	{
		switch( typeof(type) )
		{
		case "string" :
			return typeof(variable)==type ;
			
		case "function" :

			// 检查接口
			if( type instanceof jc.lang.Interface )
			{
				return type.isInstance(variable) ;
			}
			
			// 检查类
			else
			{
				return variable instanceof type ;
			}
		default :
			throw new TypeError("arg 'type' must be string or function") ;		
		}
		
		return false ;
	}


}

a=12 ;
switch(a)
{
case 12 :
	alert("bingo")
	break ;
}