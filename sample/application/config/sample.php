<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| Sample config file
| -------------------------------------------------------------------
|	После загрузки этого конфигурационного файла в одноименный контроллер
|	через библиотеку Letcoc, указанные здесь конфигурации, станут
|	публичными свойствами этого класса контроллера.
*/

/**
 *  Массив с SQL запросами для Letcoc->DB()
 * 	( префикс таблиц задается через {__pref__} )
 */
$config["SQL"]					=	array(
	"get_user_by_name"	=> "SELECT * FROM `{__pref__}users` WHERE `user_name` = {user_name} LIMIT 1;"
);


$config["this_is_public_var"]	=	"А это его значение!";
$config["Array"]				=	array( "11", "22" ,"33" );
$config["Int"]					=	10;

/* End of file sample.php */
/* Location: ./application/config/sample.php */
?>