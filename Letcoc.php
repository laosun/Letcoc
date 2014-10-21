<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * (Letcoc) Library extends the capabilities of CodeIgniter.
 * 
 * PHP Version 5.3.9
 * 
 * @name		Letcoc Library Class.
 * @package		Letcoc.
 * @title		Библиотека расширяющая возможности CodeIgniter.
 * @author		Aleksei Zhulitov (LaoSun).
 * @version		0.2 (system)
 * @link		http://github.com/laosun/Letcoc
 */
class Letcoc {
	
	/**#@+
	 * Class Variables
	 * @access private|protected|public
	 */
	/** CI Контроллер (супер объект по ссылке) */
	public			$CI				=	NULL;
	
	/** Метод которым приходят данные от пользователя (_POST||_GET). */
	private			$_REQ_Method	= "_POST";
	
	/** Массив в котором хранятся данные поступившие методом заданным в `$_REQ_Method`. */
	protected		$__REQUEST		= array();
	
	protected static $_instance		=	NULL;
	/**#@-*/

	/**
	 * КОНСТРУКТОР.
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{	
		if ( get_called_class() == "Letcoc" )
		{
			self::$_instance	= & $this;
			include_once( BASEPATH . "libraries/Letcoc_extends/_P.php" );		
		}
		
		$this->CI			= & get_instance();
		$this->initialize();
		
		if ( get_called_class() == "Letcoc" )
		{
			$this->_instance	= & $this;
		}
		else
			$this->_instance 	= & self::$_instance;
		
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
		 * Блок проверки доступности свойств контроллера.
		 **/
			$_check_REQ_Method	= _P::CP_access( $this->CI, "_REQ_Method" );
			$_check___REQUEST	= _P::CP_access( $this->CI, "__REQUEST" );
		
			if ( $_check___REQUEST !== FALSE AND $_check___REQUEST !== "public" )
				return;
		
			if ( $_check_REQ_Method !== FALSE AND $_check_REQ_Method !== "public" )
				return;
	
		/**
		 * Блок назначения параметров для приема
		 * входящих данных и прием данных.
		 */
			if ( isset( $this->CI->_REQ_Method ) )
				$this->_REQ_Method		= & $this->CI->_REQ_Method;
			else
				$this->CI->_REQ_Method	= & $this->_REQ_Method;
		
			$this->__REQUEST			=  $GLOBALS[ $this->_REQ_Method ];
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
	 * Метод позволяет проверить а потом получить
	 * переданное в __REQUEST значение.
	 * 
	 * @access	public
	 * @param	string	$value	[Имя ключа в __REQUEST для получения]
	 * @param	string	$deff	[Значение возвращаемое в случае отсутствия аргуманта]
	 * @return	value||NULL		[value - значение; NULL - если ключа нет. ]
	 */
	public function get_REQ( $value = "", $deff = NULL )
	{
		if ( isset( $this->__REQUEST) and isset( $this->__REQUEST[$value] ) )
		{
			return $this->__REQUEST[$value];
		}
		return $deff;
	}
	
	
	
	/** 
	 * Метод для инициализации и получения класса Letcoc контроллера.
	 * 
	 * @access	public
	 * @return	object	[ссылка на класс L_Controller]
	 */
	public function Controller() {
		if ( !isset( $this->_instance->Controller ) )
		{
			include_once( BASEPATH . "libraries/Letcoc_extends/L_Controller.php" );
			$this->_instance->Controller = new L_Controller;
		}
		return $this->_instance->Controller;
	}
	
	/** 
	 * Метод для инициализации и получения класса Letcoc DB.
	 * 
	 * @access	public
	 * @return	object	[ссылка на класс L_DataBase]
	 */
	public function DB( $config = NULL ) {
		
		if ( !isset( $this->_instance->DB ) )
		{
			include_once( BASEPATH . "libraries/Letcoc_extends/L_DataBase.php" );
			$this->_instance->DB = new L_DataBase($config);
		}
		return $this->_instance->DB;
	}
	
	

	/** 
	 * Метод для инициализации и получения класса Letcoc Lib.
	 * 
	 * Если указать в качестве первого аргумента ($library)
	 * имя библиотеки расположенной в `./application/libraries/`,
	 * загрузит ее если она там имеется.
	 * 
	 * Если указать второй аргумент ($isConfig) = TRUE,
	 * попытается загрузить файл с конфигурацией располоденный в
	 * `./application/config/` в загружаемую библиотеку.
	 * !! Имя файла конфигурации должно совпадать с именем файла библиотеки.
	 * 
	 * @access	public
	 * @param	string	$library	[Имя библиотеки которую нужно подгрузить в СI контроллер]
	 * @param	bool	$isConfig	[TRUE - подгрузить в библиотеку файл с конфигурацией; FALSE - нет;]
	 * @return	object				[ссылка на класс L_Library]
	 */
	public function Lib( $library = NULL, $isConfig = FALSE ) {
		
		$library	= ( strlen( (string) $library ) > 0 )	? $library  : NULL;
		$isConfig	= ( is_bool( $isConfig ) )				? $isConfig : FALSE;
		
		if ( !isset( $this->_instance->Lib ) )
		{
			include_once( BASEPATH . "libraries/Letcoc_extends/L_Library.php" );
			$this->_instance->Lib = new L_Library( $library, $isConfig );
		}
		else if ( isset( $this->_instance->Lib ) AND !is_null( $library ) )
		{
			$this->_instance->Lib->load( $library, $isConfig );
		}

		return $this->_instance->Lib;
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
		$class_ = ucfirst( strtolower( $class_ ) );
		if ( !class_exists( "L_Spider_silk" ) )
		{
			include_once( BASEPATH . "libraries/Letcoc_extends/L_Spider_silk.php" );
		}
		
		$FALSE	= new L_Spider_silk();
		if ( !is_object( $this->CI ) )
			return $FALSE;
			
		if ( strtolower( get_class( $this->CI ) ) == strtolower( $class_ ) )
			return $FALSE;
		
		$class_file_name	= strtolower( $class_ );
		if ( !file_exists( APPPATH . "controllers/{$class_file_name}.php" ) )
			return $FALSE;
	
		include_once( APPPATH . "controllers/{$class_file_name}.php" );
		
		if( class_exists( $class_ ) )
		{
			$_class = new $class_;
		}
		else
		{	
			$_class	= new stdClass;
		}
		
		$this->CI->load->Loaded_controller	= & $_class;
		
		return new L_Spider_silk( $_class );
	}
}

/* End of file Letcoc.php */
/* Location: ./system/libraries/Letcoc.php */