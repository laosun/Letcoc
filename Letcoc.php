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
		error_reporting( 0 );
		
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
 * L_Controller Letcoc Class.
 * 
 * PHP Version 5.3.9
 * 
 * @name		Letcoc Controller Class
 * @package		CodeIgniter
 * @author		Aleksei Zhulitov
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
 * L_DataBase Letcoc Class.
 * 
 * PHP Version 5.3.9
 * 
 * @name		Letcoc DataBase Class
 * @package		CodeIgniter
 * @author		Aleksei Zhulitov
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
 * L_Spider_silk Letcoc Class.
 * 
 * PHP Version 5.3.9
 * 
 * @name		Spider_silk Class
 * @package		CodeIgniter
 * @author		Aleksei Zhulitov
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


/* End of file Letcoc.php */
/* Location: ./application/controllers/Letcoc.php */