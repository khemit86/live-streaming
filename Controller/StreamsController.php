<?php
/**
 * user content controller.
 *
 * This file will render views from views/pages/
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
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('AppController', 'Controller');

/**
 * user content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */

App::uses('Sanitize', 'Utility');
class StreamsController extends AppController {



/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Streams';

/**
 * This controller use a model admins
 *
 * @var array
 */
	public $uses 		= array('Stream');
	
	public $helpers 	= array('Html', 'Session','General','Csv');
	var $components = 	array('General',"Upload");
	
	
	public function beforeFilter() {
	
        parent::beforeFilter();
        $this->loadModel('Plan');
		$this->Auth->allow('stream_detail','add_sandbox_stream','add_stream','recorded_stream_detail','follow_ajax','get_all_recordings');
    }

	
	
	/*
	@ param : null
	@ return void
	*/
	
	public function admin_live() {
		/* pr( $this->Session);
		die; */
		
		$this->loadModel('User');
		$grid_list_type = "";
		
		if (!isset($this->params['named']['page'])) {
            $this->Session->delete('AdminSearch');
        }
		
		$filters	=	array();
        if (!empty($this->request->data)) 
		{
			
			$grid_list_type = $this->request->data['Stream']['grid_list_type'];
			
			$this->Session->delete('AdminSearch');
           if (isset($this->request->data['Stream']['title']) && $this->request->data['Stream']['title'] != '') {
                $title = trim($this->request->data['Stream']['title']);
                $this->Session->write('AdminSearch.title', $title);
				
            }
			if (isset($this->request->data['Stream']['user_id']) && $this->request->data['Stream']['user_id'] != '') {
                $user_id = trim($this->request->data['Stream']['user_id']);
                $this->Session->write('AdminSearch.user_id', $user_id);
				
            }
		}
		
		if ($this->Session->check('AdminSearch')) 
		{
            $keywords 	= 	$this->Session->read('AdminSearch');
			foreach($keywords as $key=>$values){
				if($key == 'title')
				{
					$filters[] = array('Stream.'.$key.' LIKE'=>"%".$values."%");					
				}
				if($key == 'user_id')
				{
					$filters[] = array('Stream.'.$key=>$values);					
				}
			}
			
		}
		
		$this->Stream->bindModel(array('belongsTo'=>array('User')));
		/* pr($this->params);
		die; */
		$this->paginate = array('Stream' => array(
			'limit' =>Configure::read('App.PageLimit'),
			'order' => array('Stream.id' => 'DESC'),
			'conditions' => $filters,
        ));	
				
		$user_list = $this->User->find('list',array('fields'=>array('User.id','User.nickname'),'conditions'=>array('User.role_id'=>USER_ROLE)));
		$data = $this->paginate('Stream');		
		/* pr($data );
		die; */
		$this->set(compact('data','user_list','grid_list_type'));
		$this->set('title_for_layout', __('Live Streaming', true));
		
	}
	
	
	/*
	@ param : null
	@ return void
	*/
	public function admin_live_view($id = null){
	
		$this->set('title_for_layout','Streaming Detail');
		
		$this->Stream->id = $id;
		$this->set(compact('id'));
		
		/*check conditions allreday conditions for users update*/
        if (!$this->Stream->exists()) {
            throw new NotFoundException(__('Invalid Stream'));
        }
		
		$this->Stream->bindModel(array('belongsTo'=>array('User')));
		$data = $this->Stream->read(null, $id);
		$this->set(compact('data'));
	}
	public function admin_featured($id = null) {
	$this->autoRender = false;
	$get_featured_detail = $this->Stream->find('first',array('conditions'=>array('Stream.id'=>$id),'fields'=>array('Stream.id','Stream.featured')));
	if($get_featured_detail['Stream']['featured'] == 1)
	{
		$this->Stream->id = $get_featured_detail['Stream']['id'];
		$this->Stream->saveField('featured',0);
		die("0");
	}
	else
	{
		$this->Stream->id = $get_featured_detail['Stream']['id'];
		$this->Stream->saveField('featured',1);
		die("1");
	}
	
	
	
	
	// $this->layout = false;
		echo $id;die;
        /* if ($this->Stream->toggleFeaturStatus($id)) { 
            $this->Session->setFlash(__('Admin\'s status has been changed'), 'admin_flash_good');
		    $this->redirect($this->referer());
        }
        $this->Session->setFlash(__('Admin\'s status was not changed', 'admin_flash_error'));
        $this->redirect($this->referer()); */
    }
	
	
	
	public function channel_listing()
	{
		$this->layout = "front";
		$this->set('title_for_layout','CHANNELS');
		$this->loadModel('Channel');
		$channel_listing = $this->Channel->find('all',array('conditions'=>array('Channel.status'=>Configure::read('App.Status.active'))));
		$this->set('channel_listing',$channel_listing);
		/* pr($channel_listing);
		die; */
		
	}
	
	
	
	
	public function channel_detail($id = null)
	{
		$this->layout = "lay_channel_detail";
		$this->set('title_for_layout','Channel Detail');
		
		$this->loadModel('Channel');
		$this->loadModel('RecordingStreams');
		$this->Channel->bindModel(array(
			'belongsTo'=>array(
				'User'=>array(
					'className'=>'User',
					'foreignKey'=>'user_id',
					'fields'=>array('id','first_name','profile_image')	
				)
			)			
		),false);
		$this->Channel->bindModel(array(
			'hasMany'=>array(
				'RecordingStreams'=>array(
					'className'=>'RecordingStreams',
					'foreignKey'=>'channel_id',
					'fields'=>array('id','title')	
				)
			)			
		),false);
		
		$channelData = $this->Channel->find('first',array('conditions'=>array('Channel.id'=>$id)));
		$this->set('channelData',$channelData);
		
	}
	
	
	
	
	
	public function stream_detail($id)
	{
		$this->layout = 'lay_stream_detail';
		$this->set('title_for_layout','Stream Detail');
		$this->loadmodel("ChannelSubscription");
		$this->Stream->bindModel(array(
                'hasOne' => array(
                    'ChannelFollower' => array(
                        'className' => 'ChannelFollower',
                        'foreignKey' => 'stream_id',
                        'fields' => array('is_follow'), 
						'conditions'=>array('ChannelFollower.user_id'=>$this->Auth->user('id'),'ChannelFollower.recording_stream_id'=>'0'),
                    ),
                ),
				'belongsTo'=>array(
					'Channel'
				)	
			), false
		);
		
		$stream_detail = $this->Stream->find('first',array('conditions'=>array('Stream.id'=>$id)));
		$channel_subscribe_check_user = $this->ChannelSubscription->find('count',array('conditions'=>array('ChannelSubscription.user_id'=>$this->Auth->user('id'),'ChannelSubscription.channel_id'=>$stream_detail['Channel']['id'],'ChannelSubscription.stream_id'=>$stream_detail['Stream']['id'])));
			
		
		$this->set('stream_detail',$stream_detail);
		$this->set('channel_subscribe_check_user',$channel_subscribe_check_user);
		
	}
	
	
	public function follow_ajax() {
		if (!empty($this->request->data)) {
			$this->loadModel('Channel');				
			$this->loadModel('ChannelFollower');				
			
			if(!empty($this->request->data['recording_stream_id'])){
				$lkD = $this->ChannelFollower->find('first', array('conditions' => array('ChannelFollower.user_id' => $this->request->data['user_id'], 'ChannelFollower.recording_stream_id' => $this->request->data['recording_stream_id'])));
				if (empty($lkD)) {
					$followData = array('user_id' => $this->request->data['user_id'],
						'stream_id' => $this->request->data['stream_id'],
						'recording_stream_id' => $this->request->data['recording_stream_id'],
						'channel_id' => $this->request->data['channel_id'],
						'is_follow' => $this->request->data['status']
					);
				} else {
					$followData = array('user_id' => $this->request->data['user_id'],
						'stream_id' => $this->request->data['stream_id'],
						'recording_stream_id' => $this->request->data['recording_stream_id'],
						'channel_id' => $this->request->data['channel_id'],
						'is_follow' => $this->request->data['status'],
						'id' => $lkD['ChannelFollower']['id']
					);
				}
			
			}else{
			
				$lkD = $this->ChannelFollower->find('first', array('conditions' => array('ChannelFollower.user_id' => $this->request->data['user_id'], 'ChannelFollower.stream_id' => $this->request->data['stream_id'], 'ChannelFollower.recording_stream_id' =>'0')));
				
				if (empty($lkD)) {
					$followData = array('user_id' => $this->request->data['user_id'],
						'stream_id' => $this->request->data['stream_id'],						
						'channel_id' => $this->request->data['channel_id'],
						'is_follow' => $this->request->data['status']
					);
				} else {
					$followData = array('user_id' => $this->request->data['user_id'],
						'stream_id' => $this->request->data['stream_id'],						
						'channel_id' => $this->request->data['channel_id'],
						'is_follow' => $this->request->data['status'],
						'id' => $lkD['ChannelFollower']['id']
					);
				}
				
			}
			
			
			if ($this->ChannelFollower->saveAll($followData)) {
				
				$channelData	=	$this->Channel->find('first',array('fields'=>array('id','follower_count'),'conditions'=>array('Channel.id'=>$this->request->data['channel_id'])));
				
				if(!empty($channelData)){
					$this->Channel->id	=	$channelData['Channel']['id'];				
					
					
					if(isset($this->request->data['status']) && $this->request->data['status'] == 0 && $channelData['Channel']['follower_count'] > 0) {
						$this->Channel->saveField('follower_count',$channelData['Channel']['follower_count']-1,false);
					} else {
						$this->Channel->saveField('follower_count',$channelData['Channel']['follower_count']+1,false);					
					}
				}				
				
				$response = array(
					'message' => 'success',
					'success' => true,
					'msg' => 'You have follow this post successfully.'								
				);
				
			} else {
				$response = array(
					'message' => 'Failed',
					'success' => false,
					'msg' => 'Error! try again'
				);
			}
		} else {
			$response = array(
				'message' => 'Failed',
				'msg' => 'Error! try again'
			);
		}
	    $response = array('response' => $response);
	    die(json_encode($response));
	}
	
	
	public function add(){
		$this->layout = 'lay_dashboard';
		$this->set('title_for_layout','Add Stream');
		$time_zones = parent::tz_list();
		$this->set('time_zones',$time_zones);
		
		$broadcast_location = array_merge(Configure::read('Stream.Broadcast.Location'), Configure::read('4K.Broadcast.Location'));
		$this->set('broadcast_location',$broadcast_location);
		$aspect_ration_options =  array();
		$this->set('aspect_ration_options',$aspect_ration_options);
		$message  = '';
		$this->set('message',$message);
		
		
		if(!empty($this->request->data)){
			
			if (!isset($this->request->params['_Token']['key']) || ($this->request->params['_Token']['key'] != $this->request->params['_Token']['key'])) {
				$blackHoleCallback = $this->Security->blackHoleCallback;
				$this->$blackHoleCallback();
            }
			
			$this->Stream->set($this->request->data['Stream']);
			$this->Stream->setValidation('front_add');
			
			
			if ($this->Stream->validates()) {
			
				$aspect_ratio_array = explode('x',$this->request->data['Stream']['aspect_ratio']); 
				$add_stream_request = '{
				  "live_stream": {
					"name": "'.$this->request->data['Stream']['title'].'",
					"transcoder_type": "transcoded",
					"billing_mode": "pay_as_you_go",
					"broadcast_location": "'.$this->request->data['Stream']['stream_broadcast_location'].'",
					"recording":"true",
					"encoder": "'.$this->request->data['Stream']['stream_encoder_type'].'",
					"delivery_method": "push",
					"disable_authentication":"true",
					"aspect_ratio_width": '.$aspect_ratio_array[0].',
					"aspect_ratio_height": '.$aspect_ratio_array[1].',
					"player_responsive": "true",
					"player_countdown": "false",
					"hosted_page": "true",
					"hosted_page_sharing_icons": "true",
					"player_width":500
					}
				}';
				
				$ch = curl_init('https://api.cloud.wowza.com/api/v1/live_streams/');
				//$ch = curl_init('https://api-sandbox.cloud.wowza.com/api/v1/live_streams/');
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                             curl_setopt($ch, CURLOPT_POSTFIELDS, $add_stream_request);   
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                           		'Content-Type: application/json ;charset=utf-8', 
					'wsc-api-key:'.Configure::read('WOWZA_API_KEY'),
					'wsc-access-key:'.Configure::read('WOWZA_ACCESS_KEY')
					)                                                                       
				); // live
				
				/* curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
				'Content-Type: application/json ;charset=utf-8', 
				'wsc-api-key:9lJxxxxxQQ5hv7ze2vr70hXNQTcoikFqXJ5nTcz5Q6Hn834j5MRH5G7YGjRcepIQ13146',
				'wsc-access-key:bKJgrvQ68b2ze51ARiQqcvsv8BQ9qp7hqYaqvcUim2JvNzJr4rl9yK0hvTcJ3562'
				)                                                                       
				);  *///sandbox
				
				
				
				$result = curl_exec($ch);
				curl_close($ch);
				$response_array = json_decode($result,true);
				/* echo $result;
				pr($response_array);
				die; */
				if(!isset($response_array['error']) && !isset($response_array['meta']))
				{
					$this->loadModel('Channel');
					$user_id = $this->Session->read('Auth.User.id');	
					$channel_detail = $this->Channel->find('first',array('conditions'=>array('Channel.user_id'=>$user_id),'fields'=>array('Channel.id','Channel.user_id')));
					$this->request->data['Stream']['stream_request'] = $add_stream_request;
					$this->request->data['Stream']['stream_response'] = $result;
					$this->request->data['Stream']['stream_key'] = $response_array['live_stream']['id'];
					$this->request->data['Stream']['player_id'] = $response_array['live_stream']['player_id'];
					if(isset($response_array['live_stream']['connection_code']))
					{
						$this->request->data['Stream']['connection_code'] = $response_array['live_stream']['connection_code'];
					}
					/* $this->request->data['Stream']['connection_code_expires_at'] = date('Y-m-d H:i:s',strtotime($response_array['live_stream']['connection_code_expires_at'])); */
					$this->request->data['Stream']['user_id'] = $user_id;
					if(isset($channel_detail) &&  !empty($channel_detail['Channel']['id']))
					{
						$this->request->data['Stream']['channel_id'] = $channel_detail['Channel']['id'];
					}
					$this->request->data['Stream']['stream_name'] = $response_array['live_stream']['source_connection_information']['stream_name'];
					$this->request->data['Stream']['primary_server'] = $response_array['live_stream']['source_connection_information']['primary_server'];
					$this->Stream->save($this->request->data);
					
					$stream_id	=	$this->Stream->id;
					$p_image	=	$this->request->data['Stream']['image'];
					
					if (!empty($p_image) && $p_image['tmp_name'] != '' && $p_image['size'] > 0) {
			
						
						$allowed	=	array('jpg','jpeg','png');
						$temp 		= 	explode(".", $p_image["name"]);
						$extension 	= 	end($temp);
						$imageName 	= 	'stream_image_'.microtime(true).'.'.$extension;
						$files		=	$p_image;
						
						$result 	= 	$this->Upload->upload($files, WWW_ROOT . STREAM_IMAGE_FULL_DIR . DS, $imageName,'',$allowed);
						
						if(!empty($this->Upload->result) && empty($this->Upload->errors)) {
							$this->Stream->id	=	$stream_id;
							$this->Stream->saveField('stream_image',$imageName,false);
						}					
					}
					
					
					$this->Session->setFlash("Streams add successfully.", 'flash_good');
					$this->redirect(array('controller'=>'streams','action'=>'index'));	
					
				}
				else
				{
					$this->Session->setFlash($response_array['error'], 'admin_flash_bad');
					
				}	
		
			} else {
				
				
				if(!empty($this->request->data['Stream']['stream_broadcast_location']))
				{
				
				
					if (array_key_exists($this->request->data['Stream']['stream_broadcast_location'], Configure::read('Stream.Broadcast.Location')))
					{
						$aspect_ration_options =  array('1920x1080'=>'1920 x 1080(1080p)','1280x720'=>'1280 x 720(720p)','1024x576'=>'1024 x 576');
						$this->set('aspect_ration_options',$aspect_ration_options);
						
					}
					else if (array_key_exists($this->request->data['Stream']['stream_broadcast_location'], Configure::read('4K.Broadcast.Location')))
					{
						$aspect_ration_options =  array('3840x2160'=>'3840 x 2160','1920x1080'=>'1920 x 1080(1080p)','1280x720'=>'1280 x 720(720p)','1024x576'=>'1024 x 576');
						$this->set('aspect_ration_options',$aspect_ration_options);
					}
					
					if($this->request->data['Stream']['aspect_ratio']=='3840x2160')
					{
						$message = 'This setting creates <strong>7 bitrate renditions.</strong>';
					}
					else if($this->request->data['Stream']['aspect_ratio']=='1920x1080')
					{
						$message = 'This setting creates <strong>6 bitrate renditions.</strong>';
					}
					else if($this->request->data['Stream']['aspect_ratio']=='1280x720')
					{
						$message = 'This setting creates <strong>5 bitrate renditions.</strong>';
					}
					else if($this->request->data['Stream']['aspect_ratio']=='1024x576')
					{
						$message = 'This setting creates <strong>5 bitrate renditions.</strong>';
					}
					$this->set('message',$message);
					
				}
				
				$this->Session->setFlash("Record has not been created", 'admin_flash_bad');
			}
		
		}
	
	
	}
	
	
	
	public function edit($id){
		
		//$this->set('id',$id);
		$this->layout = 'lay_dashboard';
		$this->set('title_for_layout','Edit Stream');
		$stream_data = $this->Stream->find('first',array('conditions'=>array('Stream.id'=>$id),'fields'=>array('Stream.title')));
		$this->set('stream_data',$stream_data);
		if(!empty($this->request->data)){
			
			if (!isset($this->request->params['_Token']['key']) || ($this->request->params['_Token']['key'] != $this->request->params['_Token']['key'])) {
				$blackHoleCallback = $this->Security->blackHoleCallback;
				$this->$blackHoleCallback();
            }
			
			$this->Stream->set($this->request->data['Stream']);
			$this->Stream->setValidation('front_edit');
			
			
			if ($this->Stream->validates()) {
			
				$stream_data =  $this->Stream->find('first',array('conditions'=>array('Stream.id'=>$id),'fields'=>array('Stream.stream_key')));
				$edit_stream_request = '{"live_stream": {"name": "'.$this->request->data['Stream']['title'].'"}}';
				
				/* echo 'https://api.cloud.wowza.com/api/v1/live_streams/'.$stream_data['Stream']['stream_key'].'/';
				die;
				 */
				$ch = curl_init('https://api.cloud.wowza.com/api/v1/live_streams/'.$stream_data['Stream']['stream_key'].'/');
				//$ch = curl_init('https://api-sandbox.cloud.wowza.com/api/v1/live_streams/');
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");  
				curl_setopt($ch, CURLOPT_POSTFIELDS, $edit_stream_request);   
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(   
					'Content-Type: application/json ;charset=utf-8', 
					'wsc-api-key:'.Configure::read('WOWZA_API_KEY'),
					'wsc-access-key:'.Configure::read('WOWZA_ACCESS_KEY')
					)                                                                       
				); // live
				
				/* curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
				'Content-Type: application/json ;charset=utf-8', 
				'wsc-api-key:9lJxxxxxQQ5hv7ze2vr70hXNQTcoikFqXJ5nTcz5Q6Hn834j5MRH5G7YGjRcepIQ13146',
				'wsc-access-key:bKJgrvQ68b2ze51ARiQqcvsv8BQ9qp7hqYaqvcUim2JvNzJr4rl9yK0hvTcJ3562'
				)                                                                       
				);  *///sandbox
				
				
				
				$result = curl_exec($ch);
				curl_close($ch);
				$response_array = json_decode($result,true);
				
				
				if(!isset($response_array['error']) && !isset($response_array['meta']))
				{
					
					$this->loadModel('Channel');
					$user_id = $this->Session->read('Auth.User.id');	
					$channel_detail = $this->Channel->find('first',array('conditions'=>array('Channel.user_id'=>$user_id),'fields'=>array('Channel.id','Channel.user_id')));
					
					$this->request->data['Stream']['stream_request'] = $edit_stream_request;
					$this->request->data['Stream']['stream_response'] = $result;
					$this->request->data['Stream']['stream_key'] = $response_array['live_stream']['id'];
					$this->request->data['Stream']['player_id'] = $response_array['live_stream']['player_id'];
					
					if(isset($response_array['live_stream']['connection_code']))
					{
						$this->request->data['Stream']['connection_code'] = $response_array['live_stream']['connection_code'];
					}
					
					/* $this->request->data['Stream']['connection_code_expires_at'] = date('Y-m-d H:i:s',strtotime($response_array['live_stream']['connection_code_expires_at'])); */
					
					
					//$this->request->data['Stream']['user_id'] = $user_id; 
					 
					/* $this->request->data['Stream']['channel_id'] = $channel_detail['Channel']['id']; */
					$this->request->data['Stream']['stream_name'] = $response_array['live_stream']['source_connection_information']['stream_name'];
					$this->request->data['Stream']['primary_server'] = $response_array['live_stream']['source_connection_information']['primary_server'];
					
					
					
					$this->Stream->id = $id;
					$this->Stream->save($this->request->data);
					
					
					$stream_id	=	$id;
					
					$p_image	=	$this->request->data['Stream']['image'];
					$old_image	=	$this->request->data['Stream']['stream_image'];
					
				
					if (!empty($p_image) && $p_image['tmp_name'] != '' && $p_image['size'] > 0) {
			
						$allowed	=	array('jpg','jpeg','png');
						$temp 		= 	explode(".", $p_image["name"]);
						$extension 	= 	end($temp);
						$imageName 	= 	'stream_image_'.microtime(true).'.'.$extension;
						$files		=	$p_image;
						
						$result 	= 	$this->Upload->upload($files, WWW_ROOT . STREAM_IMAGE_FULL_DIR . DS, $imageName,'',$allowed);
						
						if(!empty($this->Upload->result) && empty($this->Upload->errors)) {
							
							if($old_image &&  file_exists(WWW_ROOT.STREAM_IMAGE_FULL_DIR.DS.$old_image )) {
								@unlink(WWW_ROOT.STREAM_IMAGE_FULL_DIR.DS.$old_image);
							}
							
							$this->Stream->id	=	$stream_id;
							$this->Stream->saveField('stream_image',$imageName,false);
						}
						
					}
					$this->Session->setFlash("Streams updated successfully.", 'flash_good');
					$this->redirect(array('controller'=>'streams','action'=>'index'));	
					
				}
				else
				{
					$this->Session->setFlash($response_array['error'], 'admin_flash_bad');
					
				}
			} else {
				
				$this->Session->setFlash("Record has not been updated", 'admin_flash_bad');
			}
		}
		else
		{
			$this->request->data = $this->Stream->find('first',array('conditions'=>array('Stream.id'=>$id)));
		}
	}
	
	
	
	public function index(){
		if (!isset($this->params['named']['page'])) {
            $this->Session->delete('FrontSearch');
        }
		
		$filters	=	array();
        if (!empty($this->request->data)) {
			$this->Session->delete('AdminSearch');
			if (isset($this->request->data['Stream']['title']) && $this->request->data['Stream']['title'] != '') {
				$title = trim($this->request->data['Stream']['title']);
				$this->Session->write('FrontSearch.title', $title);
				
			}
		}
		
		if ($this->Session->check('FrontSearch')) {
            $keywords 	= 	$this->Session->read('FrontSearch');
			foreach($keywords as $key=>$values){
				if($key == 'title'){
					$filters[] = array('Stream.'.$key.' LIKE'=>"%".$values."%");					
				}
				$this->admin_exportcsv($this->request->data);
			}
		}
		$user_id = $this->Session->read('Auth.User.id'); 
		$filters[] =  array('Stream.user_id'=>$user_id);
		$this->paginate = array('Stream' => array(
			'limit' =>Configure::read('App.PageLimit'),
			'order' => array('Stream.id' => 'DESC'),
			'conditions' => $filters,
        ));
		$data = $this->paginate('Stream');	
			
		$this->set(compact('data'));
		$this->set('title_for_layout', __('Stream Listing', true));

		$this->layout = 'lay_dashboard';
		
	
	
	}
	
	/* public function detail($id){
	
		
		$this->layout = 'dashboard';
		$stream_detail = $this->Stream->find('first',array('conditions'=>array('Stream.id'=>$id),'fields'=>array('Stream.player_id')));
		$this->set('stream_detail',$stream_detail);
		
		$ch = curl_init('https://api.cloud.wowza.com/api/v1/players/'.$stream_detail['Stream']['player_id'].'/');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(   
		'Content-Type: application/json ;charset=utf-8', 
			'wsc-api-key:'.Configure::read('WOWZA_API_KEY'),
			'wsc-access-key:'.Configure::read('WOWZA_ACCESS_KEY')
			)                                                                       
		); 
		$result = curl_exec($ch);
		curl_close($ch);
		$response_array = json_decode($result,true);
		//if(isset($response_array['meta']['status']) && $response_array['meta']['status'] == '404')
		if(isset($response_array['error']) && isset($response_array['meta']))
		{
			$response_array = array();
		}
		
		$this->set('response_array',$response_array);
		
		
	} */
	
	
	
	public function add_sandbox_stream()
	{
		$this->layout = "lay_stream_detail";
		$this->set('title_for_layout','Add Stream');
		$player_logo = SITE_URL.'img/Front/logo.png';
		$add_stream_json_string = '{
		  "live_stream": {
			"name": "My Live Stream",
			"transcoder_type": "transcoded",
			"billing_mode": "pay_as_you_go",
			"broadcast_location": "eu_germany",
			"recording": true,
			"closed_caption_type": "none",
			"encoder": "wowza_gocoder",
			"delivery_method": "push",
			"delivery_type": "single-bitrate",
			"delivery_protocol": "hls-hds",
			"use_stream_source": false,
			"aspect_ratio_width": 1920,
			"aspect_ratio_height": 1080,
			"player_logo_image":"'.$player_logo.'",
			"remove_player_logo_image": "false",
			"player_logo_position": "top-right"
		  }
		}';
		
		$ch = curl_init('https://api-sandbox.cloud.wowza.com/api/v1/live_streams/');                                                                      
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
		curl_setopt($ch, CURLOPT_POSTFIELDS, $add_stream_json_string);                                                                  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
			'Content-Type: application/json ;charset=utf-8', 
			'wsc-api-key:9lJxxxxxQQ5hv7ze2vr70hXNQTcoikFqXJ5nTcz5Q6Hn834j5MRH5G7YGjRcepIQ13146',
			'wsc-access-key:bKJgrvQ68b2ze51ARiQqcvsv8BQ9qp7hqYaqvcUim2JvNzJr4rl9yK0hvTcJ3562'
			)                                                                       
		);                                                                                                                   
																															 
		$result = curl_exec($ch);
		curl_close($ch);
		echo $result;
		die("hiiii");
		
		
		$response = '{
	"live_stream": {
		"id": "29g9s54w",
		"name": "My Live Stream",
		"transcoder_type": "transcoded",
		"billing_mode": "pay_as_you_go",
		"broadcast_location": "eu_germany",
		"recording": true,
		"closed_caption_type": "none",
		"encoder": "wowza_gocoder",
		"delivery_method": "push",
		"delivery_protocol": "hls-hds",
		"use_stream_source": false,
		"aspect_ratio_width": 1920,
		"aspect_ratio_height": 1080,
		"connection_code": "020531",
		"connection_code_expires_at": "2016-07-12T10:36:55.000Z",
		"source_connection_information": {
			"primary_server": "78a4e8.entrypoint.cloud.wowza.com",
			"host_port": 1935,
			"application": "app-9c70",
			"stream_name": "cceecfa9",
			"disable_authentication": false,
			"username": "client11469",
			"password": "edf107a4"
		},
		"video_fallback": false,
		"player_id": "ypgybrff",
		"player_responsive": false,
		"player_width": 640,
		"player_countdown": false,
		"player_embed_code": "in_progress",
		"player_hds_playback_url": "http://wowzaprodhd68-lh.akamaihd.net/z/20aeccce_1@42948/manifest.f4m",
		"player_hls_playback_url": "http://wowzaprodhd68-lh.akamaihd.net/i/20aeccce_1@42948/master.m3u8",
		"hosted_page": true,
		"hosted_page_title": "My Live Stream",
		"hosted_page_url": "in_progress",
		"hosted_page_sharing_icons": true,
		"stream_targets": [{
			"id": "4n9z7m1v"
		}],
		"created_at": "2016-07-11T10:36:55.000Z",
		"updated_at": "2016-07-11T10:36:56.000Z",
		"links": [{
			"rel": "self",
			"method": "GET",
			"href": "https://api.cloud.wowza.com/api/v1/live_streams/29g9s54w"
		}, {
			"rel": "update",
			"method": "PATCH",
			"href": "https://api.cloud.wowza.com/api/v1/live_streams/29g9s54w"
		}, {
			"rel": "state",
			"method": "GET",
			"href": "https://api.cloud.wowza.com/api/v1/live_streams/29g9s54w/state"
		}, {
			"rel": "thumbnail_url",
			"method": "GET",
			"href": "https://api.cloud.wowza.com/api/v1/live_streams/29g9s54w/thumbnail_url"
		}, {
			"rel": "start",
			"method": "PUT",
			"href": "https://api.cloud.wowza.com/api/v1/live_streams/29g9s54w/start"
		}, {
			"rel": "reset",
			"method": "PUT",
			"href": "https://api.cloud.wowza.com/api/v1/live_streams/29g9s54w/reset"
		}, {
			"rel": "stop",
			"method": "PUT",
			"href": "https://api.cloud.wowza.com/api/v1/live_streams/29g9s54w/stop"
		}, {
			"rel": "regenerate_connection_code",
			"method": "PUT",
			"href": "https://api.cloud.wowza.com/api/v1/live_streams/29g9s54w/regenerate_connection_code"
		}, {
			"rel": "delete",
			"method": "DELETE",
			"href": "https://api.cloud.wowza.com/api/v1/live_streams/29g9s54w"
		}]
	}
	}';
		die("hiiiiii");
		
	}
	
	
	
	public function start($id)
	{
		$this->layout = false;
		$stream_detail = $this->Stream->find('first',array('conditions'=>array('Stream.id'=>$id),'fields'=>array('Stream.id','Stream.stream_key')));
		$ch = curl_init('https://api.cloud.wowza.com/api/v1/live_streams/'.$stream_detail['Stream']['stream_key'].'/start/');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); 
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $add_stream_request);    
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                           		'Content-Type: application/json ;charset=utf-8', 
			'wsc-api-key:'.Configure::read('WOWZA_API_KEY'),
			'wsc-access-key:'.Configure::read('WOWZA_ACCESS_KEY')
			)                                                                       
		); 
		$result = curl_exec($ch);
		curl_close($ch);
		$response_array = json_decode($result,true);
		
		if(!isset($response_array['error']))
		{
			
			if($response_array['live_stream']['state'] == 'starting')
			{
				
				$this->request->data['Stream']['id'] = $stream_detail['Stream']['id'];
				$this->request->data['Stream']['state'] = 1;
				//$this->request->data['Stream']['connection_code'] = NULL;
				//$this->request->data['Stream']['connection_code_expires_at'] = NULL;
				$this->Stream->save($this->request->data);
				$this->Session->setFlash("Streams start successfully.This may take a few minutes. Thank you for your patience.", 'flash_good');	
				$this->redirect(array('controller'=>'streams','action'=>'detail',$id));
			}
			else
			{
				$this->Session->setFlash($response_array['error'], 'admin_flash_bad');
				$this->redirect(array('controller'=>'streams','action'=>'detail',$id));
			}
			
		}
		else
		{
			$this->Session->setFlash($response_array['error'], 'admin_flash_bad');
			$this->redirect(array('controller'=>'streams','action'=>'detail',$id));
		}
	}
	
	
	public function stop($id)
	{
		$this->layout = false;
		$stream_detail = $this->Stream->find('first',array('conditions'=>array('Stream.id'=>$id),'fields'=>array('Stream.id','Stream.stream_key')));
		$ch = curl_init('https://api.cloud.wowza.com/api/v1/live_streams/'.$stream_detail['Stream']['stream_key'].'/stop/');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); 
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $add_stream_request);    
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                           		'Content-Type: application/json ;charset=utf-8', 
			'wsc-api-key:'.Configure::read('WOWZA_API_KEY'),
			'wsc-access-key:'.Configure::read('WOWZA_ACCESS_KEY')
			)                                                                       
		); 
		$result = curl_exec($ch);
		curl_close($ch);
		$response_array = json_decode($result,true);
		if(!isset($response_array['error']))
		{
			if($response_array['live_stream']['state'] == 'stopped')
			{
				$this->request->data['Stream']['id'] = $stream_detail['Stream']['id'];
				$this->request->data['Stream']['state'] = 2;
				//$this->request->data['Stream']['connection_code'] = NULL;
				$this->Stream->save($this->request->data);
				$this->Session->setFlash("Streams stopped successfully.This may take a few minutes. Thank you for your patience.", 'flash_good');
				$this->redirect(array('controller'=>'streams','action'=>'detail',$id));						
			}
			else
			{
				$this->Session->setFlash($response_array['error'], 'admin_flash_bad');
				$this->redirect(array('controller'=>'streams','action'=>'detail',$id));
			}
			
		}
		else
		{
			$this->Session->setFlash($response_array['error'], 'admin_flash_bad');
			$this->redirect(array('controller'=>'streams','action'=>'detail',$id));
		}
	}
	
	
	
	public function get_connection_code($id)
	{
		$this->layout = false;
		$stream_detail = $this->Stream->find('first',array('conditions'=>array('Stream.id'=>$id),'fields'=>array('Stream.id','Stream.stream_id')));
		$ch = curl_init('https://api.cloud.wowza.com/api/v1/live_streams/'.$stream_detail['Stream']['stream_id'].'/regenerate_connection_code/');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); 
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $add_stream_request);    
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                           		'Content-Type: application/json ;charset=utf-8', 
			'wsc-api-key:'.Configure::read('WOWZA_API_KEY'),
			'wsc-access-key:'.Configure::read('WOWZA_ACCESS_KEY')
			)                                                                       
		); 
		$result = curl_exec($ch);
		curl_close($ch);
		$response_array = json_decode($result,true);
		
		
		if(!isset($response_array['error']))
		{
			$connection_code = $response_array['live_stream']['connection_code'];
			if(!empty($connection_code))
			{
				$this->request->data['Stream']['id'] = $stream_detail['Stream']['id'];
				$this->request->data['Stream']['connection_code'] = $connection_code;
				$this->Stream->save($this->request->data);
				$this->Session->setFlash("Connection code getting successfully.", 'flash_good');
				$this->redirect($this->referer());						
			}
			else
			{
				$this->Session->setFlash("Connection code not getting successfully.", 'admin_flash_bad');
				$this->redirect($this->referer());
			}
			
		}
		else
		{
			$this->Session->setFlash($response_array['error'], 'admin_flash_bad');
			$this->redirect($this->referer());
		} 
	}
	
	
	
	/* public function get_recordings()
	{
		$ch = curl_init('https://api.cloud.wowza.com/api/v1/recordings/cfcb2h6b/state/');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                           		
			'Content-Type: application/json ;charset=utf-8', 
			'wsc-api-key:'.Configure::read('WOWZA_API_KEY'),
			'wsc-access-key:'.Configure::read('WOWZA_ACCESS_KEY')
			)                                                                       
		); 
		 $result = curl_exec($ch);
		
		curl_close($ch);
		$response_array = json_decode($result,true);
		pr($response_array);
		die;
	} */
	
	
	
	public function channel_manager()
	{	
		$this->loadModel('User');
		$id = $this->Auth->User('id');
		$user_detail = $this->User->find('first', array('conditions' => array('User.id' => $id)));
		$this->set('user_detail',$user_detail);
		$this->layout = 'lay_dashboard';
		$this->set('title_for_layout','Channel Manager');
	}


	public function settings()
	{	
		$this->layout = 'lay_dashboard';
		$this->set('title_for_layout','Streaming  Settings');
	}		
	
	
	public function detail($id)
	{
		$this->layout = 'lay_dashboard';
		$this->set('title_for_layout','Stream Detail');
		$stream_detail = $this->Stream->find('first',array('conditions'=>array('Stream.id'=>$id),'fields'=>array('Stream.id','Stream.title','Stream.subject','Stream.stream_bio','Stream.stream_state','Stream.player_id','Stream.primary_server','Stream.stream_key','Stream.aspect_ratio','Stream.stream_image')));
		$this->set('stream_detail',$stream_detail);
	}
	
	
	public function start_stream($id)
	{
		$this->layout = false;
		$this->autoRender = false;
		$response = array('key'=>'success','msg'=>'Streams start successfully.This may take a few minutes. Thank you for your patience.');
		$stream_detail = $this->Stream->find('first',array('conditions'=>array('Stream.id'=>$id),'fields'=>array('Stream.id','Stream.stream_key')));
		$ch = curl_init('https://api.cloud.wowza.com/api/v1/live_streams/'.$stream_detail['Stream']['stream_key'].'/start/');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(    
			'Content-Type: application/json ;charset=utf-8', 
			'wsc-api-key:'.Configure::read('WOWZA_API_KEY'),
			'wsc-access-key:'.Configure::read('WOWZA_ACCESS_KEY')
			)                                                                       
		); 
		$result = curl_exec($ch);
		curl_close($ch);
		$response_array = json_decode($result,true);
		
		if(!isset($response_array['error']))
		{
			if($response_array['live_stream']['state'] == 'starting')
			{
				$this->request->data['Stream']['id'] = $stream_detail['Stream']['id'];
				//$this->request->data['Stream']['state'] = 1;
				$this->request->data['Stream']['state'] = STARTING;
				if($this->Stream->save($this->request->data));
				{
					$response = array('key'=>'success','msg'=>'Streams start successfully.This may take a few minutes. Thank you for your patience.');
					echo json_encode($response);
					die;
				}
			}
			else
			{
				echo $response = array('key'=>'failed','msg'=>$response_array['error']);
				die;
				
			}
			
		}
		else
		{
			echo $response = array('key'=>'failed','msg'=>$response_array['error']);
			die;
		}
	}
	
	
	public function check_stream_status($id)
	{
		$this->layout = false;
		$this->autoRender = false;
		$stream_detail = $this->Stream->find('first',array('conditions'=>array('Stream.id'=>$id),'fields'=>array('Stream.id','Stream.stream_key')));
		
		$ch = curl_init('https://api.cloud.wowza.com/api/v1/live_streams/'.$stream_detail['Stream']['stream_key'].'/state/');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(    
			'Content-Type: application/json ;charset=utf-8', 
			'wsc-api-key:'.Configure::read('WOWZA_API_KEY'),
			'wsc-access-key:'.Configure::read('WOWZA_ACCESS_KEY')
			)                                                                       
		); 
		$result = curl_exec($ch);
		curl_close($ch);
		$response_array = json_decode($result,true);
		if(!isset($response_array['error']))
		{
			$stream_state = 0;
			if($response_array['live_stream']['state'] == 'starting')
			{
				$stream_state = 1;
			}
			elseif($response_array['live_stream']['state'] == 'started')
			{
				$stream_state = 2;
				$this->Session->setFlash("Streams start successfully.Thank you for your patience.", 'flash_good');	
			}
			elseif($response_array['live_stream']['state'] == 'stopping')
			{
				$stream_state = 3;
			}
			elseif($response_array['live_stream']['state'] == 'stopped')
			{
				$stream_state = 4;
			}
			elseif($response_array['live_stream']['state'] == 'resetting')
			{
				$stream_state = 5;
			}
			$this->request->data['Stream']['id'] = $stream_detail['Stream']['id'];
			$this->request->data['Stream']['stream_state'] = $stream_state;
			if($this->Stream->save($this->request->data));
			{
				
				$response = array('key'=>'success','stream_state'=>$stream_state);
				echo json_encode($response);
				die;
			}
		}
		else
		{
			echo $response = array('key'=>'failed');
			die;
		} 
	}	
	
	
	public function stop_stream($id)
	{
		$this->layout = false;
		$this->autoRender = false;
		$stream_detail = $this->Stream->find('first',array('conditions'=>array('Stream.id'=>$id),'fields'=>array('Stream.id','Stream.stream_key')));
		$ch = curl_init('https://api.cloud.wowza.com/api/v1/live_streams/'.$stream_detail['Stream']['stream_key'].'/stop/');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); 
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $add_stream_request);    
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                           		'Content-Type: application/json ;charset=utf-8', 
			'wsc-api-key:'.Configure::read('WOWZA_API_KEY'),
			'wsc-access-key:'.Configure::read('WOWZA_ACCESS_KEY')
			)                                                                       
		); 
		$result = curl_exec($ch);
		curl_close($ch);
		$response_array = json_decode($result,true);
		if(!isset($response_array['error']))
		{
			if($response_array['live_stream']['state'] == 'stopped')
			{
				$this->request->data['Stream']['id'] = $stream_detail['Stream']['id'];
				//$this->request->data['Stream']['stream_state'] = 4;
				$this->request->data['Stream']['stream_state'] = STOPPED;
				//$this->request->data['Stream']['connection_code'] = NULL;
				$this->Stream->save($this->request->data);
				$this->Session->setFlash("Streams stopped successfully.This may take a few minutes. Thank you for your patience.", 'flash_good');
				$this->redirect(array('controller'=>'streams','action'=>'detail',$id));					
			}
			else
			{
				$this->Session->setFlash($response_array['error'], 'admin_flash_bad');
				$this->redirect(array('controller'=>'streams','action'=>'detail',$id));
			}
			
		}
		else
		{
			$this->Session->setFlash($response_array['error'], 'admin_flash_bad');
			$this->redirect(array('controller'=>'streams','action'=>'detail',$id));
		}
	}
	
	
	public function get_all_recordings()
	{
		
		
		$this->layout = false;
		$this->autoRender = false;
		$this->loadModel('RecordingStream');
		$get_all_streams = $this->Stream->find('all',array('fields'=>array('Stream.id','Stream.stream_key','Stream.user_id','Stream.stream_bio','Stream.channel_id')));
		$recordings_data_array = array();
		if(!empty($get_all_streams))
		{
			foreach($get_all_streams as $key=>$value)
			{
				$ch = curl_init('https://api.cloud.wowza.com/api/v1/transcoders/'.$value['Stream']['stream_key'].'/recordings/');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(    
				'Content-Type: application/json ;charset=utf-8', 
					'wsc-api-key:'.Configure::read('WOWZA_API_KEY'),
					'wsc-access-key:'.Configure::read('WOWZA_ACCESS_KEY')
					)                                                                       
				); 
				$result = curl_exec($ch);
				curl_close($ch);
				$response_array = json_decode($result,true);
				
				if(!empty($response_array['recordings']))
				{
					foreach($response_array['recordings'] as $k=>$v)
					{ 
						if($v['state'] == 'completed')
						{
							/* $thumb = $value['Stream']['user_id'].'_'.$value['Stream']['stream_key'].'_'.$v['id'].'.jpg';
							$thumbnail = '/var/www/html/app/webroot/uploads/recording_images/'.$thumb;
							$time = '00:00:10';
							exec("/usr/bin/ffmpeg -i ".$v['download_url']." -an -ss " . $time . " -an -r 1 -s qcif -vframes 1 -filter:v scale=280:160 -y $thumbnail 2>&1");
							$check_record_existing = $this->RecordingStream->find('count',array('conditions'=>array('RecordingStream.user_id'=>$value['Stream']['user_id'],'RecordingStream.recording_key'=>$v['id'],'RecordingStream.stream_key'=>$value['Stream']['stream_key']))); */
							
							
							$check_record_existing = $this->RecordingStream->find('count',array('conditions'=>array('RecordingStream.recording_key'=>$v['id'],'RecordingStream.stream_key'=>$value['Stream']['stream_key'])));
							
							if($check_record_existing == 0 && $v['file_size'] >0 && $v['duration'] > 0)
							{
								
								$thumb = $value['Stream']['user_id'].'_'.$value['Stream']['stream_key'].'_'.$v['id'].'.jpg';
								$thumbnail = '/var/www/html/app/webroot/uploads/recording_images/'.$thumb;
								$time = '00:00:10';
								exec("/usr/bin/ffmpeg -i ".$v['download_url']." -an -ss " . $time . " -an -r 1 -s qcif -vframes 1 -filter:v scale=280:160 -y $thumbnail 2>&1");
								
								
								$recordings_data['RecordingStream']['description'] = $value['Stream']['stream_bio'];
								$recordings_data['RecordingStream']['user_id'] = $value['Stream']['user_id'];
								$recordings_data['RecordingStream']['stream_key'] = $value['Stream']['stream_key'];
								$recordings_data['RecordingStream']['channel_id'] = $value['Stream']['channel_id'];
								$recordings_data['RecordingStream']['stream_id'] = $value['Stream']['id'];
								$recordings_data['RecordingStream']['image'] = $thumb;
								
								$recordings_data['RecordingStream']['title'] = $v['transcoder_name'];
								$recordings_data['RecordingStream']['recording_key'] = $v['id'];
								//$recordings_data['RecordingStream']['file_name'] = $v['file_name'];
								$recordings_data['RecordingStream']['file_size'] = $v['file_size'];
								$recordings_data['RecordingStream']['duration'] = $v['duration'];
								$recordings_data['RecordingStream']['download_url'] = $v['download_url'];
								$recordings_data['RecordingStream']['created'] = date('Y-m-d H:i:s',strtotime($v['created_at']));
								$recordings_data['RecordingStream']['modified'] = date('Y-m-d H:i:s',strtotime($v['updated_at']));;
								$recordings_data_array[] = $recordings_data;
								$recordings_data =array();
							}
						}
					}
				}
			}
		}
		if(!empty($recordings_data_array))
		{
			$this->RecordingStream->saveAll($recordings_data_array);
		}
		die;
		
	}
	
		
	
	public function recorded_stream_detail($id)
	{	
		$this->layout = 'lay_stream_detail';
		$this->loadModel('RecordingStream');
		$this->loadModel('ChannelFollower');
		$this->RecordingStream->bindModel(array(
                'hasOne' => array(
                    'ChannelFollower' => array(
                        'className' => 'ChannelFollower',
                        'foreignKey' => 'recording_stream_id',
                        'fields' => array('is_follow'), 
						'conditions'=>array('ChannelFollower.user_id'=>$this->Auth->user('id'),'ChannelFollower.recording_stream_id !='=>'0'),
                    ),
                ),	
			), false
		);
		$recorded_stream_detail = $this->RecordingStream->find('first',array('conditions'=>array('RecordingStream.id'=>$id)));
		
		
		$related_recorded_stream_listing = $this->RecordingStream->find('all',array('conditions'=>array('RecordingStream.user_id'=>$recorded_stream_detail['RecordingStream']['user_id'],'RecordingStream.id != '=>$id)));
		/* pr($related_recorded_stream_listing);
		die; */
		
		$this->set('recorded_stream_detail',$recorded_stream_detail);
		$this->set('title_for_layout',$recorded_stream_detail['RecordingStream']['title']);
		$this->set('related_recorded_stream_listing',$related_recorded_stream_listing);
	}
	
	
	
	
	public function stream_aspect_ration_options()
	{
		$this->layout = false;
		$this->autoRender = false;
		$aspect_ration_options =  array();
		if(!empty($_POST['broadcast_location']))
		{
			if (array_key_exists($_POST['broadcast_location'], Configure::read('Stream.Broadcast.Location')))
			{
				$aspect_ration_options =  array('1920x1080'=>'1920 x 1080(1080p)','1280x720'=>'1280 x 720(720p)','1024x576'=>'1024 x 576');;
				
			}
			else if (array_key_exists($_POST['broadcast_location'], Configure::read('4K.Broadcast.Location')))
			{
				$aspect_ration_options =  array('3840x2160'=>'3840 x 2160','1920x1080'=>'1920 x 1080(1080p)','1280x720'=>'1280 x 720(720p)','1024x576'=>'1024 x 576');;
			}
		}
		$this->set('aspect_ration_options',$aspect_ration_options);
		$this->render('/Elements/Front/Streams/stream_aspect_ration_options');
		
	}
	
	
	
	public function  bitrate_renditions()
	{
		$this->layout = false;
		$this->autoRender = false;
		$message = '';
		if(!empty($_POST['aspect_ratio']))
		{
			if($_POST['aspect_ratio']=='3840x2160')
			{
				$message = 'This setting creates <strong>7 bitrate renditions.</strong>';
			}
			else if($_POST['aspect_ratio']=='1920x1080')
			{
				$message = 'This setting creates <strong>6 bitrate renditions.</strong>';
			}
			else if($_POST['aspect_ratio']=='1280x720')
			{
				$message = 'This setting creates <strong>5 bitrate renditions.</strong>';
			}
			else if($_POST['aspect_ratio']=='1024x576')
			{
				$message = 'This setting creates <strong>5 bitrate renditions.</strong>';
			}
			
		}
		echo $message;
		die;;
	}
	
	
	
	
	
}
