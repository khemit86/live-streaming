<?php
/**
 * RecordingStream
 *
 * PHP version 5
 *
 * @category Model 
 * 
 */
// App::uses('AuthComponent', 'Controller/Component');
// App::uses('SessionComponent', 'Controller/Component');
App::uses('AppModel', 'Model');
class RecordingStream extends AppModel{

	//public $primaryKey = '_id';
	
	/**
	 * Model name
	 * @var string
	 * @access public
	 */
	var $name = 'RecordingStream';
	/**
	 * Behaviors used by the Model
	 *
	 * @var array
	 * @access public
	 */
   
	var $actsAs = array(
       'Multivalidatable'
    );
	var $validationSets = array(
			'edit' => array(
				'title' => array(
					'notEmpty' => array(
						'rule' => 'notEmpty',
						'message' => 'Please enter title'
					)
				),'description' => array(
					'notEmpty' => array(
						'rule' => 'notEmpty',
						'message' => 'Please enter discription'
					)
				)
			)
	);
		            
	function toggleStatus($id = null)
	{
		$this->id = $id;
		$status = $this->field('status');
		$status = $status?0:1;
		return $this->saveField('status',$status);
	}
}