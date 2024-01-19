<?php
/**
 * Country
 *
 * PHP version 5
 *
 * @plan Model 
 * 
 */
// App::uses('AuthComponent', 'Controller/Component');
// App::uses('SessionComponent', 'Controller/Component');
App::uses('AppModel', 'Model');
class Plan extends AppModel{

	//public $primaryKey = '_id';
	
	/**
	 * Model name
	 * @var string
	 * @access public
	 */
	var $name = 'Plan';
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
		'add'=>	array(
			'name'=>array(
				'R1'=>array(
					'rule'=>'notEmpty',
					'message' => 'Plan is required.'
				)
			),
			'price' => array(
				'notEmpty' => array(
					'rule' => array('notEmpty'),
					'message' => 'Price is required.'
				),
				'checkIntegerOrFloat'=>array(
				'rule'	=> 	array('checkIntegerOrFloat', 'price'),
				'message' =>'Enter valid price.'
				)
			)	
		),
		'edit'=>	array(
			'name'=>array(
				'R1'=>array(
					'rule'=>'notEmpty',
					'message' => 'Plan is required.'
				)
			),
			'price' => array(
				'notEmpty' => array(
					'rule' => array('notEmpty'),
					'message' => 'Price is required.'
				),
				'checkIntegerOrFloat'=>array(
				'rule'	=> 	array('checkIntegerOrFloat', 'price'),
				'message' =>'Enter valid price.'
				)
			)			
		)
	);
	
	
	public function checkIntegerOrFloat($data = null, $field=null){		
		if(preg_match('/^[0-9.]+$/', $data[$field])){			
			return true;
		}
		else{
			return false;
		}		
	}
	function getPlanList()
	{		
		//$data	=	$this->find('list',array('conditions' => array('Plan.status'=>1),'fields'=>array('id','name'),'order'=>array('Plan.name'=>'asc')));
		
		
		$data	=	$this->find('all',array('conditions' => array('Plan.status'=>1),'fields'=>array('id','name','price','month','year'),'order'=>array('Plan.name'=>'asc')));
		
		$dataArray = array();
		foreach($data as $key => $val){
	
			$dataArray[$val['Plan']['id']] = $val['Plan']['name'].' ( Price-'.$val['Plan']['price'].')'.' ( Year-'.$val['Plan']['year'].')'.' ( Month-'.$val['Plan']['month'].')';
		}
		
		//pr($dataArray);die;
		
		
		
		return $dataArray;
	}
}