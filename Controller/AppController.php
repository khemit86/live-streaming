<?php
/**
 * Application level Controller
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 * PHP 5
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
 
	App::uses('Controller', 'Controller');
	App::uses('CakeEmail', 'Network/Email');

/**
 * Application Controller
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
	
	//public $theme 		= 	"Cakestrap";
	public $components 	= 	array(	
		'Security',
		'Auth',
		'Session',
		'RequestHandler',
		'Cookie'
	);
	var $helpers 		= 	array('Html','General','Session');
	var $uses 			= 	array('Admin');
	
	public function beforeFilter() {
		
		$this->Security->unlockedActions = array($this->request->action);
		$this->Security->blackHoleCallback = '__securityError';
        $this->disableCache();
		
		if (isset($this->request->params['admin'])) {
		
			$this->layout 				= 	'admin';
			$this->Auth->userModel 		= 	'Admin';
			
			$this->Auth->authenticate 	= 	array('Admin');
			  
			$this->Auth->loginError 	= 	__("login_failed_invalid_username_or_password");
            $this->Auth->loginAction 	= 	array('admin' => true, 'controller' => 'admins', 'action' => 'login');
			
            $this->Auth->authError 		= 	__('You must login to view this information');
            $this->Auth->autoRedirect 	= 	false;
			
            $this->Auth->allow('admin_login');			
			AuthComponent::$sessionKey 	= 	'Auth.Admin';	
			
        } else {
			$this->layout 				= 	'default';
			$this->Auth->userModel 		= 	'User';
			
			//$this->Auth->authenticate 	= 	array('User');
			 $this->Auth->authenticate = array(
							'Form' => array(
							'scope' => array('User.status' => 1),
							'fields' => array('username' => 'email'))); 
			$this->Auth->loginError 	= 	__("login_failed_invalid_username_or_password");
            $this->Auth->loginAction 	= 	array('admin' => false, 'controller' => 'homes', 'action' => 'index');
			
			/* $this->Auth->unauthorizedRedirect = array(
				'controller' => 'homes',
				'action' => 'index' 
			); */
			
            // $this->Auth->authError 		= 	__('You must login to view this information');
            $this->Auth->autoRedirect 	= 	false;
			
           // $this->Auth->allow('login','signup');			
			AuthComponent::$sessionKey 	= 	'Auth.User';
			
		}
		
		
		$this->Auth->authorize = array('Controller');
		$userData = $this->getUserData($this->Auth->user('id'));
		$this->set('userData',$userData);
		$this->set( 'USER', $this->Auth->user() );
		
		
		
		$this->loadModel('Setting');
		$settings=$this->Setting->find('all',array('fields'=>array('Setting.name','Setting.value')));
		if(!empty($settings))
		{
			foreach($settings as $key=>$value)
			{
				Configure::write(strtoupper($value['Setting']['name']),$value['Setting']['value']);
			}
		}
	}
	
	
	
	
	
	
	public function sendMail($to, $subject, $message, $from) {
        if ($_SERVER['HTTP_HOST'] == '192.168.1.16') {
            $email = new CakeEmail('gmail');
        } elseif ($_SERVER['HTTP_HOST'] == '67.205.96.105:8080') {
            $email = new CakeEmail('gmail');
        } else {
            $email = new CakeEmail('gmail');
        }

        $email->template('default', 'default');
        $email->emailFormat('html');
        $email->from(trim($from));
        $email->to($to);
        $email->subject($subject);
		$site_url = Router::url("/",true);
		$email->viewVars(array("site_url"=>$site_url));
        if ($email->send($message))
            return true;
        return false;
    }
	
	public function __sendMail($To, $Subject, $message, $From, $template = 'default', $smtp = 1, $attachment = array() ) {
		 
		App::uses('CakeEmail', 'Network/Email');
		$email  = 	new CakeEmail();
		$email->config('default');
        $email->to($To);
        $email->from($From); 
        $email->subject($Subject);
		$email->emailFormat('html');
        $email->attachments($attachment) ;
        $email->template($template);
        $email->layout 	= 	'default';
		unset($this->helpers['Paginator']);
		
		if ($email->send($message)) {
			return true;
        } else {
			return false;
        }
		
    }
	
	
	function _setErrorLayout() {
		if ($this->name == 'CakeError') {
			if(isset($this->request->params['admin'])) {
				$this->layout = 'admin';
			}else{
				$this->layout = '404';
			}
		}
    }
	
	/**
     * isAuthorized
     *
     * @return void
     */
	 
	/* function isAuthorized($user) {

        if (isset($this->request->params['admin'])) {
            if ($this->Auth->user()) {
				
				//pr($this->Session->read('Auth.User'));die;
                if ($this->Auth->user('role_id') != 1 && $this->Auth->user('role_id') != 2) {
                    throw new MethodNotAllowedException(__('Invalid Request'));
                } else {
                    return true;
                }
            }
        } else {
            return true;
        }
    } */
	
	function isAuthorized($user) {

        /* if (isset($this->request->params['admin'])) {
            if ($this->Auth->user()) {
                if ($this->Auth->user('role_id') != 1) {
                    throw new MethodNotAllowedException(__('Invalid Request'));
                } else {
					return true;
                }
            }
        } else {
            return true;
        } */
		 return true;
    }
	
	 function getUserData($id=null)
	{
		$this->loadModel('User');
		if(empty($id)) {
			if(isset($this->params['admin'])){ 
				$SUser = $this->Session->read("Admin");
				$id =  $SUser['id'];
			} 
			if(empty($id)) {
				$id =  $this->Auth->User("id");
			}
			else {
				return false;  
			}
		}		
		$user_data_array = $this->User->find('first',array('conditions' => array('User.id' =>$id)));		
		if($user_data_array)
			return $user_data_array;
		else
			return false;  
	}
	
	public function displaySqlDump(){
		if (!class_exists('ConnectionManager') || Configure::read('debug') < 2) {
			return false;
		}
		$noLogs = !isset($logs);
		if ($noLogs):
			$sources = ConnectionManager::sourceList();
			$logs = array();
			foreach ($sources as $source):
				$db =& ConnectionManager::getDataSource($source);
				echo "<pre>";
				$log = $db->getLog(false, false);
				debug($log);
				die;
				if (!$db->isInterfaceSupported('getLog')):
					continue;
				endif;
				$logs[$source] = $db->getLog();
			endforeach;
		endif;

		if ($noLogs || isset($_forced_from_dbo_)):
			foreach ($logs as $source => $logInfo):
				$text = $logInfo['count'] > 1 ? 'queries' : 'query';
				printf(
					'<table class="cake-sql-log" id="cakeSqlLog_%s" summary="Cake SQL Log" cellspacing="0" border = "0">',
					preg_replace('/[^A-Za-z0-9_]/', '_', uniqid(time(), true))
				);
				printf('<caption>(%s) %s %s took %s ms</caption>', $source, $logInfo['count'], $text, $logInfo['time']);
			?>
			<thead>
				<tr><th>Nr</th><th>Query</th><th>Error</th><th>Affected</th><th>Num. rows</th><th>Took (ms)</th></tr>
			</thead>
			<tbody>
			<?php
				foreach ($logInfo['log'] as $k => $i) :
					echo "<tr><td>" . ($k + 1) . "</td><td>" . h($i['query']) . "</td><td>{$i['error']}</td><td style = \"text-align: right\">{$i['affected']}</td><td style = \"text-align: right\">{$i['numRows']}</td><td style = \"text-align: right\">{$i['took']}</td></tr>\n";
				endforeach;
			?>
			</tbody></table>
			<?php 
			endforeach;
		else:
			echo '<p>Encountered unexpected $logs cannot generate SQL log</p>';
		endif;	
	}
	
	function tz_list() {
		$zones_array = array();
		$timestamp = time();
		foreach(timezone_identifiers_list() as $key => $zone) {
		date_default_timezone_set($zone);
		$zones_array[$key]['zone'] = $zone;
		$zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
		}
		return $zones_array;
	}
	
	
	
}
