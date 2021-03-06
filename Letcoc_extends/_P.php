<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



/**
 * _P Class.
 * 
 * PHP Version 5.3.9
 * 
 * @name   		_P Class
 * @package		Letcoc
 * @author 		Aleksei Zhulitov
 * @title  		Набор плюшек для PHP кодера кода.
 * 
 * @info   		Для получения справки по классу, выполните: <code>_P::DOC();</code>
 * 
 * @example		[Образец класса для запуска примеров]
 * <pre>
 *	class some{
 *		public $param	 = array();
 *		public $param_ll = "someValue";
 
 *		/**
 *		 * Some Method test
 *		 * ...
 *		 * /
 *		function test(){}
 *
 *		function test_ll(){}
 *	}
 *	$class = new some;
 * </pre>
 */
class _P {
	
	/**
	 * Метод выводящий результат Функции `print_r` обрамленный в `pre`.
	 * 
	 * @example	<code>_P::P( $you_variable );</code>
	 * 
	 * @access	public
	 * @param	any		$var		[Переменная, информация о которой будет выведена]
	 * @param	string|bool	$title	[string - Заголовок выходящий в начале контейнера с инфой;
	 *       	           	      	bool - `FALSE` не выводить заголовок ]
	 * 
	 * @param	bool		$var	[TRUE  - тэг `pre` будет позиционирован абсолютно]
	 * @param	bool		$return	[TRUE  - вернет без `pre`; FALSE - выведет в браузер ]
	 * @return	void
	 * @return	string				[в том случае если параметр $return задан TRUE]
	 */
	public static function P( $var = "", $title = "Информация", $onTop = FALSE, $return = FALSE )
	{
		$onTop	= ( $onTop ) ? "position:absolute;z-index:999999;" : "";
		if( !$return )
			echo "<pre style='{$onTop}border:1px dotted red;padding:10px; background:#FFC; color:#000;'>";
			
		$buffer	 = "";
		if ( !is_bool( $title ) and $title !== FALSE )
			$buffer	.= print_r( "<h4 style='color:blue;'> :: {$title} :: </h4>", $return );
		$buffer	.= print_r( $var, $return );
		
		if( !$return )
			echo "</pre>";
		
		if( $return ) return $buffer;
	}
	
	
	/**
	 * Метод аналогичный _P::P, только по завершению выполняет выход (exit();)
	 **/
	public static function E( $var = "", $title = "Информация", $onTop = FALSE, $return = FALSE )
	{
		self::P( $var, $title, $onTop, $return );
		exit();
	}
	
	
	/**
	 * Метод выводящий результат Функции `print_r` в указанный файл.
	 * 
	 * @example	<code>_P::toFile( $you_variable, "path/file.name" );</code>
	 * 
	 * @access	public
	 * 
	 * @param	any			$var		[Переменная, информация о которой будет выведена]
	 * @param	bool		$file		[Имя файла в который будет помещен результат]
	 * @param	bool		$flag		[флаг для создания файла (см.fopen); a+ - по умолчаниюж]
	 * @return	bool					[TRUE - запись успешна; FALSE - ошибка;]
	 */
	public static function toFile( $var = "", $file = NULL, $flag = "a+" )
	{
		if ( !is_string( $file ) or empty( $file ) )	return FALSE;
		$handle	= fopen( $file, $flag );
		if ( !$handle )				return FALSE;
		fwrite( $handle, print_r( $var, TRUE ) );
		fclose( $handle );
		return TRUE;
	}
	
	
	/**
	 * Метод выводящий список методов переданного аргументом класса.
	 * 
	 * @example	<code>_P::CM( $class );</code>
	 * 
	 * @access	public
	 * @param	object	$class	[класс, методы которого нужно вывести.]
	 * @return	void
	 */
	public static function CM ( $class = "" )
	{
		if( !is_object( $class ) ) return;
		$_ReflectionClass			= new ReflectionObject( $class );
		$BUFF						= new stdClass;
		$BUFF->static				= array();
		$BUFF->public				= array();
		$BUFF->protected			= array();
		$BUFF->private				= array();
		foreach( $_ReflectionClass->getMethods() as $Method )
		{
			$_ReflectionMethod		= new ReflectionMethod( $class, $Method->name );
			$_ReflectionMethod->setAccessible(true);
			
			$modifer	= $_ReflectionMethod->isPublic()	? "public"		: "??";
			$modifer	= $_ReflectionMethod->isPrivate()	? "private"		: $modifer;
			$modifer	= $_ReflectionMethod->isProtected()	? "protected"	: $modifer;
			$modifer	= $_ReflectionMethod->isStatic()	? "static"		: $modifer;
			
			if ( $modifer == "private" AND get_class($class) != $Method->class )
				continue;

			array_push($BUFF->$modifer, $Method->name );
		}
		sort($BUFF->static);
		sort($BUFF->public);
		sort($BUFF->protected);
		sort($BUFF->private);
		if( count($BUFF->static) < 1 )		unset($BUFF->static);
		if( count($BUFF->public) < 1 )		unset($BUFF->public);
		if( count($BUFF->protected) < 1 )	unset($BUFF->protected);
		if( count($BUFF->private) < 1 )		unset($BUFF->private);
		self::P(
			$BUFF,
			"Методы класса - " . get_class( $class )
		);
	}
	
	
	
	/**
	 * Метод выводящий список свойств
	 * переданного аргументом класса или объекта.
	 * 
	 * @example	<code>_P::CV( $class );</code>
	 * 
	 * @access	public
	 * @param	object	$class	[класс, свойства которого нужно вывести.]
	 * @return	void
	 */
	public static function CV ( $class = "" )
	{
		if( !is_object( $class ) ) return;
		self::P(
			get_object_vars( $class ), 
			"Свойства класса - " . get_class( $class )
		);
	}
	
	
	
	/**
	 * Метод выводящий список свойств
	 * переданного аргументом класса или объекта.
	 * 
	 * Выводит информацию только о тех свойствах,
	 * значения которых не являются другими классами.
	 * 
	 * Дополнительно выводит модификатор доступа.
	 *
	 * @example	<code>_P::COV( $class );</code>
	 *
	 * @access	public
	 * @param	object	$class	[класс, свойства которого нужно вывести.]
	 * @return	void
	 */
	public static function COV( $class = "" )
	{
		if( !is_object( $class ) ) return;
			
		$BUFF						=	new stdClass;
		
		$_ReflectionClass			= new ReflectionObject( $class );
		foreach( $_ReflectionClass->getProperties() as $Properties )
		{
			$name					= $Properties->name;
			$_ReflectionProperty	= new ReflectionProperty( $class, $name );
			$_ReflectionProperty->setAccessible(true);
			
			$modifer	= $_ReflectionProperty->isPublic()		? "public"		: "??";
			$modifer	= $_ReflectionProperty->isPrivate()		? "private"		: $modifer;
			$modifer	= $_ReflectionProperty->isProtected()	? "protected"	: $modifer;
			$modifer	= $_ReflectionProperty->isStatic()		? "static"		: $modifer;
			
			$value	= $_ReflectionProperty->getValue( $class );
			if( is_object( $value ) and get_class( $value ) != "stdClass" )
				continue;
			$BUFF->{$name." ($modifer)"}			= $value;
		}
		
		self::P(
			$BUFF,
			"Свойства класса - " . get_class( $class )
		);
	}
	
	
	
	/**
	 * Метод выводит информацию о всех определенных ранее константах.
	 * 
	 * @example	<code>_P::DC();</code>
	 * 
	 * @access	public
	 * @return	void
	 */
	public static function DC ()
	{
		self::P(
			get_defined_constants(),
			"Информация о константах"
		);
	}
	
	
	
	/**
	 * Метод выводит информацию о файлах которые ранее были подключены
	 * через: include; include_once; require; require_once;
	 * 
	 * @example	<code>_P::_IF();</code>
	 * 
	 * @access	public
	 * @return	void
	 */
	public static function _IF ()
	{
		self::P(
			get_included_files(),
			"Информация о подключеных файлах"
		);
	}
	
	
	
	/**
	 * Метод возвращает модификатор (области видимости) свойства класса.
	 * 
	 * Или проверяет, имеет ли свойство `$property` экземпляра класса `$class`
	 * модификатор `$modifer`.
	 * 
	 * @example		[Получение имени модификатора свойства `propetry_name` экземпляра класса `$class`]
	 * 	<code>_P::P( $class, "propetry_name" );</code>
	 * 
	 * @example		[Проверка, имеет ли свойство `propetry_name` экземпляра класса `$class` модификатор `private`]
	 * 	<code>_P::P( $class, "propetry_name", "private" );</code>
	 * 
	 * @access	public
	 * @param	object		$class		[Экземпляр класса]
	 * @param	string		$property	[Имя свойства экземпляра класса]
	 * @param	string|NULL	$modifer	[Название модификатора для проверки]
	 * @return	string|bool				[string - имя модификатора;
	 * 									 TRUE	- свойство имеет модификатор указанный в `$modifer`;
	 * 									 FALSE	- если такого свойства нет, и другие ошибки.]
	 * 
	 * @todo	Сделать флаг который проверит доступно ли свойство для записи.
	 * 			(Для записи доступны не существующие и public свойства)
	 */
	public static function CP_access (	$class = NULL, $property = NULL, $modifer = NULL )
	{
		if ( !is_object( $class ) OR !is_string( $property ) )
			return FALSE;
			
		if ( !is_null( $modifer ) AND !is_string( $modifer ) )
			return FALSE;
		
		/** Строковые значения констант модификаторов доступа. */
		$_IS	= array(
			"1"		=> "static",
			"256"	=> "public",
			"257"	=> "static",
			"512"	=> "protected",
			"1024"	=> "private"
		);
		
		/**
		 * Блок проверки экземпляра `stdClass`.
		 **/
			if ( get_class( $class ) == "stdClass" )
			{
				if ( isset( $class->{$property} ) and !is_string( $modifer ) )
				{
					return $_IS["256"];
				}
				else if ( isset( $class->{$property} ) and is_string( $modifer ) )
				{
					return ( strtolower( $_IS["256"] ) == strtolower( $modifer ) );
				}
				else if ( !isset( $class->{$property} ) )
				{
					return $_IS["256"];
				}
				else
					return FALSE;
			}
		
		/**
		 * Блок проверки классов.
		 **/
			/** Получение информации о классе (объекте). */
			$_ReflectionClass		= new ReflectionObject( $class );
			
			/** Проверка наличия в классе запрошенного свойства. (нет - 256)*/
			if ( !$_ReflectionClass->hasProperty( $property ) )
			{
				if ( is_string( $modifer ) )
					return ( strtolower( $_IS["256"] ) == strtolower( $modifer ) );

				return $_IS["256"];
			}
		
			/** Получение информации свойстве класса. */
			$_ReflectionProperty	= new ReflectionProperty( $class, $property );
			$_Mod_int				= $_ReflectionProperty->getModifiers();
			
			
			/** Проверка наличия числового модификатора в массиве строковых значений. */
			if ( isset( $_IS[ $_Mod_int ] ) )
				$_mod_name			= $_IS[ $_Mod_int ];	//Строковое значение для этого модификатора.
			else
				$_mod_name			= $_IS[ "256" ];		//Дефолтное значение.	

			/** Если сказано проверить на соответствие.  */
			if ( is_string( $modifer ) )
				return ( strtolower( $_mod_name ) == strtolower( $modifer ) );
				
			/** Иначе вернем имя модификатора.  */
			else
				return strtolower( $_mod_name );
	}
	
	
	
	/**
	 * Метод выводит информацию о количестве памяти выделенном для PHP.
	 * (Количество памяти выделенное до вызова метода.)
	 * 
	 * @example	<code>_P::MEM();</code>
	 * 
	 * @access	public
	 * @return	void
	 */
	public static function MEM()
	{
		$_Mem		= memory_get_usage(true);
			$Mem 	= "{$_Mem} Б";
		if ( $_Mem >= 1024 )
			$Mem	.= " || " . round( $_Mem/1024, 3 ) . " КБ";
			
		if ( $_Mem >= 1048576 )
			$Mem	.= " || " . round( $_Mem/1048576, 3 ) . " MB";
		self::P( $Mem, "Выделено памяти" );
	}
	
	
	
	/**
	 * Метод выводит документацию по указанному классу.
	 * 
	 * @example
	 *	<pre>
	 *		class some{
	 *			/**
	 *			 * Some Method
	 *			 * ...
	 *			 * /
	 *			function test(){}
	 *		}	
	 *		$class	= new some;
	 *		_P::DOC( $class );
	 *	</pre>
	 * 
	 * @access	public
	 * @param	object	$class	[object - класс по которому нужно вывести инфу; NULL - выводит информацию о классе `плюшек`]
	 * @return	void
	 */
	public static function DOC( $class = NULL )
	{
		if ( is_null( $class ) or !is_object( $class ) or get_class( $class ) == "stdClass" )
		{
			$class	= new _P;
		}
		
		if ( !function_exists(	"_Parse_DOC" ) )
		{
			function _Parse_DOC ( $DOC ) {
				if ( strlen( $DOC ) < 10 )
					return "/**\r * Документация отсутствует ):\r */";
			
			
				$DOC	= mb_ereg_replace( "[\r\n]+\t+", "\r", $DOC );
				$DOC	= mb_ereg_replace( "[\r\n]+[\t *]+<", "\r<", $DOC );
				
				$DOC	= mb_ereg_replace( "//([\w]+)([\r\n]+)", "<span style='color:#11AAAA;'>//\\1</span>\\2", $DOC );
				
				$DOC	= mb_ereg_replace(
							"([\r\n]+[\t *]+)(@{1}[\w]+)",
							"\\1<span style='color:#090; font-weight:bold;'>\\2</span>",
							$DOC
						);
				
				$_DOC	= "";
				foreach( mb_split( "[\n\r]{1,2}", $DOC ) as $str )
				{
					$str = mb_ereg_replace( "^([\t ]*)/?\*+/?", "\\1", $str );
					$str = mb_ereg_replace( "^ +", "", $str );
					$str = mb_ereg_replace( "\[([\w\W]+)\]", "[<i>\\1</i>]", $str );
					$str = mb_ereg_replace( "\[([\w\W]+)", "[<i>\\1", $str );
					$_DOC .= mb_ereg_replace( "(\S)([\w\W]+)\]", "\\1\\2</i>]", $str ) . PHP_EOL;
				}
				$DOC	= $_DOC;
		
				$DOC	= str_replace( "<pre>", "<pre style='display:block;color:red;margin: 0 0 -15 0'>", $DOC );
				$DOC	= str_replace( "<code>", "<code style='color:red;'>", $DOC );
				$DOC	.= "<hr size=4 noshade>\r";
				return $DOC;
			}
		}
		
		$RETURN	= "";
		
		$_Reflection		= new ReflectionClass( $class );
		
		$DocComment			= _Parse_DOC ( $_Reflection->getDocComment() );

		
		$RETURN	.= _P::P( $DocComment, "Информация о class " . get_class( $class ) . "{}", FALSE, TRUE );
		
		foreach ( $_Reflection->getMethods() as $method )
		{
			$DocComment	=	_Parse_DOC( $_Reflection->getMethod( $method->name )->getDocComment() );
			$RETURN	.= _P::P(
							$DocComment,
							"Информация о методе `{$method->name}` класса `{$method->class}`",
							FALSE, TRUE
						) ;
		}
		
		_P::P( $RETURN, FALSE );
	}
}

/* End of file _P.php */
/* Location: ./system/libraries/Letcoc_extends/_P.php */