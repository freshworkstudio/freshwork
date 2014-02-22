<?php
class Hook{
	var $events;
	
	function add_filter($filter,$function,$priority = 10,$accepted_arguments=1){		
		$c=(isset($this->events[$filter][$priority]) && is_array($this->events[$filter][$priority]))?count($this->events[$filter][$priority]):0;
		$this->events[$filter][$priority][$c] = array('function'=>$function, 'accepted_args'=>$accepted_arguments);
	}
		
	/**
	* Executes the functions addeed to the specified filter
	* The function allows for additional arguments to be added and passed
	* <code>
	* function example_hook($string, $arg1, $arg2)
	* {
	*              //Do stuff
	*              return $string;
	* }
	* $value = apply_filters('example_filter', 'filter me', 'arg1', 'arg2');
	* </code>
	*
	* @TODO: Order the events array to match their priority.
	*
	* @param string $tag The name of the filter hook.
	* @param mixed $value The value on which the filters hooked to <tt>$tag</tt> are applied on.
	* @param mixed $var,... Additional variables passed to the functions hooked to <tt>$tag</tt>.
	* @return mixed The filtered value after all hooked functions are applied to it.
	*/
	function apply_filters($filter, $value = ''){
		$args = func_get_args();
		
		if(!isset($this->events[$filter]) || !is_array($this->events[$filter]))return $value;
		
		ksort($this->events[$filter]);
		foreach( $this->events[$filter] as $priority =>$listeners ){
			foreach($listeners as $listener){
				if ( !is_null($listener['function']) ){
					$args[1] = $value;
					if(is_callable($listener['function'])){
						$value = call_user_func_array($listener['function'], array_slice($args, 1,$listener['accepted_args']));
					}else{
						trigger_error("The function '".$listener['function']."' can't be called from hook '$filter'");	
					}
				}
			}
		}
		return $value;
	}
	function remove_filter($filter,$function){
			
	}
	function remove_all_filters($filter){
		unset($this->events[$filter]);
		return true;
	}
	
	/** Listener */
	function add_listener($filter,$function,$priority = 10,$accepted_arguments=1){
		return $this->add_filter($filter,$function,$priority,$accepted_arguments);	
	}
	function trigger($filter){
		$args = func_get_args();
		$this->apply_filters($filter,'');
	}
}


/*** GLOBAL FUNCTIONS ****/
function add_filter($filter,$function,$priority = 10,$accepted_arguments=1){
	global $hooks;
	$args = func_get_args();
	return call_user_func_array(array($hooks,"add_filter"),$args);
}
function apply_filters($filter,$value){
	global $hooks;
	$args = func_get_args();
	return call_user_func_array(array($hooks,"apply_filters"),$args);
}
function add_listener($filter,$function,$priority = 10,$accepted_arguments=1){
	global $hooks;
	$args = func_get_args();
	return call_user_func_array(array($hooks,"add_listener"),$args);
}
function trigger($filter){
	global $hooks;
	$args = func_get_args();
	return call_user_func_array(array($hooks,"trigger"),$args);
}

