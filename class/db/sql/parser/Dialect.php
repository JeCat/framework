<?php
namespace org\jecat\framework\db\sql\parser ;

use org\jecat\framework\lang\Object;

class Dialect extends Object
{
	const ANSI = 'ANSI' ;
	const MySQL = 'MySQL' ;
	
	public function __construct($sDialect=self::MySQL)
	{
		$this->sDialect = $sDialect ;
	}
	public function commands()
	{
		return self::$arrDialects[$this->sDialect]['commands'] ;
	}
	public function isCommand($sToken)
	{
		$sToken = strtolower($sToken) ;
		return in_array($sToken,self::$arrDialects[$this->sDialect]['commands']) ;
	}
	
	public function operators()
	{
		return self::$arrDialects[$this->sDialect]['operators'] ;
	}
	public function isOperator($sToken)
	{
		$sToken = strtolower($sToken) ;
		return in_array($sToken,self::$arrDialects[$this->sDialect]['operators']) ;
	}
	
	public function types()
	{
		return self::$arrDialects[$this->sDialect]['types'] ;
	}
	public function isType($sToken)
	{
		$sToken = strtolower($sToken) ;
		return in_array($sToken,self::$arrDialects[$this->sDialect]['types']) ;
	}
	
	public function conjunctions()
	{
		return self::$arrDialects[$this->sDialect]['conjunctions'] ;
	}
	public function isConjunction($sToken)
	{
		$sToken = strtolower($sToken) ;
		return in_array($sToken,self::$arrDialects[$this->sDialect]['conjunctions']) ;
	}
	
	public function functions()
	{
		return self::$arrDialects[$this->sDialect]['functions'] ;
	}
	public function isFunction($sToken)
	{
		$sToken = strtolower($sToken) ;
		return in_array($sToken,self::$arrDialects[$this->sDialect]['functions']) ;
	}
	
	public function reserved()
	{
		return self::$arrDialects[$this->sDialect]['reserved'] ;
	}
	public function isReserved($sToken)
	{
		$sToken = strtolower($sToken) ;
		return in_array($sToken,self::$arrDialects[$this->sDialect]['reserved']) ;
	}
	
	public function synonyms()
	{
		return self::$arrDialects[$this->sDialect]['synonyms'] ;
	}
	public function isSynonym($sToken)
	{
		$sToken = strtolower($sToken) ;
		return in_array($sToken,self::$arrDialects[$this->sDialect]['synonyms']) ;
	}
	
	public function comments()
	{
		return self::$arrDialects[$this->sDialect]['comments'] ;
	}
	public function isCommentBegin($sToken)
	{
		return isset(self::$arrDialects[$this->sDialect]['comments'][$sToken]) ;
	}
	public function isCommentEnd($sToken,$bBeginToken)
	{
		return self::$arrDialects[$this->sDialect]['comments'][$bBeginToken] === $sToken ;
	}
	
	public function isJoinType($sToken)
	{
		$sToken = strtolower($sToken) ;
		return in_array($sToken,self::$arrDialects[$this->sDialect]['jointype']) ;
	}
	
	public function quotes()
	{
		return self::$arrDialects[$this->sDialect]['quotes'] ;
	}
	public function isQuote($sToken)
	{
		return in_array($sToken,self::$arrDialects[$this->sDialect]['quotes']) ;
	}
	
	private $sDialect ;
	
	static private $arrDialects = array(
		

		'ANSI' => array(
		    'commands' => array(
		        'alter',
		        'create',
		        'drop',
		        'select',
		        'delete',
		        'insert',
		        'update',
		    ),
		
		    'operators' => array(
		        '+',
		        '-',
		        '*',
		        '/',
		        '^',
		        '=',
		        '!=',
		        '<>',
		        '<',
		        '<=',
		        '>',
		        '>=',
		        'like',
		        'clike',
		        'slike',
		        'not',
		        'is',
		        'in',
		        'between',
		        'and',
		        'or',
		    		
	    		'(' ,
	    		')' ,
	    		',' ,
	    		'.' ,
		    ),
		
		    'types' => array(
		        'character',
		        'char',
		        'varchar',
		        'nchar',
		        'bit',
		        'numeric',
		        'decimal',
		        'dec',
		        'integer',
		        'int',
		        'smallint',
		        'float',
		        'real',
		        'double',
		        'date',
		        'time',
		        'timestamp',
		        'interval',
		        'bool',
		        'boolean',
		        'set',
		        'enum',
		        'text',
		    ),
		
		    'conjunctions' => array(
		        'by',
		        'as',
		        'on',
		        'into',
		        'from',
		        'where',
		        'with',
		    ),
		
		    'functions' => array(
		        'avg',
		        'count',
		        'max',
		        'min',
		        'sum',
		        'nextval',
		        'currval',
		    ),
		
		    'reserved' => array(
		        'absolute',
		        'action',
		        'add',
		        'all',
		        'allocate',
		        'and',
		        'any',
		        'are',
		        'asc',
		        'ascending',
		        'assertion',
		        'at',
		        'authorization',
		        'auto_increment',
		        'begin',
		        'bit_length',
		        'both',
		        'cascade',
		        'cascaded',
		        'case',
		        'cast',
		        'catalog',
		        'char_length',
		        'character_length',
		        'check',
		        'close',
		        'coalesce',
		        'collate',
		        'collation',
		        'column',
		        'commit',
		        'connect',
		        'connection',
		        'constraint',
		        'constraints',
		        'continue',
		        'convert',
		        'corresponding',
		        'cross',
		        'current',
		        'current_date',
		        'current_time',
		        'current_timestamp',
		        'current_user',
		        'cursor',
		        'day',
		        'deallocate',
		        'declare',
		        'default',
		        'deferrable',
		        'deferred',
		        'desc',
		        'descending',
		        'describe',
		        'descriptor',
		        'diagnostics',
		        'disconnect',
		        'distinct',
		        'domain',
		        'else',
		        'end',
		        'end-exec',
		        'escape',
		        'except',
		        'exception',
		        'exec',
		        'execute',
		        'exists',
		        'external',
		        'extract',
		        'false',
		        'fetch',
		        'first',
		        'for',
		        'foreign',
		        'found',
		        'full',
		        'get',
		        'global',
		        'go',
		        'goto',
		        'grant',
		        'group',
		        'having',
		        'hour',
		        'identity',
		        'immediate',
		        'indicator',
		        'initially',
		        'inner',
		        'input',
		        'insensitive',
		        'intersect',
		        'isolation',
		        'join',
		        'key',
		        'language',
		        'last',
		        'leading',
		        'left',
		        'level',
		        'limit',
		        'local',
		        'lower',
		        'match',
		        'minute',
		        'module',
		        'month',
		        'names',
		        'national',
		        'natural',
		        'next',
		        'no',
		        'null',
		        'nullif',
		        'octet_length',
		        'of',
		        'only',
		        'open',
		        'option',
		        'or',
		        'order',
		        'outer',
		        'output',
		        'overlaps',
		        'pad',
		        'partial',
		        'position',
		        'precision',
		        'prepare',
		        'preserve',
		        'primary',
		        'prior',
		        'privileges',
		        'procedure',
		        'public',
		        'read',
		        'references',
		        'relative',
		        'restrict',
		        'revoke',
		        'right',
		        'rollback',
		        'rows',
		        'schema',
		        'scroll',
		        'second',
		        'section',
		        'session',
		        'session_user',
		        'size',
		        'some',
		        'space',
		        'sql',
		        'sqlcode',
		        'sqlerror',
		        'sqlstate',
		        'substring',
		        'system_user',
		        'table',
		        'temporary',
		        'then',
		        'timezone_hour',
		        'timezone_minute',
		        'to',
		        'trailing',
		        'transaction',
		        'translate',
		        'translation',
		        'trim',
		        'true',
		        'union',
		        'unique',
		        'unknown',
		        'upper',
		        'usage',
		        'user',
		        'using',
		        'value',
		        'values',
		        'varying',
		        'view',
		        'when',
		        'whenever',
		        'work',
		        'write',
		        'year',
		        'zone',
		        'eoc',
		    ),
		
		    'synonyms' => array(
		        'decimal' => 'numeric',
		        'dec' => 'numeric',
		        'numeric' => 'numeric',
		        'float' => 'float',
		        'real' => 'real',
		        'double' => 'real',
		        'int' => 'int',
		        'integer' => 'int',
		        'interval' => 'interval',
		        'smallint' => 'smallint',
		        'timestamp' => 'timestamp',
		        'bool' => 'bool',
		        'boolean' => 'bool',
		        'set' => 'set',
		        'enum' => 'enum',
		        'text' => 'text',
		        'char' => 'char',
		        'character' => 'char',
		        'varchar' => 'varchar',
		        'ascending' => 'asc',
		        'asc' => 'asc',
		        'descending' => 'desc',
		        'desc' => 'desc',
		        'date' => 'date',
		        'time' => 'time',
		    ),
		
		    'lexeropts' => array(
		        'allowIdentFirstDigit' => false,
		    ),
		
		    'parseropts' => array(
		    ),
		
		    'comments' => array(
		        '--' => "\n",
		    ),
		
		    'quotes' => array(
		        "'" => 'string',
		        '"' => 'ident',
		    ),
				
			'jointype' => array(
				'left', 'right', 'inner', 'full'
			)
		) ,
			
			
			
			
			
		
		self::MySQL => array(
		    'commands' => array(
		        'alter',
		        'create',
		        'drop',
		        'select',
		        'delete',
		        'insert',
		        'update',
				'rename',
		        'do',
		        'handler',
		        'load',
		        'replace',
		        'truncate',
		        'describe',
		        'explain',
		        'help',
		        'use',
		        'start',
		        'commit',
		        'rollback',
		        'lock',
		        'unlock',
		        'set',
		        'show',
		        'purge',
		        'reset',
		        'change',
		        'start',
		        'stop',
		        'savepoint',
		        'release',
		    ),
		
		    'operators' => array(
		        '+',
		        '-',
		        '*',
		        '/',
		        '^',
		    	'=',
		        '<>',
		        '!=',
		        '<',
		        '<=',
		        '>',
		        '>=',
		        'like',
		        'clike',
		        'slike',
		        'not',
		        'is',
		        'in',
		        'between',
		        'and',
		        'or',
		    		
		    	'(' ,
		    	')' ,
		    	',' ,
	    		'.' ,
		    ),
		
		    'types' => array(
		        'character',
		        'char',
		        'varchar',
		        'nchar',
		        'bit',
		        'numeric',
		        'decimal',
		        'dec',
		        'integer',
		        'int',
		        'tinyint',
		        'smallint',
		        'float',
		        'real',
		        'double',
		        'date',
		        'datetime',
		        'time',
		        'timestamp',
		        'interval',
		        'bool',
		        'boolean',
		        'set',
		        'enum',
		        'text',
		    ),
		
		    'conjunctions' => array(
		        'by',
		        'as',
		        'on',
		        'into',
		        'from',
		        'where',
		        'with',
		    ),
		
		    'functions' => array(
		        'abs',
		        'acos',
		        'adddate',
		        'addtime',
		        'aes_encrypt',
		        'aes_decrypt',
		        'against',
		        'ascii',
		        'asin',
		        'atan',
		        'avg',
		        'benchmark',
		        'bin',
		        'bit_and',
		        'bit_or',
		        'bitcount',
		        'bitlength',
		        'cast',
		        'ceiling',
		        'char',
		        'char_length',
		        'character_length',
		        'charset',
		        'coalesce',
		        'coercibility',
		        'collation',
		        'compress',
		        'concat',
		        'concat_ws',
		        'conection_id',
		        'conv',
		        'convert',
		        'convert_tz',
		        'cos',
		        'cot',
		        'count',
		        'crc32',
		        'curdate',
		        'current_user',
		        'currval',
		        'curtime',
		        'database',
		        'date_add',
		        'date_diff',
		        'date_format',
		        'date_sub',
		        'day',
		        'dayname',
		        'dayofmonth',
		        'dayofweek',
		        'dayofyear',
		        'decode',
		        'default',
		        'degrees',
		        'des_decrypt',
		        'des_encrypt',
		        'elt',
		        'encode',
		        'encrypt',
		        'exp',
		        'export_set',
		        'extract',
		        'field',
		        'find_in_set',
		        'floor',
		        'format',
		        'found_rows',
		        'from_days',
		        'from_unixtime',
		        'get_format',
		        'get_lock',
		        'group_concat',
		        'greatest',
		        'hex',
		        'hour',
		        'if',
		        'ifnull',
		        'in',
		        'inet_aton',
		        'inet_ntoa',
		        'insert',
		        'instr',
		        'interval',
		        'is_free_lock',
		        'is_used_lock',
		        'last_day',
		        'last_insert_id',
		        'lcase',
		        'least',
		        'left',
		        'length',
		        'ln',
		        'load_file',
		        'localtime',
		        'localtimestamp',
		        'locate',
		        'log',
		        'log2',
		        'log10',
		        'lower',
		        'lpad',
		        'ltrim',
		        'make_set',
		        'makedate',
		        'maketime',
		        'master_pos_wait',
		        'match',
		        'max',
		        'md5',
		        'microsecond',
		        'mid',
		        'min',
		        'minute',
		        'mod',
		        'month',
		        'monthname',
		        'nextval',
		        'now',
		        'nullif',
		        'oct',
		        'octet_length',
		        'old_password',
		        'ord',
		        'password',
		        'period_add',
		        'period_diff',
		        'pi',
		        'position',
		        'pow',
		        'power',
		        'quarter',
		        'quote',
		        'radians',
		        'rand',
		        'release_lock',
		        'repeat',
		        'replace',
		        'reverse',
		        'right',
		        'round',
		        'row_count',
		        'rpad',
		        'rtrim',
		        'sec_to_time',
		        'second',
		        'session_user',
		        'sha',
		        'sha1',
		        'sign',
		        'soundex',
		        'space',
		        'sqrt',
		        'std',
		        'stddev',
		        'stddev_pop',
		        'stddev_samp',
		        'strcmp',
		        'str_to_date',
		        'subdate',
		        'substring',
		        'substring_index',
		        'subtime',
		        'sum',
		        'sysdate',
		        'system_user',
		        'tan',
		        'time',
		        'timediff',
		        'timestamp',
		        'timestampadd',
		        'timestampdiff',
		        'time_format',
		        'time_to_sec',
		        'to_days',
		        'trim',
		        'truncate',
		        'ucase',
		        'uncompress',
		        'uncompressed_length',
		        'unhex',
		        'unix_timestamp',
		        'upper',
		        'user',
		        'utc_date',
		        'utc_time',
		        'utc_timestamp',
		        'uuid',
		        'var_pop',
		        'var_samp',
		        'variance',
		        'version',
		        'week',
		        'weekday',
		        'weekofyear',
		        'year',
		        'yearweek',
		    ),
		
		    'reserved' => array(
		        'add',
		        'all',
		        'alter',
		        'analyze',
		        'and',
		        'as',
		        'asc',
		        'asensitive',
		        'auto_increment',
		        'bdb',
		        'before',
		        'berkeleydb',
		        'between',
		        'bigint',
		        'binary',
		        'blob',
		        'both',
		        'by',
		        'call',
		        'cascade',
		        'case',
		        'change',
		        'char',
		        'character',
		        'check',
		        'collate',
		        'column',
		        'columns',
		        'condition',
		        'connection',
		        'constraint',
		        'continue',
		        'create',
		        'cross',
		        'current_date',
		        'current_time',
		        'current_timestamp',
		        'cursor',
		        'database',
		        'databases',
		        'day_hour',
		        'day_microsecond',
		        'day_minute',
		        'day_second',
		        'dec',
		        'decimal',
		        'declare',
		        'default',
		        'delayed',
		        'delete',
		        'desc',
		        'describe',
		        'deterministic',
		        'distinct',
		        'distinctrow',
		        'div',
		        'double',
		        'drop',
		        'else',
		        'elseif',
		        'enclosed',
		        'escaped',
		        'exists',
		        'exit',
		        'explain',
		        'false',
		        'fetch',
		        'fields',
		        'float',
		        'for',
		        'force',
		        'foreign',
		        'found',
		        'frac_second',
		        'from',
		        'fulltext',
		        'grant',
		        'group',
		        'having',
		        'high_priority',
		        'hour_microsecond',
		        'hour_minute',
		        'hour_second',
		        'if',
		        'ignore',
		        'in',
		        'index',
		        'infile',
		        'inner',
		        'innodb',
		        'inout',
		        'insensitive',
		        'insert',
		        'int',
		        'integer',
		        'interval',
		        'into',
		        'io_thread',
		        'is',
		        'iterate',
		        'join',
		        'key',
		        'keys',
		        'kill',
		        'leading',
		        'leave',
		        'left',
		        'like',
		        'limit',
		        'lines',
		        'load',
		        'localtime',
		        'localtimestamp',
		        'lock',
		        'long',
		        'longblob',
		        'longtext',
		        'loop',
		        'low_priority',
		        'master_server_id',
		        'match',
		        'mediumblob',
		        'mediumint',
		        'mediumtext',
		        'middleint',
		        'minute_microsecond',
		        'minute_second',
		        'mod',
		        'natural',
		        'not',
		        'no_write_to_binlog',
		        'null',
		        'numeric',
		        'on',
		        'optimize',
		        'option',
		        'optionally',
		        'or',
		        'order',
		        'out',
		        'outer',
		        'outfile',
		        'precision',
		        'primary',
		        'privileges',
		        'procedure',
		        'purge',
		        'read',
		        'real',
		        'references',
		        'regexp',
		        'rename',
		        'repeat',
		        'replace',
		        'require',
		        'restrict',
		        'return',
		        'revoke',
		        'right',
		        'rlike',
		        'second_microsecond',
		        'select',
		        'sensitive',
		        'separator',
		        'set',
		        'show',
		        'smallint',
		        'some',
		        'soname',
		        'spatial',
		        'specific',
		        'sql',
		        'sqlexception',
		        'sqlstate',
		        'sqlwarning',
		        'big_result',
		        'calc_found_rows',
		        'small_result',
		        'tsi_day',
		        'tsi_frac_second',
		        'tsi_hour',
		        'tsi_minute',
		        'tsi_month',
		        'tsi_quarter',
		        'tsi_second',
		        'tsi_week',
		        'tsi_year',
		        'ssl',
		        'starting',
		        'straight_join',
		        'striped',
		        'table',
		        'tables',
		        'terminated',
		        'then',
		        'timestampadd',
		        'timestampdiff',
		        'tinyblob',
		        'tinyint',
		        'tinytext',
		        'to',
		        'trailing',
		        'true',
		        'undo',
		        'union',
		        'unique',
		        'unlock',
		        'unsigned',
		        'update',
		        'usage',
		        'use',
		        'user_resources',
		        'using',
		        'utc_date',
		        'utc_time',
		        'utc_timestamp',
		        'values',
		        'varbinary',
		        'varchar',
		        'varcharacter',
		        'varying',
		        'when',
		        'where',
		        'while',
		        'with',
		        'write',
		        'xor',
		        'year_month',
		        'zerofill',
		    ),
		
		    'synonyms' => array(
		        'decimal' => 'numeric',
		        'dec' => 'numeric',
		        'numeric' => 'numeric',
		        'float' => 'float',
		        'real' => 'real',
		        'double' => 'real',
		        'int' => 'int',
		        'integer' => 'int',
		        'interval' => 'interval',
		        'smallint' => 'smallint',
		        'tinyint' => 'tinyint',
		        'timestamp' => 'timestamp',
		        'bool' => 'bool',
		        'boolean' => 'bool',
		        'set' => 'set',
		        'enum' => 'enum',
		        'text' => 'text',
		        'char' => 'char',
		        'character' => 'char',
		        'varchar' => 'varchar',
		        'ascending' => 'asc',
		        'asc' => 'asc',
		        'descending' => 'desc',
		        'desc' => 'desc',
		        'date' => 'date',
		        'time' => 'time',
		    ),
		
		    'lexeropts' => array(
		        'allowIdentFirstDigit' => true,
		    ),
		
		    'parseropts' => array(
		    ),
		
		    'comments' => array(
		        '-- '   => "\n",
		        '/*'    => '*/',
		        '#'     => "\n",
		    ),
		
		    'quotes' => array(
		        "'" => 'string',
		        '"' => 'string',
		        '`' => 'ident',
		    ),
				
			'jointype' => array(
				'left', 'right', 'inner'
			)
		),
	) ;
}

?>