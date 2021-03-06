<?php
class MY_Controller extends CI_Controller{
	public $adminId;
	protected static $smarty; // smarty模板对象
	public function __construct(){
		parent::__construct();
		//$this->smarty = $this->init_smarty();//实例化smarty  这里不用暂时注视
		$this->load->library('session');//加载session类
		$url = $this->config->item('base_url');//获取网站的url地址
		$this->data['base_url'] = $url;
		
		//登录用户的相关数据
		$this->adminId = $this->session->userdata('adminId');
		if ($this->adminId) {
			$this->load->model('admin_user');
			$info = $this->admin_user->getUseInfo($this->adminId);
			$this->data['adminId'] = $this->adminId;
			$this->data['userInfo'] = $info;
			$c = $_GET['c'];
			$m = $_GET['m'];
			define("APP", $c);
			define("ACT", $m);
			$this->data['c'] = $c;
			$this->data['m'] = $m;
		}else{
			  $this->load->helper('url');
              redirect('/login/index');
		}
		//是否需要加载头部数据
		if($this->input->get('is_ajax')){
		
		}else{
			$menuInfo = $this->menu($c,$m);
			$this->load->view('public/header',$this->data);
			$this->data['menu'] = $menuInfo['list'];
			$this->data['menuInfo'] = $menuInfo['curInfo'];
			$this->load->view('public/menu',$this->data);
		}
	}
	
	public function menu($c='',$m=''){
		$this->load->model('admin_operate');
		$this->load->model('admin_ror');
		$operateIds = $this->admin_ror->getInfoByUserId($this->adminId);
		$result = $this->admin_operate->getAllMenus($operateIds);
		foreach ($result as $key=>&$rs){
			if (!in_array($rs['id'], $operateIds)) {
				unset($result[$key]);
			}
			if($rs['list']){
				foreach ($rs['list'] as $k=>$lt){
					if (!in_array($lt['id'], $operateIds)) {
						unset($rs['list'][$k]);
					}
				}
			}
		}
		$info = $this->admin_operate->getInfoByAct($c,$m);
		$data['list'] = $result;
		$data['curInfo'] = $info;
		return $data;
	}
	/*****************smarty相关配置************************/
	public function init_smarty(){
		if (! self::$smarty) {
			include_once (FCPATH.APPPATH . 'libraries/smarty/Smarty.class.php');
			$smarty = new Smarty ();
			$script_name = $_SERVER['SCRIPT_NAME'] ? strtolower(substr($_SERVER['SCRIPT_NAME'], 1)) : strtolower(substr($_SERVER['DOCUMENT_URI'], 1));
			$path = '/view/admin/';
			$smarty->template_dir = FCPATH.APPPATH . $path;
			$smarty->compile_dir = FCPATH.APPPATH . '/chache/template_c/';
			$smarty->config_dir = FCPATH.APPPATH . '/chache/configs/';
			$smarty->cache_dir = FCPATH.APPPATH . '/chache/cache/';
			//$smarty->debugging = true;
			self::$smarty = $smarty;
		}
		return self::$smarty;
	}
	public function display($tpl=''){
		self::$smarty->display($tpl);;
		
	}
	public function assign($v,$k){
		self::$smarty->assign($v,$k);
	}
	/*********************************end*******************/
}