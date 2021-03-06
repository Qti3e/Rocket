<?php
/**
 * This class will call the functions of any class with the right options
 *
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 3
 * @author  QTIƎE <Qti3eQti3e@Gmail.com>
 */

namespace core\factory;

use core\rocket\rocket;

/**
 * Class caller
 * @package core\factory
 */
class call {
	/**
	 * It will return when method is not callable
	 */
	const notCallable   = -100;
	/**
	 * Convert name to class name
	 * @var array
	 */
	protected static $map   = [
		'redis'     => 'core\\redis\\redis',
		'lang'      => 'core\\i18n\\lang',
		'i18n'      => 'core\\i18n\\lang',
		'country'   => 'core\\i18n\\country',
		'helper'    => 'core\\helpers\\helper',
		'session'   => 'core\\session\\session',
		'validator' => 'core\\validate\\validator',
		'cdn'       => 'core\\cdn\\cdns',
		'date'      => 'core\\date\\date.php',
		'ftp'       => 'core\\ftp\\ftp',
		'algorithms'=> 'core\\algorithms\\algorithms',
		'call'      => 'core\\factory\\call',
		'interval'  => 'core\\interval\\interval',
		'sender'    => 'core\\sender\\sender',
		'router'    => 'core\\router\\routerProvider', //Alias of routerProvider
		'routerProvider'    => 'core\\router\\routerProvider',
	];

	/**
	 * @var array
	 */
	protected static $defaults  = [];

	/**
	 * Register a default value
	 * @param $name
	 * @param $value
	 *
	 * @return void
	 */
	public static function register($name,$value){
		static::$defaults[$name]    = $value;
	}

	/**
	 * Clear all of default values
	 * @return void
	 */
	public static function clear(){
		static::$defaults   = [];
	}

	/**
	 * Call a function
	 * @param       $class
	 * @param       $method
	 *
	 * @param array $defaultValues
	 *
	 * @return mixed
	 */
	public static function method($class,$method,$defaultValues = []){
		$defaultValues['redis'] = rocket::redis();
		$defaultValues  = $defaultValues+static::$defaults;
		if(!is_callable([$class,$method])){
			return static::notCallable;
		}
		$c      = $class;
		$class = new \ReflectionClass($class);
		$values     = [];
		$parameters = $class->getMethod($method)->getParameters();
		$count      = count($parameters);
		for($i  = 0;$i < $count;$i++){
			$name   = $parameters[$i]->getName();
			$type   = $parameters[$i]->getType();
			if(isset($defaultValues[$name])){
				$values[]   = $defaultValues[$name];
			}elseif ($type === null){
				$values[]   = self::newInstance($name,$defaultValues,true);
			}else{
				$values[]   = self::newInstance($type,$defaultValues);
			}
		}
		return call_user_func_array([$c,$method],$values);
	}

	/**
	 * Create new instance of given class
	 * @param       $class
	 *  Class name or object
	 * @param array $defaultValues
	 *  Default values
	 * @param bool  $convert2class
	 *  Convert class' name to location via name2class function when it's true
	 * @return null|object
	 */
	public static function newInstance($class,$defaultValues = [],$convert2class = false){
		$class  = $convert2class ? self::name2class($convert2class) : $class;
		$defaultValues['redis'] = rocket::redis();
		$defaultValues  = $defaultValues+static::$defaults;
		if(class_exists($class) || is_object($class)){
			$class = new \ReflectionClass($class);
			if($class->getConstructor() === null || $class->getConstructor()->getNumberOfParameters() == 0){
				return $class->newInstance();
			}
			$parameters = $class->getConstructor()->getParameters();
			$values     = [];
			$count      = count($parameters);
			for($i  = 0;$i < $count;$i++){
				$name   = $parameters[$i]->getName();
				$type   = $parameters[$i]->getType();
				if(isset($defaultValues[$name])){
					$values[]   = $defaultValues[$name];
				}elseif($type === null){
					$values[]   = self::newInstance($name,true);
				}else{
					$values[]   = self::newInstance($type);
				}
			}
			return $class->newInstance(...$values);
		}
		return null;
	}

	/**
	 * Convert parameter's name to class name
	 * @param $name
	 *
	 * @return mixed
	 */
	public static function name2class($name){
		return isset(static::$map[$name]) ? static::$map[$name] : $name;
	}

	/**
	 * Call a function that is outside of a class
	 * @param       $function
	 * @param array $defaultValues
	 *
	 * @return int|mixed
	 *
	 */
	public static function func($function,$defaultValues = []){
		$defaultValues['redis'] = rocket::redis();
		$defaultValues  = $defaultValues+static::$defaults;
		if(!is_callable($function)){
			return static::notCallable;
		}
		$f          = $function;
		$function   = new \ReflectionFunction($function);
		$parameters = $function->getParameters();
		$count      = count($parameters);
		$values     = [];
		for($i  = 0;$i < $count;$i++){
			$name   = $parameters[$i]->getName();
			$type   = $parameters[$i]->getType();
			if(isset($defaultValues[$name])){
				$values[]   = $defaultValues[$name];
			}elseif ($type === null){
				$values[]   = self::newInstance(self::name2class($name));
			}else{
				$values[]   = self::newInstance($type);
			}
		}
		return call_user_func_array($f,$values);
	}
}