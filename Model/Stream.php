<?php
/**
 * Country
 *
 * PHP version 5
 *
 * @stream Model 
 * 
 */
// App::uses('AuthComponent', 'Controller/Component');
// App::uses('SessionComponent', 'Controller/Component');
App::uses('AppModel', 'Model');
class Stream extends AppModel{

	//public $primaryKey = '_id';
	
	/**
	 * Model name
	 * @var string
	 * @access public
	 */
	var $name = 'Stream';
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
		'front_add' => array(
			'title' => array(
                'notEmpty' => array(
                    'rule' => 'notEmpty',
                    'message' => 'Please enter title'
                )
            ),'subject' => array(
                'notEmpty' => array(
                    'rule' => 'notEmpty',
                    'message' => 'Please enter subject'
                )
            ),'stream_bio' => array(
                'notEmpty' => array(
                    'rule' => 'notEmpty',
                    'message' => 'Please enter biography'
                )
            ),'note' => array(
                'notEmpty' => array(
                    'rule' => 'notEmpty',
                    'message' => 'Please enter note'
                )
            ),'time_zone' => array(
                'notEmpty' => array(
                    'rule' => 'notEmpty',
                    'message' => 'Please select timezone'
                )
            ),'schedule_start_date' => array(
                'notEmpty' => array(
                    'rule' => 'notEmpty',
                    'message' => 'Please enter date'
                )
            ),'schedule_start_time' => array(
                'notEmpty' => array(
                    'rule' => 'notEmpty',
                    'message' => 'Please enter time'
                )
            ),'stream_encoder_type' => array(
                'notEmpty' => array(
                    'rule' => 'notEmpty',
                    'message' => 'Please select.'
                )
            ),'stream_broadcast_location' => array(
                'notEmpty' => array(
                    'rule' => 'notEmpty',
                    'message' => 'Please select.'
                )
            ),'aspect_ratio' => array(
                'notEmpty' => array(
                    'rule' => 'notEmpty',
                    'message' => 'Please select.'
                )
            ),'image' => array(
				'check1' => array(
                    'rule' => array('imageRequire'),
                    'message' => 'Image is require.'
                ),
				'check2' => array(
                    'rule' => array('checkextension'),
                    'message' => 'Please upload only image files'
                ),
                'check3' => array(
                    'rule' => array('fileSize1'),
                    'message' => 'Image size should be 480x270 or greater'
                ),
            )
		),'front_edit' => array(
			'title' => array(
                'notEmpty' => array(
                    'rule' => 'notEmpty',
                    'message' => 'Please enter title'
                )
            ),'subject' => array(
                'notEmpty' => array(
                    'rule' => 'notEmpty',
                    'message' => 'Please enter subject'
                )
            ),'stream_bio' => array(
                'notEmpty' => array(
                    'rule' => 'notEmpty',
                    'message' => 'Please enter biography'
                )
            ),'note' => array(
                'notEmpty' => array(
                    'rule' => 'notEmpty',
                    'message' => 'Please enter note'
                )
            ),'time_zone' => array(
                'notEmpty' => array(
                    'rule' => 'notEmpty',
                    'message' => 'Please select timezone'
                )
            ),'date' => array(
                'notEmpty' => array(
                    'rule' => 'notEmpty',
                    'message' => 'Please enter date'
                )
            ),'time' => array(
                'notEmpty' => array(
                    'rule' => 'notEmpty',
                    'message' => 'Please enter time'
                )
            ),'image' => array(				
				'check2' => array(
                    'rule' => array('checkextension'),
                    'message' => 'Please upload only image files'
                ),
                'check3' => array(
                    'rule' => array('fileSize1'),
                    'message' => 'Image size should be 480x270 or greater'
                ),
            )
		)
	);
	
	
	function imageRequire() 
	{
		$flag = false;
		if($this->data['Stream']['image']['name'])
		{
			$flag = true;
		}
		
		if(!$flag)
		{
			return false;
		}
		else
		{
			return true;
		}	
	}
	
	
	function checkextension() 
	{
		if (isset($this->data['Stream']['image']) && !empty($this->data['Stream']['image'])) 
		{			
			$files = $this->data['Stream']['image'];
	
				if (!empty($files) && $files['tmp_name'] != '' && $files['size'] > 0) 
				{
					$allowed = array('jpg', 'jpeg', 'gif', 'png', 'JPG', 'JPEG', 'GIF', 'PNG');
					$path_info = pathinfo($files['name']);
					if(!in_array($path_info['extension'],$allowed))
					{
						return false;
					}
					else
					{
						return true;
					}
				}					
		}
		return true;
    }
	
	function fileSize1 ($field){
		
		list($width, $height) = @getimagesize($field['image']['tmp_name']);
		//pr($this->data['Stream']['image']['name']);die;
		if(!empty($this->data['Stream']['image']['name'])){
			if ($height <= 270 || $width <= 480) {		
			   return false;
			} else {
				return true;
			}
		}else{
			return true;
		}
		
	}
	
	function toggleFeaturStatus($id = null)
	{	
		$this->id = $id;
		$featured = $this->field('featured');
		$featured = $featured?0:1;
		return $this->saveField('featured',$featured);
	}	
}