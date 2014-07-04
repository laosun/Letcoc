<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * L_DataBase extends Letcoc Class.
 * 
 * PHP Version 5.3.9
 * 
 * @name		Letcoc DataBase Class.
 * @package		Letcoc.
 * @author		Aleksei Zhulitov.
 * @title		Класс расширения Letcoc, для Базы Данных CodeIgniter.
 */
class L_DataBase extends Letcoc {
	
	/**#@+
	 * Class Variables
	 * @access private|protected|public
	 */
	
	/** Текущий SQL запрос. */
	private	$_curSQL					= NULL;
	
	/** Не экранируемые ключи-константы. */
	private $__constant					= array();
	/**#@-*/
	
	/**
	 * КОНСТРУКТОР.
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct($config = NULL)
	{
		parent::__construct();

		/** Подгружаем DB.  */
		$this->CI->load->database( (is_string($config) ? $config : "") );
		
		/** Подгружаем Парсер. */
		$this->CI->load->library( "parser" );
	}
	
	
	/**
	 * Метод возвращает наполненный SQL запрос по его имени.
	 * (наполнение может происходить из __REQUEST или из переданного массива `$array` )
	 * 
	 * @access	public
	 * @param	string	$SQL_or_name	[имя SQL запроса или SQL запрос]
	 * @param	array	$array			[массив для наполнения (опционально)]
	 * @return	object					[ссылка на этот класс]
	 */
	public function SQL( $SQL_or_name = FALSE, $array = FALSE )
	{
		$this->_curSQL		= NULL;
		if ( !is_string( $SQL_or_name ) )
			return $this;
		
		if( !isset( $this->SQL ) or !is_array( $this->SQL ) )
		{
			if ( isset( $this->CI->SQL ) AND is_array( $this->CI->SQL )	AND _P::CP_access( $this->CI, "SQL", "public" )	)
				$this->SQL	= & $this->CI->SQL;
			else
				$this->SQL	= array();
		}
		
		if ( isset( $this->SQL[$SQL_or_name] ) AND is_string($this->SQL[$SQL_or_name]) )
			$SQL			= $this->SQL[$SQL_or_name];
		elseif ( strlen($SQL_or_name) > 10 )
			$SQL			= $SQL_or_name;
		else
			return $this;
		
		$this->_curSQL			= $this->_fill_SQL( $SQL, $array );
		$this->__constant	= array();
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
	 * @param	bool		$parse	[true - заменить константы в переданном первым аргументом запросе]
	 * @return	object				[класс CodeIgniter для работы с DB]
	 * @return	object				[L_DataBase_FIX - если нет SQL запроса и нет запроса в памяти.]
	 */
	public function query( $SQL = NULL, $parse = FALSE )
	{
		if ( !is_string( $SQL ) or strlen( $SQL ) < 5 )
		{
			if (
					!is_string( $this->_curSQL )
				or	strlen( $this->_curSQL ) < 5
			){
				return new L_DataBase_FIX();
			}
			else
			{
				$SQL	= $this->_curSQL;
			}
		}
		
		if ( $parse === TRUE ) {
			$__REQUEST		= array(
				"__pref__"	=> $this->CI->db->dbprefix
			);
			$__REQUEST		= array_merge( $__REQUEST, (array)$this->__constant );
			$SQL			= $this->CI->parser->parse_string( (string)$SQL, $__REQUEST, TRUE );
			$SQL			= preg_replace( "/\{\/?(\w*?)\}/", "''", $SQL );
		}
		
		return $this->CI->db->query( $SQL );
	}
	
	
	/**
	 * Метод добавляет не экранируемую ключь-константу.
	 * (Константы используется при дополнении SQL запроса, но не экранируются так же как и константа __pref__)
	 * 
	 * Список заданных констант очищается после использования метода SQL.
	 * 
	 * @important	Рекомендуется использовать следующие имена ключь-констант `__***__`: __name__, __sample__, ...
	 * 
	 * @access	public
	 * @param	string	$key	[Имя ключь-константы]
	 * @param	string	$value	[значение ключь-константы]
	 * @return	object			[ссылка на этот класс]
	 */
	public function addConstant	( $key = FALSE, $value = FALSE ) {
		if ( is_string( $key ) AND is_string( $value ) )
			$this->__constant[$key]	= $value;
		return $this;
	}
	
	
	/** 
	 * Метод парсит переданных SQL запрос
	 * и подставляет значения из $this->__REQUEST или из переданного массива;
	 * 
	 * @access	private
	 * @param	array	$array	[массив со значениями для подстановки]
	 * @param	bool	$merge	[true = слить 2 массива (array и __REQUEST) ]
	 * @return	array
	 */
	private function _fill_SQL( $SQL = "", $array = FALSE, $merge	= FALSE )
	{
		$array	= (is_object($array)) ? (array)$array : $array;
		
		$__REQUEST					= array_merge(
			array(
				"__pref__"		=> $this->CI->db->dbprefix
			),
			(array)$this->__constant
		);
		
		if ( $merge === TRUE AND is_array( $array ) )
			$array					= array_merge( $array, $this->__REQUEST );
		else
			$array					= is_array( $array )	? $array : $this->__REQUEST;
		
		
		/* Экранируем ввод. */
		foreach ( $array as $KEY => $VALUE ) {
			if ( is_string( $VALUE ) or is_int( $VALUE ) or is_bool( $VALUE) )
				$__REQUEST[ $KEY ]	= $this->CI->db->escape( $VALUE );
			else
				$__REQUEST[ $KEY ]	= $this->CI->db->escape( (string)-1 );
		}
		

		$SQL						= $this->CI->parser->parse_string( $SQL, $__REQUEST, TRUE );
		return preg_replace( "/\{\/?(\w*?)\}/", "''", $SQL );
	}
}



/**
 * L_DataBase_FIX.
 * 
 * PHP Version 5.3.9
 * 
 * @name		L_DataBase_FIX Class.
 * @package		Letcoc.
 * @author		Aleksei Zhulitov.
 * @title		Класс перехватывающий вызовы к CI_DB_result,
 * 				при отсутствии запроса в методе `query` класса `L_DataBase`.
 */
class L_DataBase_FIX{
	public function __call( $method, $arg = array() )
	{
		switch( $method )
		{
			case "num_rows":
			case "num_fields":
				return 0;
			
			case "list_fields":
			case "field_data":
			case "result":
			case "custom_result_object":
			case "result_object":
			case "result_array":
			case "row":
			case "custom_row_object":
			case "row_object":
			case "row_array":
			case "first_row":
			case "last_row":
			case "next_row":
			case "previous_row":
				return array();
			
			case "free_result":
				return NULL;

			case "set_row":
				return $this;
				
			default:
				return $this;
		}
	}
};
/* End of file L_DataBase.php */
/* Location: ./system/libraries/Letcoc_extends/L_DataBase.php */