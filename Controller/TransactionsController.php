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
class TransactionsController extends AppController {



/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Transactions';

/**
 * This controller use a model admins
 *
 * @var array
 */
	public $uses 		= array('Transaction');
	
	public $helpers 	= array('Html', 'Session','General','Csv');
	var $components = 	array('General',"Upload");
	
	
	public function beforeFilter() {
	
        parent::beforeFilter();
        $this->loadModel('Transaction');
		//$this->Auth->allow('index','login','signup','facebookOauthLogin','twitterOauth','twitterOauthCallback','contact_us','forgot_password','verify');
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
		//pr($this->request->data);die;
        if (!empty($this->request->data)) 
		{
			$this->Session->delete('AdminSearch');
           if (isset($this->request->data['Transaction']['nickname']) && $this->request->data['Transaction']['nickname'] != '') {
                $nickname = trim($this->request->data['Transaction']['nickname']);
                $this->Session->write('AdminSearch.nickname', $nickname);
				
            }
		}
		
		if ($this->Session->check('AdminSearch')) 
		{
            $keywords 	= 	$this->Session->read('AdminSearch');
			foreach($keywords as $key=>$values){
				if($key == 'nickname')
				{
					$filters[] = array('User.'.$key.' LIKE'=>"%".$values."%");				
				}
			}
			
		}
		
		
		//$this->Transaction->bindModel(array('belongsTo'=>array('User')));
		
		$this->Transaction->bindModel(array(
				'belongsTo'=>array(
					'User'=>array(
						'className'=>'User',
						'foreignKey'=>'user_id',
						'fields'=>array('id','nickname')
					),
				)
			),false);
		$this->paginate = array('Transaction' => array(
			'limit' =>Configure::read('App.PageLimit'),
			'order' => array('Transaction.id' => 'DESC'),
			'conditions' => $filters,
			//'fields'=>array('Transaction.*','User.nickname','User.id')
        ));		
		
		$data = $this->paginate('Transaction');	
		//parent::displaySqlDump();
		/* pr($data);
		die; */
		$this->set(compact('data'));
		$this->set('title_for_layout', __('Transaction Listing', true));
		
	}
	
	
	/*
	@ param : null
	@ return void
	*/
	public function admin_view($id = null){
	
		$this->set('title_for_layout','Transaction Detail');
		
		$this->Transaction->id = $id;
		$this->set(compact('id'));
		
		/*check conditions allreday conditions for users update*/
        if (!$this->Transaction->exists()) {
            throw new NotFoundException(__('Invalid Transaction'));
        }
		
		$this->Transaction->bindModel(array('belongsTo'=>array('User')));
		$data = $this->Transaction->read(array('Transaction.*','User.first_name','User.last_name','User.nickname'), $id);
		$this->set(compact('data'));
	}
	
}
