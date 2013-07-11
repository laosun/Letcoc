<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Sample Controller Class
 * 
 * PHP Version 5.3.9
 * 
 * @name		Sample Controller Class
 * @package		CodeIgniter
 * @subpackage	Controller
 * 
 * ---- SQL DUMP ---- в помощь --- 
 *	CREATE TABLE `site_users` (
 *		`id` INT NOT NULL AUTO_INCREMENT ,
 *		`user_name` VARCHAR( 50 ) NOT NULL ,
 *		`mail` VARCHAR( 50 ) NOT NULL ,
 *		`pass` VARCHAR( 50 ) NOT NULL ,
 *		PRIMARY KEY ( `id` )
 *	);
 *	INSERT INTO `site_users` (`id`,`user_name`,`mail`,`pass`) VALUES ('1','sample_user','user@site.ru','123_123');
 *	INSERT INTO `site_users` (`id`,`user_name`,`mail`,`pass`) VALUES ('2','other_user','other@domen.ru','333111');
 */
class Sample extends CI_Controller {
	
	/**#@+
	 * Class Variables
	 * @access public
	 */
		/**
		 *  Массив с SQL запросами для Letcoc->DB()
		 * 	( префикс таблиц задается через {__pref__} )
		 * !!! Значение этого свойства можно не указывать, оно будет загружено из ./application/config/sample.php !!!
		 **/
		public $SQL	= NULL;
	/**#@-*/

	/**
	 * Метод главного направления.
	 * 
	 * @example	[http://you_site.name/sample/]
	 *
	 * @access	public
	 * @param	array $arg
	 * @param	array $segments
	 * @return	void
	 */
	public function index( $arg = NULL, $segments = NULL )
	{	
		/**
		 * В этот класс контроллера загружаются все значения ключей из конфига `./application/config/sample.php`
		 * 
		 * Если в конфиге указаны:
		 * 		$config["this_is_public_var"]	=	"А это его значение!";
		 *		$config["Array"]				=	array( "11", "22" ,"33" );
		 *		$config["Int"]					=	10;
		 *
		 * То они будут загружены в класс контроллера и будут доступны как:
		 * 		$this->this_is_public_var	=	"А это его значение!";
		 * 		$this->Array				=	array( "11", "22" ,"33" );
		 * 		$this->Int					=	10;
		 */
		_P::COV( $this );
		
		
		/**
		 * В свойство класса `SQL` загружено значение ключа `SQL` из конфига `./application/config/sample.php`
		 * т.е. оно содержит следующее значение:
		 * 	array(
			*		"get_user_by_name"	=> "SELECT * FROM `{__pref__}users` WHERE `user_name` = {user_name} LIMIT 1;"
			*	)
		**/
		_P::P( $this->SQL, "Содержимое свойства класса SQL" );
		
		
		/**
		 * Запрос на получение информации о пользователе с именем `sample_user`.
		 **/
		$result	= $this->Letcoc
					->DB()
					
						/**
						 * Метод SQL класса `L_DataBase`.
						 * @info [https://github.com/laosun/Letcoc/blob/master/Letcoc_extends/L_DataBase.php#L59]
						 * 
						 * Устанавливает активный sql запрос - `get_user_by_name`, в котором шаблоны {[_a-z0-9]}
						 * автоматически заменены на соответствующие значения:
						 **		{__pref__}  - префикс базы данных.
						 * 					  ! указывается в ./application/config/database.php !
						 * 					  ! $db['default']['dbprefix']	= "site_";			!
						 * 
						 **		{user_name} - передан в массиве вторым аргументом.
						 *					  ! array("user_name"=>"sample_user")	!
						 * 
						 * В результате активный sql будет следующий:
						 **		SELECT * FROM `site_users` WHERE `user_name` = `sample_user` LIMIT 1;
						 * 
						 */
						->SQL( "get_user_by_name", array("user_name"=>"sample_user") )
						
							/**
							 * Метод query класса `L_DataBase`.
							 * @info	[https://github.com/laosun/Letcoc/blob/master/Letcoc_extends/L_DataBase.php#L76]
							 * 
							 * Метод выполняет установленный SQL запрос.
							 * 
							 * Если sql запрос выполнен успешно, возвращается набор методов CodeIgniter для получения результата.
							 * @info	[http://ellislab.com/codeigniter/user-guide/database/results.html]
							 */
							->query()
							
								/**	
								 * Метод `first_row` класса `CI_DB_result` (./system/database/DB_result.php).
								 * 
								 * Returns the "first" row.
								 * 
								 * Результат выполнения:
								 * 		stdClass Object
								 *		(
								 *			[id] => 1
								 *			[user_name] => sample_user
								 *			[mail] => user@site.ru
								 *			[pass] => 123_123
								 *		)
								 **/
								->first_row();
		/* ВЫВОД */
		_P::P( $result, "Результат запроса<br>\t\$this->Letcoc<br>\t\t->DB()<br>\t\t->SQL( \"get_user_by_name\", array(\"user_name\"=>\"sample_user\") )<br>\t\t->query()<br>\t\t->first_row();" );
		
		
		/**
		 * ТАК ЖЕ!
		 * 
		 * Если были отправлены [POST] данные, то они автоматически загружаются
		 * в свойство класса контроллера `$this->__REQUEST`
		 **/
		 /* ВЫВОД */
		_P::P( $this->__REQUEST, "POST" );
		
		
		/**
		 * Чтобы перезагрузить `$this->__REQUEST` данными из [GET]
		 * @example		[http://you_site.name/sample/?foo=bar&test=some]
		 */
		$this->Letcoc->set_REQ( "_GET" );
		/* ВЫВОД */
		_P::P( $this->__REQUEST, "GET" );
		
		
		
		/**
		 * Получаем справку по методам класса _P (Плюшки) 
		 **/
		 _P::DOC();
	}
	
	
	
	/**
	 * Метод прочего направления.
	 *
	 * @example  [http://you_site.name/sample/other/]
	 *
	 * @access	public
	 * @return	void
	 */
	public function other(){
		/**
		 * Получаем справку по методам `этого` класса контроллера.
		**/
		_P::DOC( $this );
	}

	/* ############################### CORE FUNCTION ########################### */
	
	/**
	 * КОНСТРУКТОР
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct ()
	{
		parent::__construct();
		
		/** Загружаем Библиотеку Letcoc. */
		$this->load->library( "Letcoc", NULL, "Letcoc" );
	}

	
	
	/**
	 * МАРШРУТИЗАТОР
	 * (Ремаппинг вызовов функций)
	 * 
	 * @access	public
	 * @param	string $method
	 * @return	void
	 */
	public function _remap ( $method = "index" )
	{
		$this->Letcoc
		
			 /**
			  * Загружаем и создаем экземпляр класса `L_DataBase`
			  * @info	[https://github.com/laosun/Letcoc/blob/master/Letcoc.php#L159]
			  * 
			  * (Далее обращаться к этому экземпляру можно через `$this->Letcoc->DB()`  )
			  */
			->DB()
			
			/**
			 * Загружаем и создаем экземпляр класса `L_Controller`
			 * @info	[https://github.com/laosun/Letcoc/blob/master/Letcoc.php#L144]
			 * 
			 * (Далее обращаться к этому экземпляру можно через `$this->Letcoc->Controller()`  )
			 */
			->Controller()
			
				/**
				 * Метод `config` экз.класса `L_Controller`.
				 * @info	[https://github.com/laosun/Letcoc/blob/master/Letcoc_extends/L_Controller.php#L47]
				 *
				 * Загружает в свойства этого контроллера конфиг ./application/config/sample.php
				 */
				->config("sample")
				
				/**
				 * Выполняем метод `_remap` экз.класса `L_Controller`.
				 * @info [Ремаппинг вызовов функций - http://ellislab.com/codeigniter/user-guide/general/controllers.html]
				 * @info [https://github.com/laosun/Letcoc/blob/master/Letcoc_extends/L_Controller.php#L24]
				 */
				->_remap();
	}
}

/* End of file sample.php */
/* Location: ./application/controllers/sample.php */