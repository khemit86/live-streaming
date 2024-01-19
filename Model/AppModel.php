<?php
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Model', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {
	public $actsAs = array('Containable');
	
	//Validation message
	function invalidate($field, $value = true){
		parent::invalidate($field, $value);
		$this->validationErrors[$field] = __($value, true);
	}
	
	/* Validate with left and right white space*/
	function checkWhiteSpace($data = null, $field=null){
			if(substr($data[$field], -1, 1) == ' '){
					return false;				
			}   	
			if(substr($data[$field], 0, 1) == ' '){
				return false;    	 	
			}	
			return true;		
	}
	
	/* validate alpha numeric */
	function checkAlpha($data = null, $field=null){
		if(preg_match('/^[a-zA-Z ]+$/', $data[$field])){			
			return true;
		}
		else{
			return false;
		}
	}
	
	/* validate alpha numeric */
	function checkAlphaNumericDashUnderscore($data = null, $field=null){
		
		if(preg_match('/^[a-zA-Z0-9_ -]+$/', $data[$field])){			
			return true;
		}
		else{
			return false;
		}
	}
	/* validate alpha numeric */
	function checkAlphaNumericDashUnderscoreExtra($data = null, $field=null){
		if(preg_match('/^[a-zA-Z0-9._ -]+$/', $data[$field])){			
			return true;
		}
		else{
			return false;
		}
	}
	/* validate alpha numeric comma fullstop*/
	function checkAlphaNumericDashUnderscoreCommaFullstop($data = null, $field=null){
		if(preg_match('/^[a-zA-Z0-9.,_ -]+$/', $data[$field])){			
			return true;
		}
		else{
			return false;
		}
	}
	//check numeric
	function checkNumeric($data = null, $field = null){
	  if(preg_match('/^[0-9][0-9]*$/', trim($data[$field]))){
			return true;
		}
		else{
			return false;
		}
	}
        
        function unbindModelAll($model = null){
		$unbind = array();
		foreach ($this->belongsTo as $model=>$info)
		{
		  $unbind['belongsTo'][] = $model;
		}
		foreach ($this->hasOne as $model=>$info)
		{
		  $unbind['hasOne'][] = $model;
		}
		foreach ($this->hasMany as $model=>$info)
		{
		  $unbind['hasMany'][] = $model;
		}
		foreach ($this->hasAndBelongsToMany as $model=>$info)
		{
		  $unbind['hasAndBelongsToMany'][] = $model;
		}
		parent::unbindModel($unbind);
	}
	
		function toggleStatus($id = null)
	{
		$this->id = $id;
		$status = $this->field('status');
		$status = $status?0:1;
		return $this->saveField('status',$status);
	}
	

	


}
