<?php
/**
 * channel content controller.
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
 * channel content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */

App::uses('Sanitize', 'Utility');
class ChannelsController extends AppController {



/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Channels';

/**
 * This controller use a model admins
 *
 * @var array
 */
	public $uses 		= array('Channel');
	
	public $helpers 	= array('Html', 'Session','General','Csv');
	var $components = 	array('General',"Upload");
	
	
	public function beforeFilter() {
	
        parent::beforeFilter();
        $this->loadModel('Channel');
		$this->Auth->allow('index','channel_listing','channel_detail','update_channel_play_count');
    }
	
	/*
	@ param : null
	@ return void
	*/
	
	public function admin_index() {
		
		
		if (!isset($this->params['named']['page'])) {
            $this->Session->delete('AdminSearch');
        }
		
		$filters	=	array();
        if (!empty($this->request->data)) {
			$this->Session->delete('AdminSearch');
           if (isset($this->request->data['Channel']['name']) && $this->request->data['Channel']['name'] != '') {
                $name = trim($this->request->data['Channel']['name']);
                $this->Session->write('AdminSearch.name', $name);
            }
			
           if (isset($this->request->data['Channel']['company']) && $this->request->data['Channel']['company'] != '') {
                $company = trim($this->request->data['Channel']['company']);
                $this->Session->write('AdminSearch.company', $company);
            }
			
           if (isset($this->request->data['Channel']['website']) && $this->request->data['Channel']['website'] != '') {
                $website = trim($this->request->data['Channel']['website']);
                $this->Session->write('AdminSearch.website', $website);
            }
			
		}
		
		if ($this->Session->check('AdminSearch')) {
            $keywords 	= 	$this->Session->read('AdminSearch');
			foreach($keywords as $key=>$values){
				if($key == 'name'){
					$filters[] = array('Channel.'.$key.' LIKE'=>"%".$values."%");					
				}
				if($key == 'company'){
					$filters[] = array('Channel.'.$key.' LIKE'=>"%".$values."%");				
				}
				if($key == 'website'){
					$filters[] = array('Channel.'.$key.' LIKE'=>"%".$values."%");				
				}
				
				
			}
		}
		$this->loadModel('Channel');		
		$this->paginate = array('Channel' => array(
			'limit' =>Configure::read('App.PageLimit'),
			'order' => array('Channel.id' => 'DESC'),
			'conditions' => $filters,
        ));
		$data = $this->paginate('Channel');		
		$this->set(compact('data'));
		$this->set('title_for_layout', __('Channel', true));
		
	}
	
	
	
	public function admin_video_index() {
		
		
		if (!isset($this->params['named']['page'])) {
            $this->Session->delete('AdminSearch');
        }
		$this->loadModel('RecordingStream');
		$filters	=	array();
        if (!empty($this->request->data)) {
			$this->Session->delete('AdminSearch');
           if (isset($this->request->data['RecordingStream']['title']) && $this->request->data['RecordingStream']['title'] != '') {
                $title = trim($this->request->data['RecordingStream']['title']);
                $this->Session->write('AdminSearch.title', $title);
            }
			
           if (isset($this->request->data['RecordingStream']['description']) && $this->request->data['RecordingStream']['description'] != '') {
                $description = trim($this->request->data['RecordingStream']['description']);
                $this->Session->write('AdminSearch.description', $description);
            }
			
           if (isset($this->request->data['Channel']['name']) && $this->request->data['Channel']['name'] != '') {
                $name = trim($this->request->data['Channel']['name']);
                $this->Session->write('AdminSearch.name', $name);
            }
			
		}
		
		if ($this->Session->check('AdminSearch')) {
            $keywords 	= 	$this->Session->read('AdminSearch');
			foreach($keywords as $key=>$values){
				if($key == 'title'){
					$filters[] = array('RecordingStream.'.$key.' LIKE'=>"%".$values."%");					
				}
				if($key == 'description'){
					$filters[] = array('RecordingStream.'.$key.' LIKE'=>"%".$values."%");				
				}
				if($key == 'name'){
					$filters[] = array('Channel.'.$key.' LIKE'=>"%".$values."%");				
				}
				
				
			}
		}
		
		
		
		$this->RecordingStream->bindModel(array(
			'belongsTo'=>array(
				'User'=>array(
					'className'=>'User',
					'foreignKey'=>'user_id',
					'fields'=>array('id','first_name','profile_image')	
				),
				'Channel'=>array(
					'className'=>'Channel',
					'foreignKey'=>'channel_id',
					'fields'=>array('id','name','image')	
				)
			)			
		),false);
		
		$this->loadModel('RecordingStream');		
		$this->paginate = array('RecordingStream' => array(
			'limit' =>Configure::read('App.PageLimit'),
			'order' => array('RecordingStream.id' => 'DESC'),
			'conditions' => $filters,
        ));
		$data = $this->paginate('RecordingStream');
		//pr($data);die;
		$this->set(compact('data'));
		$this->set('title_for_layout', __('Video Listing', true));
		
	}
	
			
	/*
	@ param : null
	@ return void
	*/
	public function admin_view($id = null){
	
		$this->set('title_for_layout','Video Detail');
		
		$this->loadModel('RecordingStream');
		$this->RecordingStream->id = $id;
		$this->set(compact('id'));
		
		/*check conditions allreday conditions for users update*/
        if (!$this->RecordingStream->exists()) {
            throw new NotFoundException(__('Invalid Video'));
        }
		$this->RecordingStream->bindModel(array(
			'belongsTo'=>array(
				'User'=>array(
					'className'=>'User',
					'foreignKey'=>'user_id',
					'fields'=>array('id','first_name','profile_image')	
				),
				'Channel'=>array(
					'className'=>'Channel',
					'foreignKey'=>'channel_id',
					'fields'=>array('id','name','image')	
				)
			)			
		),false);
		$data = $this->RecordingStream->read(null, $id);
		$this->set(compact('data'));
	}
	
	
	public function admin_recording($id = null) {
		
		
		if (!isset($this->params['named']['page'])) {
            $this->Session->delete('AdminSearch');
        }
		$this->set('id',$id);
		$filters	=	array();
		$filters	=	array('RecordingStream.channel_id'=>$id);
        if (!empty($this->request->data)) {
			$this->Session->delete('AdminSearch');
          
			if (isset($this->request->data['User']['first_name']) && $this->request->data['User']['first_name'] != '') {
                $first_name = trim($this->request->data['User']['first_name']);
                $this->Session->write('AdminSearch.first_name', $first_name);				
            }
			
			if (isset($this->request->data['RecordingStream']['title']) && $this->request->data['RecordingStream']['title'] != '') {
                $title = trim($this->request->data['RecordingStream']['title']);
                $this->Session->write('AdminSearch.title', $title);				
            }
			
			if (isset($this->request->data['RecordingStream']['description']) && $this->request->data['RecordingStream']['description'] != '') {
                $description = trim($this->request->data['RecordingStream']['description']);
                $this->Session->write('AdminSearch.description', $description);				
            }
			
			
		}
		
		if ($this->Session->check('AdminSearch')) {
            $keywords 	= 	$this->Session->read('AdminSearch');
			foreach($keywords as $key=>$values){
				if($key == 'first_name'){
					$filters[] = array('User.'.$key.' LIKE'=>"%".$values."%");					
				}
				if($key == 'title'){
					$filters[] = array('RecordingStream.'.$key.' LIKE'=>"%".$values."%");					
				}
				if($key == 'description'){
					$filters[] = array('RecordingStream.'.$key.' LIKE'=>"%".$values."%");					
				}
			}
		}
		
		$this->loadModel('RecordingStream');
		
		$this->RecordingStream->bindModel(array(
			'belongsTo'=>array(
				'User'=>array(
					'className'=>'User',
					'foreignKey'=>'user_id',
					'fields'=>array('id','first_name','profile_image')	
				)
			)			
		),false);
		
		$this->paginate = array('RecordingStream' => array(
			'limit' =>Configure::read('App.PageLimit'),
			'order' => array('RecordingStream.id' => 'DESC'),
			'conditions' => $filters,
        ));
		$data = $this->paginate('RecordingStream');	
		$this->set(compact('data'));
		$this->set('title_for_layout', __('Recording Stream', true));
		
	}
	
	/*
	@ param : null
	@ return void
	*/
/* 
	@ this function are used activated,deactivated and deleted channels by admin
	*/
	public function admin_process() {
	
		if (!empty($this->request->data)) {
			if (!isset($this->request->params['_Token']['key']) || ($this->request->params['_Token']['key'] != $this->request->params['_Token']['key'])) {
                $blackHoleCallback = $this->Security->blackHoleCallback;
                $this->$blackHoleCallback();
            }
			
          
            $action = $this->request->data['Channel']['pageAction'];
            foreach ($this->request->data['Channel'] AS $value) {
                if ($value != 0) {
                    $ids[] = $value;
                }
            }

			
            if (count($this->request->data) == 0 || $this->request->data['Channel'] == null) {
                $this->Session->setFlash('No items selected.', 'admin_flash_bad');
                 $this->redirect(array('controller'=>'channels', 'action'=>'index'));
            }

            /* if ($action == "delete") {
				
				
				$profileImages	=	$this->Channel->find('all',array('fields'=>array('id','profile_image','background_image'),'conditions'=>array('Channel.id'=>$ids)));
				
				if(!empty($profileImages)) {
					 
					foreach ($profileImages AS $img) {
						$image		=	$img['Channel']['profile_image'];
						$background_image		=	$img['Channel']['background_image'];
						if($image &&  file_exists(WWW_ROOT.PROFILE_IMAGE_FULL_DIR.DS.$image )) {
							@unlink(WWW_ROOT.PROFILE_IMAGE_FULL_DIR.DS.$image);
						}
						if($background_image &&  file_exists(WWW_ROOT.BACKGROUND_IMAGE_FULL_DIR.DS.$background_image )) {
							@unlink(WWW_ROOT.BACKGROUND_IMAGE_FULL_DIR.DS.$background_image);
						}
					}
				}
				$this->Channel->deleteAll(array('Channel.id'=>$ids));
                $this->Session->setFlash('channels have been deleted successfully', 'admin_flash_good');
                $this->redirect(array('controller'=>'channels', 'action'=>'index'));
            } */
			
            if ($action == "activate") {
				
				$this->Channel->updateAll(array('status'=>Configure::read('App.Status.active')),array('Channel.id'=>$ids));
               
                $this->Session->setFlash('Channels have been activated successfully', 'admin_flash_good');
                $this->redirect(array('controller'=>'channels', 'action'=>'index'));
            }
			
            if ($action == "deactivate") {
			
				$this->Channel->updateAll(array('status'=>Configure::read('App.Status.inactive')),array('Channel.id'=>$ids));
				
                $this->Session->setFlash('Channels have been deactivated successfully', 'admin_flash_good');
				$this->redirect(array('controller'=>'channels', 'action'=>'index'));
            }
			
        } else {
            $this->redirect(array('controller' => 'channels', 'action' => 'index'));
        }
    }
	
	public function admin_video_process() {
	
		if (!empty($this->request->data)) {
			if (!isset($this->request->params['_Token']['key']) || ($this->request->params['_Token']['key'] != $this->request->params['_Token']['key'])) {
                $blackHoleCallback = $this->Security->blackHoleCallback;
                $this->$blackHoleCallback();
            }
			
			$this->loadModel('RecordingStream');
            $action = $this->request->data['RecordingStream']['pageAction'];
            foreach ($this->request->data['RecordingStream'] AS $value) {
                if ($value != 0) {
                    $ids[] = $value;
                }
            }

			
            if (count($this->request->data) == 0 || $this->request->data['RecordingStream'] == null) {
                $this->Session->setFlash('No items selected.', 'admin_flash_bad');
                 $this->redirect(array('controller'=>'channels', 'action'=>'video_index'));
            }

            if ($action == "activate") {
				
				$this->RecordingStream->updateAll(array('status'=>Configure::read('App.Status.active')),array('RecordingStream.id'=>$ids));
               
                $this->Session->setFlash('Videos have been activated successfully', 'admin_flash_good');
                $this->redirect(array('controller'=>'channels', 'action'=>'video_index'));
            }
			
            if ($action == "deactivate") {
			
				$this->RecordingStream->updateAll(array('status'=>Configure::read('App.Status.inactive')),array('RecordingStream.id'=>$ids));
				
                $this->Session->setFlash('Videos have been deactivated successfully', 'admin_flash_good');
				$this->redirect(array('controller'=>'channels', 'action'=>'video_index'));
            }
			
        } else {
            $this->redirect(array('controller' => 'channels', 'action' => 'video_index'));
        }
    }
	public function admin_recording_process() {
	
		if (!empty($this->request->data)) {
			if (!isset($this->request->params['_Token']['key']) || ($this->request->params['_Token']['key'] != $this->request->params['_Token']['key'])) {
                $blackHoleCallback = $this->Security->blackHoleCallback;
                $this->$blackHoleCallback();
            }
			
          $this->loadModel('RecordingStream');
            $action = $this->request->data['RecordingStream']['pageAction'];
            foreach ($this->request->data['RecordingStream'] AS $value) {
                if ($value != 0) {
                    $ids[] = $value;
                }
            }

			
            if (count($this->request->data) == 0 || $this->request->data['RecordingStream'] == null) {
                $this->Session->setFlash('No items selected.', 'admin_flash_bad');
                 $this->redirect($this->referer());
            }

            
			
            if ($action == "activate") {
				
				$this->RecordingStream->updateAll(array('status'=>Configure::read('App.Status.active')),array('RecordingStream.id'=>$ids));
               
                $this->Session->setFlash('Recording streams have been activated successfully', 'admin_flash_good');
                 $this->redirect($this->referer());
            }
			
            if ($action == "deactivate") {
			
				$this->RecordingStream->updateAll(array('status'=>Configure::read('App.Status.inactive')),array('RecordingStream.id'=>$ids));
				
                $this->Session->setFlash('Recording streams have been deactivated successfully', 'admin_flash_good');
				 $this->redirect($this->referer());
            }
			
        } else {
            $this->redirect($this->referer());
        }
    }
	
	
	public function channel_listing()
	{
		$this->layout = "front";
		$this->set('title_for_layout','CHANNELS');
		$this->loadModel('Stream');
		
		if(!empty($this->request->data))
		{
			
			$keyword = trim($this->request->data['Channel']['keyword']);
			$filters = array();
			$stream_channel_ids_array =array();
			$stream_data = $this->Stream->find('all',array('conditions'=>array('Stream.title LIKE'=>"%".trim($keyword)."%"),'fields'=>array('Stream.channel_id')));
			if(!empty($stream_data))
			{
				foreach($stream_data as $key=>$value)
				{
					$stream_channel_ids_array[] = $value['Stream']['channel_id'];
				}
			}
			
			$filters[] = array('OR'=>array('Channel.name LIKE'=>"%".trim($keyword)."%",'Channel.id'=>$stream_channel_ids_array));
		
			$this->paginate = array('Channel' => array(
				'limit' =>Configure::read('App.PageLimit'),
				'order' => array('Channel.name' => 'DESC'),
				'conditions' => $filters,
				'group'=>array('Channel.id')
			));
			$channel_listing = $this->paginate('Channel');
		}
		else
		{	
			
			
			$this->paginate = array('Channel' => array(
				'limit' =>Configure::read('App.PageLimit'),
				'order' => array('Channel.name' => 'DESC'),
				'conditions' => array('Channel.status'=>Configure::read('App.Status.active'))
			));
			$channel_listing = $this->paginate('Channel');
		}
		
		$this->set('channel_listing',$channel_listing);
	}
	
	
	
	
	public function channel_detail($id = null)
	{
		
		$this->layout = "lay_channel_detail";
		$this->set('title_for_layout','Channel Detail');
		
		$this->loadModel('Channel');
		$this->loadModel('RecordingStream');
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
				'RecordingStream'=>array(
					'className'=>'RecordingStream',
					'foreignKey'=>'channel_id',
					'fields'=>array('id','title','image')	
				)
			)			
		),false);
		
		$channelData = $this->Channel->find('first',array('conditions'=>array('Channel.id'=>$id)));
		$this->set('channelData',$channelData);
		
	}
	
	public function channel_manager()
	{	
		$id = $this->Auth->User('id');
		$this->User->bindModel(array('hasOne'=>array('Channel'=>array('fields' => array('id','name','image','website','company','bio')))),false);
		$this->User->bindModel(array('hasMany'=>array('RecordingStream'=>array('fields' => array('id','title','created','image'),'order'=>array('created'=>'desc')))),false);
		$user_detail = $this->User->find('first', array('conditions' => array('User.id' => $id)));
		
		$this->set('user_detail',$user_detail);
		$this->layout = 'lay_dashboard';
		$this->set('title_for_layout','Channel Manager');
	}
	
	public function setting(){
	
			
	 	 if ($this->RequestHandler->isAjax()) {
			$this->autoRender = false;
			$this->layout = 'ajax';
		}  
		$this->loadModel('Channel');
		$user_id = $this->Session->read('Auth.User.id');

		$exist = $this->Channel->find('first',array('conditions'=>array('Channel.user_id'=>$user_id)));
		if ($this->request->data) 
		{			;
			unset($this->request->data['Channel']['id']);
			$this->Channel->set($this->request->data);
			$this->Channel->setValidation('channel_add');
			
			if($this->Channel->validates()) 
			{ 
				
				if(isset($exist) && !empty($exist)){				
					$this->request->data['Channel']['id'] = $exist['Channel']['id'];
				}
				$this->request->data['Channel']['user_id'] = $user_id;
				
				if($this->Channel->save($this->request->data)) {
					
					$this->Session->setFlash(__('Channel info updated successfully.'), 'flash_success');
					echo "<script>window.location.href = '".SITE_URL."channels/channel_manager'</script>";						
				}else{
					$this->Session->setFlash('Channel info not updated.Please try again', 'flash_error');
				}	
			}else{
			
			$this->Session->setFlash('Channel info not updated.Please try again', 'flash_popup_error');
			}
			
			
			
		}else{
		
		$this->request->data = $exist;
		}
		// pr($exist);die;
		// $this->set('channel_data',$this->request->data);
		$this->render('/Elements/Front/Streams/channel_detail');
		
	}



	public function edit_recording($id = null){
	
			
	 	 if ($this->RequestHandler->isAjax()) {
			$this->autoRender = false;
			$this->layout = 'ajax';
		}  
		$this->loadModel('RecordingStream');
		
				
		if ($this->request->data) 
		{		
			$this->RecordingStream->set($this->request->data);
			$this->RecordingStream->setValidation('edit');
			
			if($this->RecordingStream->validates()) 
			{ 
				if($this->RecordingStream->save($this->request->data)) {
					
					$this->Session->setFlash(__('Recorded stream updated successfully.'), 'flash_success');
					echo "<script>window.location.href = '".SITE_URL."channels/channel_manager'</script>";						
				}else{
					$this->Session->setFlash('Recorded stream cannot be saved.Please try again', 'flash_error');
				}	
			}else{
			
			$this->Session->setFlash('Recorded stream cannot be saved.Please try again', 'flash_popup_error');
			}			
		}else{	
			$data = $this->RecordingStream->read(null,$id);		
			$this->request->data = $data;
		}
		$this->render('/Elements/Front/Streams/recording_detail');
		
	}
	public function channelpicupload(){
		$this->loadModel('Channel');
	    if(isset($_FILES['uploadfile']['name'])){
			$p_image = $_FILES['uploadfile'];			
			$oldpic= $this->Channel->find("first",array("conditions"=>array("Channel.user_id"=>$this->Auth->User('id'))));
			
			$old_image	=	$oldpic['Channel']['image'];
			$old_image_id	=	$oldpic['Channel']['id'];
		
			if (!empty($p_image) && $p_image['tmp_name'] != '' && $p_image['size'] > 0) {
				list($width, $height, $type, $attr) = getimagesize($p_image['tmp_name']);
				$allowed	=	array('jpg','jpeg','png','gif','JPG','JPEG','PNG','GIF');
				$temp 		= 	explode(".", $p_image["name"]);
				$extension 	= 	end($temp);
				$imageName 	= 	'profile_image_'.microtime(true).'.'.$extension;
				$files		=	$p_image;
				
				$result 	= 	$this->Upload->upload($files, WWW_ROOT . CHANNEL_IMAGE_FULL_DIR . DS, $imageName,'',$allowed);
				
				if($width<1645 || $height<550)
				{
					echo "sizeError|"."Image size should be 1645x550 or greater.";
					die;
				}
				if(!empty($this->Upload->result) && empty($this->Upload->errors)) 
				{					
					if($old_image &&  file_exists(WWW_ROOT.CHANNEL_IMAGE_FULL_DIR.DS.$old_image )) 
					{
						@unlink(WWW_ROOT.CHANNEL_IMAGE_FULL_DIR.DS.$old_image);
					}
					
					$this->request->data['Channel']['image'] = $imageName;
					$this->request->data['Channel']['user_id'] = $this->Auth->User('id');
					$this->request->data['Channel']['id'] = $old_image_id;
					
					$avataruploaded = $this->Channel->save($this->request->data);
					if ($avataruploaded){	
						echo "success|".$imageName;
					}else{
						echo "failed";
					}
					die;
				}
			}
		}		 
   }
	
	
	public function admin_change_featured_status($id = null) {
		
		$get_featured_detail = $this->Channel->find('first',array('conditions'=>array('Channel.id'=>$id),'fields'=>array('Channel.id','Channel.featured')));
	
		if($get_featured_detail['Channel']['featured'] == 1)
		{
			$this->Channel->id = $get_featured_detail['Channel']['id'];
			$this->Channel->saveField('featured',0);
			 $this->Session->setFlash(__('Admin\'s channel featured has been changed'), 'admin_flash_good');
				$this->redirect($this->referer());
		}
		else
		{
			$this->Channel->id = $get_featured_detail['Channel']['id'];
			$this->Channel->saveField('featured',1);
			$this->Session->setFlash(__('Admin\'s channel unfeatured was not changed', 'admin_flash_error'));
			$this->redirect($this->referer());
		}
    }
	public function admin_change_ishome_status($id = null) {
		
		$this->loadModel('RecordingStream');
		$get_ishome_detail = $this->RecordingStream->find('first',array('conditions'=>array('RecordingStream.id'=>$id),'fields'=>array('RecordingStream.id','RecordingStream.is_home')));
	
	
		if($get_ishome_detail['RecordingStream']['is_home'] == 1)
		{
			$this->RecordingStream->id = $get_ishome_detail['RecordingStream']['id'];
			$this->RecordingStream->saveField('is_home',0);
			 $this->Session->setFlash(__('Video does not show on home page'), 'admin_flash_good');
				$this->redirect($this->referer());
		}
		else
		{
			$this->RecordingStream->id = $get_ishome_detail['RecordingStream']['id'];
			$this->RecordingStream->saveField('is_home',1);
			$this->Session->setFlash(__('Video show on home page'), 'admin_flash_good');
			$this->redirect($this->referer());
		}
    }
	
	
	 public function admin_status($id = null) {
        if ($this->Session->check('Auth.User.id') && $this->Session->read('Auth.User.role_id') == Configure::read('App.SubAdmin.role')) { die('iiififififi');
            $this->Session->setFlash(__('You are not authorizatized for this action'), 'admin_flash_error');
            $this->redirect(array('controller' => 'admins', 'action' => 'dashboard'));
        }
        $this->Channel->id = $id;
		
		
        if (!$this->Channel->exists()) {
            throw new NotFoundException(__('Invalid Channel'));
        }
       
        $this->loadModel('Channel'); 
        if ($this->Channel->toggleStatus($id)) { 
            $this->Session->setFlash(__('Channel status has been changed'), 'admin_flash_good');
		    $this->redirect($this->referer());
        }
        $this->Session->setFlash(__('Channel status was not changed', 'admin_flash_error'));
        $this->redirect($this->referer());
    }
	 public function admin_video_status($id = null) {
        if ($this->Session->check('Auth.User.id') && $this->Session->read('Auth.User.role_id') == Configure::read('App.SubAdmin.role')) { die('iiififififi');
            $this->Session->setFlash(__('You are not authorizatized for this action'), 'admin_flash_error');
            $this->redirect(array('controller' => 'admins', 'action' => 'dashboard'));
        }
		$this->loadModel('RecordingStream');
        $this->RecordingStream->id = $id;
		
		
        if (!$this->RecordingStream->exists()) {
            throw new NotFoundException(__('Invalid Video'));
        }
       
        $this->loadModel('RecordingStream'); 
        if ($this->RecordingStream->toggleStatus($id)) { 
            $this->Session->setFlash(__('Video status has been changed'), 'admin_flash_good');
		    $this->redirect($this->referer());
        }
        $this->Session->setFlash(__('Video status was not changed', 'admin_flash_error'));
        $this->redirect($this->referer());
    }
	
	public function my_channel()
	{	
		$this->layout = 'lay_stream_detail';
		$this->set('title_for_layout','My Channel');
		$user_id = $this->Auth->User('id');
		$this->loadModel('Channel');
		$this->loadModel('Stream');
		$this->loadModel('RecordingStream');
		$channel_detail = $this->Channel->find('first',array('conditions'=>array('Channel.user_id'=>$user_id)));
		$latest_live_stream = $this->Stream->find('first',array('conditions'=>array('Stream.user_id'=>$user_id),'order'=>array('Stream.id'=>'DESC')));
		
		//$latest_recorded_stream = $this->RecordingStream->find('first',array('conditions'=>array('RecordingStream.user_id'=>$user_id),'order'=>array('RecordingStream.id'=>'DESC')));
		//$this->set('latest_recorded_stream',$latest_recorded_stream);
		
		$related_recorded_stream = $this->RecordingStream->find('all',array('conditions'=>array('RecordingStream.user_id'=>$user_id),'order'=>array('RecordingStream.id'=>'DESC')));
		$this->set('channel_detail',$channel_detail);
		$this->set('latest_live_stream',$latest_live_stream);
		$this->set('related_recorded_stream',$related_recorded_stream);
		//pr($latest_recorded_stream);
		
	}
	
	
	public function update_channel_play_count()
	{
		$this->layout = false;
		$this->autoRender = false;
		if(!empty($_POST) && !empty($_POST['channel_id']))
		{
			if($this->Channel->updateAll(
				array('Channel.play_count' => 'Channel.play_count + 1'),
				array('Channel.id' => $_POST['channel_id'])
			))
			{
				echo "1";die;
			}
			else
			{	
				echo "0";die;
			}
		}
		
	}
	
	
	
	public function subscribe()
	{
		$this->autoRender = false;
		$this->layout = 'ajax';
		if(!empty($_POST))
		{
			if(!empty($_POST) && !empty($_POST['channel_id']))
			{
				$this->loadModel('ChannelSubscription');
				$this->request->data['ChannelSubscription']['user_id'] =$this->Auth->User('id');
				$this->request->data['ChannelSubscription']['channel_id'] = $_POST['channel_id'];
				$this->request->data['ChannelSubscription']['stream_id'] = $_POST['stream_id'];
				if($this->ChannelSubscription->save($this->request->data))
				{
				
					if($this->Channel->updateAll(
						array('Channel.subscribe_count' => 'Channel.subscribe_count + 1'),
						array('Channel.id' => $_POST['channel_id'])
					))
				
				
					//$this->Session->setFlash('Channel Subscribe Successfully.', 'flash_success');
					echo "1";die;
				}
				else
				{
					
					//$this->Session->setFlash('Please try again later', 'flash_error');
					echo "0";die;
				}
			}
		}
	}
	
	
	
	public function unsubscribe()
	{
		$this->autoRender = false;
		$this->layout = 'ajax';
		if(!empty($_POST))
		{
			if(!empty($_POST) && !empty($_POST['channel_id']))
			{
				$this->loadModel('ChannelSubscription');
				
				$channel_subscription_detail = $this->ChannelSubscription->find('first',array('conditions'=>array('ChannelSubscription.user_id'=>$this->Auth->User('id'),'ChannelSubscription.channel_id'=>$_POST['channel_id'],'ChannelSubscription.stream_id'=>$_POST['stream_id'])));
				
				if($this->ChannelSubscription->delete($channel_subscription_detail['ChannelSubscription']['id']))
				{
				
					if($this->Channel->updateAll(
						array('Channel.subscribe_count' => 'Channel.subscribe_count - 1'),
						array('Channel.id' => $_POST['channel_id'])
					))
				
				
					//$this->Session->setFlash('Channel Subscribe Successfully.', 'flash_success');
					echo "1";die;
				}
				else
				{
					
					//$this->Session->setFlash('Please try again later', 'flash_error');
					echo "0";die;
				}
			}
		}
	}
	
	
	
}
