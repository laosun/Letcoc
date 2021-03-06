<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * L_Controller extends Letcoc Class.
 * 
 * PHP Version 5.3.9
 * 
 * @name		Letcoc Controller Class.
 * @package		Letcoc.
 * @author		Aleksei Zhulitov.
 * @title		Класс расширения Letcoc, для Контроллеров CodeIgniter.
 */
class L_Controller extends Letcoc{
	
	/**
	 * КОНСТРУКТОР.
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct(){parent::__construct();}

	/**
	 * МАРШРУТИЗАТОР.
	 * Осуществляет ремаппинг в требуемый метод активного класса контроллера.
	 *
	 * @todo	[Если требуемый метод в классе контроллере отсутствует или не является <code>public</code>, вызов будет перенаправлен в метод <code>	public function index(){...}</code>]
	 * 
	 * @access	public
	 * @param	string	$method
	 * @return	object	[this - L_Controller class]
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
	 * @return	object			[this - L_Controller class]
	 */
	public function config( $name = NULL )
	{
		if( !is_string( $name ) )
			return $this;
		if ( !$isLoad = $this->CI->config->load( $name, TRUE, TRUE ) )
			return $this;

		$_CONFIG	= $this->CI->config->item( $name );
		
		//Отключена unset( $this->CI->config->config[ $name ] );
		
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
	
	
	
	/**
	 * Метод очищает блоки переменных ({a-z}) оставшиеся после парсера.
	 * 
	 * @access	public
	 * @return	object	[this - L_Controller class]
	 * 
	 */
	public function clear_parse()
	{
		$this->CI->output->set_output(
			preg_replace(
				"/\{\/?(\w*?)\}/",
				"",
				$this->CI->output->get_output()
			)
		);
	}
	
}

/* End of file L_Controller.php */
/* Location: ./system/libraries/Letcoc_extends/L_Controller.php */