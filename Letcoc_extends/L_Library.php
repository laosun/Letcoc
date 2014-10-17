<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * L_Library extends Letcoc Class.
 * 
 * PHP Version 5.3.9
 * 
 * @name		Letcoc DataBase Class.
 * @package		Letcoc.
 * @author		Aleksei Zhulitov.
 * @title		Класс расширения Letcoc, для загрузки библиотек CodeIgniter.
 */
class L_Library extends Letcoc {
	
	/**#@+
	 * Class Variables
	 * @access private|protected|public
	 */
	/**#@-*/
	
	/**
	 * КОНСТРУКТОР.
	 * 
	 * @param	$library	[смотрите описание метода `load`]
	 * @param	$isConfig	[смотрите описание метода `load`]
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct(  $library = NULL, $isConfig = FALSE  )
	{
		parent::__construct();
		
		if ( !is_null( $library ) )
		{		
			$this->load( $library, $isConfig );
		}
	}
	
	/** 
	 * Метод для загрузки требуемой библиотеки в CI контроллер.
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
	public function load( $library = NULL, $isConfig = FALSE )
	{
		$library	= ( strlen( (string) $library ) > 0 )	? strtolower ($library)  : NULL;
		$isConfig	= ( is_bool( $isConfig ) )				? $isConfig				 : FALSE;
		
		if( $library === NULL )
			return $this;
		
		if( !file_exists( APPPATH . "libraries/{$library}.php" ) AND !file_exists( BASEPATH . "libraries/{$library}.php" ) ) {
			return $this;
		}
		
		if ( !isset( $this->CI->$library ) )
		{
			$this->CI->load->library( $library, array() );
		}
		
		if ( isset( $this->CI->$library ) )
		{
			if ( !method_exists( $this->CI->$library, "CI" ) ) {
				$this->CI->$library->CI		= &get_instance();
			}
			if ( !method_exists( $this->CI->$library, "Letcoc" ) ) {
				$this->CI->$library->Letcoc	= &$this->CI->Letcoc;
			}
		}
		
		if ( isset( $this->CI->$library ) AND $isConfig ===  TRUE )
		{
			$this->config( $library, $library );
		}
		return $this;
	}
	
	
	
	
	/** 
	 * Метод для загрузки в узазанную библиотеку указанного файла конфигураций.
	 * 
	 * Если указанная библиотека уже загруженна в CI контроллер, в нее будет
	 * загружен указанный файл конфигураций.
	 * 
	 * Имена ключей станут именами свойств указанного класса и получат значения
	 * ключей конфигурации.
	 * 
	 * Если в классе уже имеются свойства совпадающие с именами ключей,
	 * значения свойств класса будут переопределены на значения ключей.
	 * 
	 * Если имена свойств имеют модификатор доступа отличный от `public`,
	 * они будут проигнорированны.
	 * 
	 * @access	public
	 * @param	string	$library_name	[Имя библиотеки в которую нужно загрузить]
	 * @param	string	$config_name	[Имя файла конфигураций расположенного в `./application/config/` ]
	 * @return	object					[ссылка на класс L_Library]
	 */
	public function config( $library_name = NULL, $config_name = FALSE )
	{
		if (
			!is_string( $library_name )
			OR !is_string( $config_name )
			OR !isset( $this->CI->$library_name )
			OR !is_object( $this->CI->$library_name )
		){
			return $this;
		}
		
		
		if( !file_exists( APPPATH . "config/{$config_name}.php" ) )
		{
			return $this;
		}
		
		$_CONFIG	= $this->_get_included_var(APPPATH . "config/{$config_name}.php");
		
		if ( !isset( $_CONFIG["config"] ) OR !is_array( $_CONFIG["config"] ) )
			return $this;
		
		$_CONFIG = $_CONFIG["config"];
		
		/**
		 * Загружаем загруженный массив конфигураций в публичные свойства класса.
		 * 
		 * @info	Используем `Reflection`, чтобы игнорировать уже существующие
		 * 			приваатные свойства в классе.
		 */
		
		/** Получаем все приватные и защищенные свойства класса. */
		$_class_Properties	= array();
		$_Reflection		= new ReflectionClass( $this->CI->$library_name );
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
			$this->CI->$library_name->$KEY	= $_CONFIG[ $KEY ];
		}
		return $this;
	}
	
	
	
	/**
	 * Метод подгружает указанный файл и возвращает массив переменных которые в нем определены.
	 * 
	 * @access	private
	 * @param	string	$full_file_name	[полный путь с именем подключаемого файла ]
	 * @return	array
	 */
	private function _get_included_var( $full_file_name = NULL )
	{
		if ( !file_exists( $full_file_name ) )
			return array();
		
		include( $full_file_name );
		
		unset( $full_file_name );

		return get_defined_vars();
	}
	
}
/* End of file L_Library.php */
/* Location: ./system/libraries/Letcoc_extends/L_Library.php */