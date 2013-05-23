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
	
	/** Массив в котором хранятся данные поступившие методом заданным в `$_REQ_Method`. */
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
		
		/** Плюшки грузим пачками. :) */
		if ( get_class( $this ) == "Letcoc" )
		{
			include_once( APPPATH . "libraries/Letcoc_extends/_P.php" );
		}
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
		 * Блок назначения параметров, для приема
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
	 * Метод для инициализации и получения класса Letcoc Контроллера.
	 * 
	 * @access	public
	 * @return	object	[ссылка на класс L_Controller]
	 */
	public function Controller() {
		if ( !isset( $this->Controller ) )
		{
			include_once( APPPATH . "libraries/Letcoc_extends/L_Controller.php" );
			$this->Controller = new L_Controller;
		}
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
		{
			include_once( APPPATH . "libraries/Letcoc_extends/L_DataBase.php" );
			$this->DB = new L_DataBase;
		}
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
		if ( !class_exists( "L_Spider_silk" ) )
		{
			include_once( APPPATH . "libraries/Letcoc_extends/L_Spider_silk.php" );
		}
		
		$FALSE	= new L_Spider_silk();
		if ( !is_object( $this->CI ) )
			return $FALSE;
			
		if ( strtolower( get_class( $this->CI ) ) == strtolower( $class_ ) )
			return $FALSE;
		
		if ( !file_exists( APPPATH . "controllers/{$class_}.php" ) )
			return $FALSE;
	
		include_once( APPPATH . "controllers/{$class_}.php" );
		
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

/* End of file Letcoc.php */
/* Location: ./application/libraries/Letcoc.php */