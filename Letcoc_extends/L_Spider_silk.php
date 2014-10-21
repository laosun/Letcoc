<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * L_Spider_silk extends Letcoc Class.
 * 
 * PHP Version 5.3.9
 * 
 * @name		L_Spider_silk Class.
 * @package		Letcoc.
 * @author		Aleksei Zhulitov.
 * @title		Класс позволяющий загружать другие контроллеры CodeIgniter и вызывать их методы.
 *
 */
class L_Spider_silk extends Letcoc{
	public $_class		= NULL;
	public function __construct ( $class = null )
	{
		$this->_class	= $class;
	}
	
	public function __call ( $method, $arguments )
	{
		if( method_exists( $this->_class, $method ) )
		{
			return call_user_func_array( array( $this->_class, $method ), $arguments );
		}
		return $this;
	}
	
	public function __get( $name ) {
		if		( isset( $this->_class->$name ) )
			return $name = & $this->_class->$name;
		
		$CI	= & get_instance();
		if	( isset( $CI->$name ) )
			return $name = & $CI->$name;
		
		_exception_handler( E_ERROR, "No such properties '{$name}' in Classes `".get_class($this->_class)."` or `Controller`", "Load_other_controller", 0 );
	}
}

/* End of file L_Spider_silk.php */
/* Location: ./system/libraries/Letcoc_extends/L_Spider_silk.php */