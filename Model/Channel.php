<?php
/**
 * Country
 *
 * PHP version 5
 *
 * @category Model 
 * 
 */
// App::uses('AuthComponent', 'Controller/Component');
// App::uses('SessionComponent', 'Controller/Component');
App::uses('AppModel', 'Model');
class Channel extends AppModel{

	//public $primaryKey = '_id';
	
	/**
	 * Model name
	 * @var string
	 * @access public
	 */
	var $name = 'Channel';
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
			'channel_add' => array(
				'name' => array(
					'notEmpty' => array(
						'rule' => 'notEmpty',
						'message' => 'Enter channel name'
					)
				),'company' => array(
					'notEmpty' => array(
						'rule' => 'notEmpty',
						'message' => 'Enter company name'
					)
				),'website' => array(
					array(
						'required' => true,
						'allowEmpty' => false,
						'rule' => array('url', true),
						'message' => 'Enter a valid URL.',
						'last' => true
						)
				),'bio' => array(
					'notEmpty' => array(
						'rule' => 'notEmpty',
						'message' => 'Enter biography'
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