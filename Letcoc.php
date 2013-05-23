<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * (Letcoc) Library extends the capabilities of CodeIgniter.
 * 
 * PHP Version 5.3.9
 * 
 * @name		Letcoc Library Class.
 * @package		CodeIgniter.
 * @title		Библиотека расширяющая возможности CodeIgniter.
 * @author		Aleksei Zhulitov (LaoSun).
 * @version		0.1 (alpha)
 * @link		http://github.com/laosun/Letcoc
 */
class Letcoc {
	
	/**#@+
	 * Class Variables
	 * @access private|protected|public
	 */
	/** CI Контроллер (супер объект по ссылке) */
	public $CI				=	NULL;
	
	/** Метод которым приходят данные от пользователя (_POST||_GET). */
	private $_REQ_Method	= "_POST";
	
	/** Массив в котором хранятся данные поступившие методом заданным выше. */
	protected $__REQUEST	= array();
	/**#@-*/

	/**
	 * КОНСТРУКТОР.
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{	
		/** нет ошибкам */
		//error_reporting( 0 );
		
		$this->CI	= & get_instance();
		$this->initialize();
	}
	
	
	/** 
	 * Метод присваивающий по ссылке публичные параметры активного котроллера.
	 * 
	 * @access	private
	 * @return	void
	 */
	private function initialize()
	{
		foreach( $this->CI as $name => $value )
		{
			if( is_object( $value ) and get_class( $value ) != "stdClass" )
				continue;
			else
			{
				$this->$name		= & $this->CI->$name;
			}
		}
		
		/**
		 * Блок назначения параметров для приема
		 * входящих данных и прием данных.
		 */
		if ( isset( $this->CI->_REQ_Method ) )
			$this->_REQ_Method		= & $this->CI->_REQ_Method;
		else
			$this->CI->_REQ_Method	= & $this->_REQ_Method;
		
		$this->__REQUEST			= & $GLOBALS[ $this->_REQ_Method ];
		$this->CI->__REQUEST		= & $this->__REQUEST;
	}
	
	
	
	/**
	 * Метод для установки метода поступления данных
	 * перезагрузки (переполучения) данных.
	 * 
	 * @access	public
	 * @param	string	$method	[метод для получения данных _POST or _GET;
	 * 							 остальные значения перезагружают.]
	 * @return	object			[ссылка на этот класс]
	 */
	public function set_REQ( $method = "_POST" )
	{
		if ( is_string( $method ) and mb_ereg( "(_POST|_GET)", $method ) )
			$this->_REQ_Method		= $method;
		
		$this->__REQUEST			= $GLOBALS[ $this->_REQ_Method ];
		return $this;
	}
	
	/** 
	 * Метод для инициализации и получения класса Letcoc контроллера.
	 * 
	 * @access	public
	 * @return	object	[ссылка на класс L_Controller]
	 */
	public function Controller() {
		if ( !isset( $this->Controller ) )
			$this->Controller = new L_Controller;
		return $this->Controller;
	}
	
	
	/** 
	 * Метод для инициализации и получения класса Letcoc DB.
	 * 
	 * @access	public
	 * @return	object	[ссылка на класс L_DataBase]
	 */
	public function DB() {
		if ( !isset( $this->DB ) )
			$this->DB = new L_DataBase;
		return $this->DB;
	}
	
	
	/** 
	 * Метод для загрузки произвольного контроллера CodeIgniter.
	 * 
	 * @access	public
	 * @param	string	$class	[имя загружаемого контроллера]
	 * @return	object	[загруженный или stdClass]
	 */
	public function Load_other_controller ( $class_ = "" )
	{
		$FALSE	= new L_Spider_silk();
		if ( !is_object( $this->CI ) )
			return $FALSE;
			
		if ( strtolower( get_class( $this->CI ) ) == strtolower( $class_ ) )
			return $FALSE;
		
		if ( !file_exists( APPPATH."controllers/".$class_.".php" ) )
			return $FALSE;
	
		include_once( APPPATH . "controllers/" . $class_ . ".php" );
		
		if( class_exists( $class_ ) )
		{
			$_class = new $class_;
		}
		else
		{	
			$_class	= new stdClass;
		}
		return new L_Spider_silk( $_class );
	}
}



/**
 * L_Controller extends Letcoc Class.
 * 
 * PHP Version 5.3.9
 * 
 * @name		Letcoc Controller Class.
 * @package		CodeIgniter.
 * @author		Aleksei Zhulitov.
 * @title		Класс расширения Letcoc, для Контроллеров CodeIgniter.
 */
class L_Controller extends Letcoc{
	
	public function __construct(){parent::__construct();}

	/**
	 * МАРШРУТИЗАТОР.
	 *
	 * @access	public
	 * @param	string	$method
	 * @return	object	[ссылка на этот класс]
	 */
	public function _remap( $method = "index" )
	{
		$segments	= $this->CI->uri->rsegments;
		/** check segments */
		
		IF (
			is_array( $segments )
			and mb_ereg( "^\w+(\.php)?$", @$segments[2] )
			and !mb_ereg( "^_+",  @$segments[2] )
		){
			$segments[2] = mb_ereg_replace( ".php$", "", $segments[2] );
			if ( method_exists( $this->CI, $segments[2] ) )
			{
				call_user_func_array( array( &$this->CI, $segments[2] ), $segments );
				return $this;
			}
		} /** end IF */
		$this->CI->index( $method, $segments );
		return $this;
	}
	
	
	/**
	 * Загрузка файла с конфигами в параметры контроллера.
	 *
	 * @access	public
	 * @param	string	$name	[Имя файла конфигураций]
	 * @return	object			[ссылка на этот класс]
	 */
	public function config( $name = NULL )
	{
		if( !is_string( $name ) )
			return $this;
		if ( !$isLoad = $this->CI->config->load( $name, TRUE, TRUE ) )
			return $this;

		$_CONFIG	= $this->CI->config->item( $name );
		
		unset( $this->CI->config->config[ $name ] );
		
		/**
		 * Загружаем загруженный массив конфигураций в публичные свойства класса.
		 * 
		 * @info	Используем `Reflection`, чтобы игнорировать уже существующие
		 * 			приваатные свойства в классе.
		 */
		
		/** Получаем все приватные и защищенные свойства класса. */
		$_class_Properties	= array();
		$_Reflection		= new ReflectionClass( $this->CI );
		FOREACH( $_Reflection->getProperties() as $_Property )
		{
			if ( $_Property->isPrivate() OR $_Property->isProtected() )
			{
				$_class_Properties[]	= $_Property->name;
			}
		}
	
		/** Добавляем в класс свойства из файла конфигураций. */
		FOREACH ( $_CONFIG as $KEY	=> $v )
		{
			// Игнорируем определенные ранее приватные и защищенные свойства класса
			if ( in_array( $KEY, $_class_Properties ) ) continue;
			
			// Создаем в классе свойство и передаем значение по ссылке.
			$this->CI->$KEY	= $_CONFIG[ $KEY ];
		}
		return $this;
	}
}




/**
 * L_DataBase extends Letcoc Class.
 * 
 * PHP Version 5.3.9
 * 
 * @name		Letcoc DataBase Class.
 * @package		CodeIgniter.
 * @author		Aleksei Zhulitov.
 * @title		Класс расширения Letcoc, для Базы Данных CodeIgniter.
 */
class L_DataBase extends Letcoc {
	
	/**#@+
	 * Class Variables
	 * @access private|protected|public
	 */
	
	/** Свойство для хранения CI DB */
	private $_MySQL						= NULL;
	
	/** Свойство для хранения CI parser */
	private $_PARSER					= NULL;
	
	/** Текущий SQL запрос. */
	private	$_curSQL					= NULL;
	/**#@-*/
	
	/**
	 * КОНСТРУКТОР.
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();
		
		/** Подгружаем DB.  */
		$this->CI->load->database();
		$this->_MySQL		=& $this->CI->db;
		
		/** Подгружаем Парсер. */
		$this->CI->load->library( "parser" );
		$this->_PARSER		=& $this->CI->parser;
	}
	
	
	/**
	 * Метод возвращает наполненный SQL запрос по его имени.
	 * (наполнение может происходить из __REQUEST или из переданного массива `$array` )
	 * 
	 * @access	public
	 * @param	string	$name	[имя SQL запроса]
	 * @param	array	$array	[массив для наполнения (опционально)]
	 * @return	object			[ссылка на этот класс]
	 */
	public function SQL( $name = FALSE, $array = FALSE )
	{
		$this->_curSQL	= NULL;
		if ( !is_string( $name ) )
			return $this;
			
		$SQL	= $this->_fill_SQL( $array );
		
		if ( !isset( $SQL[$name] ) )
			return $this;
		
		$this->_curSQL	= $SQL[ $name ];
		return	$this;
	}
	
	
	/**
	 * Метод выполняет заданный SQL запрос.
	 * ( Запрос может быть передан в виде аргумента 
	 * 	 или установлен через метод `SQL( "имя запроса" )` .)
	 * 
	 * @example	[передача запроса аргументом]
	 *	<pre>
	 *		$this->Letcoc->DB()->query( "SELECT * FROM `table` WHERE `name` = 'some';" );
	 *	</pre>
	 * 
	 * @example	[установка запроса]
	 *	<pre>
	 *		// Список SQL запросов должен быть объявлен или загружен
	 *		// в параметр `SQL` активного конструктора как массив с ключами.
	 *		class Some extends CI_Controller {
	 *			public $SQL = array(
	 *				"select_table" => "SELECT * FROM `table` WHERE `name` = 'some';"
	 *			);
	 *
	 *			public function index() {
	 *				$this->Letcoc->DB()->SQL( "select_table" )->query();
	 *			}
	 *		}
	 * 	</pre>
	 * 
	 * @access	public
	 * @param	string|void	$SQL	[string - строка SQL запроса; void - ничего]
	 * @return	object				[класс CodeIgniter для работы с DB]
	 */
	public function query( $SQL = NULL )
	{
		if ( !is_string( $SQL ) or strlen( $SQL ) < 5 )
		{
			if (
					!is_string( $this->_curSQL )
				or	strlen( $this->_curSQL ) < 5
			){
				return $this->_MySQL;
			}
			else
			{
				$SQL	= $this->_curSQL;
			}
		}
		return $this->_MySQL->query( $SQL );
	}
	
	
	
	/** 
	 * Метод парсит каждый элемент в массиве свойства класса $this->SQL
	 * и подставляет значения из $this->__REQUEST или из переданного массива;
	 * 
	 * @access	private
	 * @param	array	$array	[массив со значениями для подстановки]
	 * @param	bool	$merge	[true = слить 2 массива (array и __REQUEST) ]
	 * @return	array
	 */
	private function _fill_SQL( $array = FALSE, $merge	= FALSE )
	{
		if( !isset( $this->SQL ) or !is_array( $this->SQL ) )
			return array();
		
		$SQL				= array();
		$__REQUEST			= array(
			"__pref__"		=> $this->_MySQL->dbprefix
		);
		
		if ( $merge === TRUE AND is_array( $array ) )
		{
			$array			= array_merge( $array, $this->__REQUEST );
		}
		else
		{
			$array				= is_array( $array )	? $array : $this->__REQUEST;
		}
		
		/* Экранируем ввод. */
		foreach ( $array as $KEY => $VALUE )
		{
			if ( is_string( $VALUE ) )
			{
				$__REQUEST[ $KEY ]	= $this->_MySQL->escape( $VALUE );
			}
			else
			{
				$__REQUEST[ $KEY ]	= $this->_MySQL->escape( (string)-1 );
			}
		}
		
		foreach( $this->SQL as $KEY => $VALUE )
		{
			$SQL[ $KEY ]	= $this->_PARSER->parse_string( (string)$VALUE, $__REQUEST, TRUE );
			$SQL[ $KEY ]	= preg_replace( "/\{\/?(\w*?)\}/", "''", $SQL[ $KEY ] );
		}
		return (array)$SQL;
	}
}





/**
 * L_Spider_silk extends Letcoc Class.
 * 
 * PHP Version 5.3.9
 * 
 * @name		Spider_silk Class.
 * @package		CodeIgniter.
 * @author		Aleksei Zhulitov.
 * @title		Класс позволяющий загружать прочие контроллеры CodeIgniter и вызывать их методы.
 *
 */
class L_Spider_silk extends Letcoc{
	private $_class		= NULL;
	public function __construct ( $class = null )
	{
		$this->_class	= $class;
	}
	public function __call ( $method, $arguments )
	{
		if( method_exists( $this->_class, $method ) )
		{
			call_user_func_array( array( $this->_class, $method ), $arguments );
			return $this->_class;
		}
		return $this;
	}
}




/**
 * _P Class.
 * 
 * PHP Version 5.3.9
 * 
 * @name   		_P Class
 * @package		CodeIgniter
 * @author 		Aleksei Zhulitov
 * @title  		Набор плюшек для разработчика кода.
 * 
 * @todo   		Для получения справки по классу, выполните: <code>_P::DOC();</code>
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
	 * @param	any		$var	[Переменная, информация о которой будет выведена]
	 * @param	string|bool	$title	[string - Заголовок выходящий в начале контейнера с инфой;
	 *       	           	      	bool - `FALSE` не выводить заголовок ]
	 * 
	 * @param	bool		$var	[TRUE  - тэг `pre` будет позиционирован абсолютно]
	 * @param	bool		$return	[TRUE  - вернет без `pre`; FALSE - выведет в браузер ]
	 * @return	void
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
		self::P(
			get_class_methods( $class ),
			"Методы класса - " . get_class( $class )
		);
	}
	
	
	/**
	 * Метод выводящий список параметров
	 * переданного аргументом класса или объекта.
	 * 
	 * @example	<code>_P::CV( $class );</code>
	 * 
	 * @access	public
	 * @param	object	$class	[класс, параметры которого нужно вывести.]
	 * @return	void
	 */
	public static function CV ( $class = "" )
	{
		if( !is_object( $class ) ) return;
		self::P(
			get_object_vars( $class ), 
			"Параметры класса - " . get_class( $class )
		);
	}
	
	
	/**
	 * Метод выводящий список параметров
	 * переданного аргументом класса или объекта.
	 * 
	 * Выводит информацию только о тех параметрах,
	 * значения которых не являются другими классами.
	 *
	 * @example	<code>_P::COV( $class );</code>
	 *
	 * @access	public
	 * @param	object	$class	[класс, параметры которого нужно вывести.]
	 * @return	void
	 */
	public static function COV( $class = "" )
	{
		if( !is_object( $class ) ) return;
			
		$BUFF				=	new stdClass;
		foreach( $class as $name => $value )
		{
			if( is_object( $value ) and get_class( $value ) != "stdClass" )
				continue;
			$BUFF->$name	= $value;
		}
		self::P(
			$BUFF,
			"Методы класса - " . get_class( $class )
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
	 * Метод выводит документацию по указанному классу.
	 * 
	 * @example_1
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
				foreach( split( "[\n\r]{1,2}", $DOC ) as $str )
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
/* End of file Letcoc.php */
/* Location: ./application/controllers/Letcoc.php */