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
class PlansController extends AppController {



/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Plans';

/**
 * This controller use a model admins
 *
 * @var array
 */
	public $uses 		= array('Plan');
	
	public $helpers 	= array('Html', 'Session','General','Csv');
	var $components = 	array('General',"Upload");
	
	
	public function beforeFilter() {
	
        parent::beforeFilter();
        $this->loadModel('Plan');
		//$this->Auth->allow('index','login','signup','facebookOauthLogin','twitterOauth','twitterOauthCallback','contact_us','forgot_password','verify');
    }

	
	
	/*
	@ param : null
	@ return void
	*/
	
	
	public function admin_add() 
	{		
		$this->set('title_for_layout','Plan');	
		if(!empty($this->request->data))
		{			
			if (!isset($this->request->params['_Token']['key']) || ($this->request->params['_Token']['key'] != $this->request->params['_Token']['key'])) 
			{
				$blackHoleCallback = $this->Security->blackHoleCallback;
				$this->$blackHoleCallback();
            }
			
			//validate category data
			$this->Plan->set($this->request->data['Plan']);
			$this->Plan->setValidation('add');
			if ($this->Plan->validates()) 
			{	
				$data	=	$this->request->data['Plan'];
				$this->Plan->save($data,false);
				$category_id	=	$this->Plan->id;
				$this->Session->setFlash("Record has been added successfully", 'admin_flash_good');
				$this->redirect(array('controller'=>'plans', 'action'=>'index'));
			} 
			else 
			{				
				$this->Session->setFlash("Record has not been created", 'admin_flash_bad');
			}
		}
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
        if (!empty($this->request->data)) 
		{
			$this->Session->delete('AdminSearch');
           if (isset($this->request->data['Plan']['name']) && $this->request->data['Plan']['name'] != '') {
                $name = trim($this->request->data['Plan']['name']);
                $this->Session->write('AdminSearch.name', $name);
				
            }
		}
		
		if ($this->Session->check('AdminSearch')) 
		{
            $keywords 	= 	$this->Session->read('AdminSearch');
			foreach($keywords as $key=>$values){
				if($key == 'name')
				{
					$filters[] = array('Plan.'.$key.' LIKE'=>"%".$values."%");					
				}
			}
			
		}
		
		$this->paginate = array('Plan' => array(
			'limit' =>Configure::read('App.PageLimit'),
			'order' => array('Plan.id' => 'DESC'),
			'conditions' => $filters,
        ));		
		
		$data = $this->paginate('Plan');		
		$this->set(compact('data'));
		$this->set('title_for_layout', __('Plan', true));
		
	}
	
	/*
	@ param : null
	@ return void
	*/
	public function admin_edit($id = null)
	{	
		$this->set('title_for_layout','Plan');	
		$this->Plan->id 	= 	$id;
		
		/*check conditions allreday conditions for plans update*/
        if (!$this->Plan->exists()) 
		{
            throw new NotFoundException(__('Invalid Plan'));
        }
		/*form post and check conditions*/
		if ($this->request->is('post') || $this->request->is('put')) 
		{			
			if (!empty($this->request->data)) 
			{				
				if (!isset($this->request->params['_Token']['key']) || ($this->request->params['_Token']['key'] != $this->request->params['_Token']['key'])) 
				{
					$blackHoleCallback = $this->Security->blackHoleCallback;
					$this->$blackHoleCallback();
				}
				//validate plans data
				$this->Plan->set($this->request->data['Plan']);
				$this->Plan->setValidation('edit');
				if ($this->Plan->validates()) 
				{
					$this->Plan->create();
					if ($this->Plan->save($this->request->data['Plan'],false)) 
					{	
						$this->Session->setFlash("Record has been added successfully", 'admin_flash_good');
						$this->redirect(array('controller'=>'plans', 'action'=>'index'));
					} 
				} 
			}
			else 
			{
				 $this->Session->setFlash(__('The Plan could not be saved. Please, try again.', true), 'admin_flash_bad');
			}	
        } 
		else 
		{
			$this->request->data = $this->Plan->read(null, $id);			
		}
	}
	
		
	/*
	@ param : null
	@ return void
	*/
	public function admin_view($id = null){
	
		$this->set('title_for_layout','Plan');
		
		$this->Plan->id = $id;
		$this->set(compact('id'));
		
		/*check conditions allreday conditions for plans update*/
        if (!$this->Plan->exists()) 
		{
            throw new NotFoundException(__('Invalid Plan'));
        }		
		$data = $this->Plan->read(null, $id);
		$this->set(compact('data'));
	}
	
	/* 
	@ this function are used activated,deactivated and deleted plans by admin
	*/
	public function admin_process() 
	{	
		if (!empty($this->request->data)) 
		{
			if (!isset($this->request->params['_Token']['key']) || ($this->request->params['_Token']['key'] != $this->request->params['_Token']['key'])) 
			{
                $blackHoleCallback = $this->Security->blackHoleCallback;
                $this->$blackHoleCallback();
            }
            $action = $this->request->data['Plan']['pageAction'];
            foreach ($this->request->data['Plan'] AS $value) 
			{
                if ($value != 0) 
				{
                    $ids[] = $value;
                }
            }
            if (count($this->request->data) == 0 || $this->request->data['Plan'] == null) 
			{
                $this->Session->setFlash('No items selected.', 'admin_flash_bad');
                 $this->redirect(array('controller'=>'plans', 'action'=>'index'));
            }

            if ($action == "delete") 
			{				
				$this->Plan->deleteAll(array('Plan.id'=>$ids));				
                $this->Session->setFlash('Plans have been deleted successfully', 'admin_flash_good');
                $this->redirect(array('controller'=>'plans', 'action'=>'index'));
            }
			
            if ($action == "activate") 
			{				
				$this->Plan->updateAll(array('status'=>Configure::read('App.Status.active')),array('Plan.id'=>$ids));               
                $this->Session->setFlash('Plans have been activated successfully', 'admin_flash_good');
                $this->redirect(array('controller'=>'plans', 'action'=>'index'));
            }
			
            if ($action == "deactivate") 
			{			
				$this->Plan->updateAll(array('status'=>Configure::read('App.Status.inactive')),array('Plan.id'=>$ids));				
                $this->Session->setFlash('Plans have been deactivated successfully', 'admin_flash_good');
				$this->redirect(array('controller'=>'plans', 'action'=>'index'));
            }
			
        } 
		else 
		{
            $this->redirect(array('controller' => 'plans', 'action' => 'index'));
        }
    }
	
	
	public function admin_delete($id = null) 
	{
		$this->layout = false;
		if (!$id) 
		{
            $this->Session->setFlash(__('Invalid  id', true), 'admin_flash_good');
            $this->redirect(array('action' => 'index'));
        } 
		else 
		{	
			if ($this->Plan->deleteAll(array('Plan.id' => $id))) 
			{
                $this->Session->setFlash('Record has been deleted successfully','admin_flash_good');
                $this->redirect($this->referer());
            }
        }
    }
}
