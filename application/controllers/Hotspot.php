<?php 
	
	 if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 	
 	class Hotspot extends CI_Controller {
 		private $_organization = array();

	    public function __construct() {
	        parent::__construct();
	        $this->load->library('session');
			$organization = [
				'salt'				=> $this->session->userdata('salt'),
				'username'			=> $this->session->userdata('username'),
				'id'				=> $this->session->userdata('id'),
				'accesskey'			=> $this->session->userdata('accesskey'),
                'branch_id'			=> $this->session->userdata('branch_id'),
			];

			if(empty($organization['id']) || empty($organization['salt']) || empty($organization['username']))
				redirect('/','refresh');

			$this->_organization = $organization;
	    }


	    public function index(){

	    	$accesskey = $this->input->get('accesskey');
			if(!$accesskey || empty($accesskey)) return false;

	    	$this->load->model('Portal_model');
	    	$bech = $this->Portal_model->branch(array('salt'=>$accesskey));	    	
	    	
	    	$data = array(
               'accesskey'  => 	$accesskey,
               'branch_id'  =>  $bech['id'],              
           	);
           	//创建session	
			$this->session->set_userdata($data);	    	
		
			/*$this->redirect('/hotspot/base?accesskey='.$accesskey);*/
			$this->load->library('twig');	
			$this->twig->display('hotspot/index.php', $data);
			
	    }
	
 	    public function base(){

            $orginazation = $this->_organization;

			$this->load->model('Portal_model');;
			
			if($this->input->post()){
				
				$id = $this->input->post('id');
				$salt = $this->input->post('salt');
				$data = $this->input->post('data');
				$data['access_info'] = json_encode($this->input->post('access_info'));

				if(empty($data['wechat'])) $data['wechat'] = '0';

				if(empty($data['qq'])) $data['qq'] = '0';

				if(empty($data['weibo'])) $data['weibo'] = '0';

				$where = ['id'=>$id,'salt'=>$salt];

				$this->Portal_model->save($data,'hotspot_branch',$where);

				echo json_encode(['status'=>"success",'message'=>'修改成功!']);
				exit();

			}
			$accesskey = $this->input->get('accesskey');
			$bech = $this->Portal_model->branch(['salt'=>$accesskey]);
			if(false==$bech || empty($bech)) $this->redirect('/manage');
			$data =	['accesskey'=>$accesskey,'bech'=>$bech];
			$this->load->library('twig');	
        	$this->twig->display('hotspot/base.php', $data);
        }

        public function downtest(){


            $id = $this->input->get('id');
            $where = ['id'=>$id];
            $this->load->model('Portal_model');;
            $bech = $this->Portal_model->first(['salt'],'hotspot_branch',$where);
            $accesskey =  $bech['salt'];
            $data['base_url'] = base_url();
            $data['accesskey'] = $accesskey;//$this->orginazation['accesskey'];
            $login = $this->load->view('hotspot/download/login.html',$data,true);
            $logout = $this->load->view('hotspot/download/logout.html',$data,true);
            $status = $this->load->view('hotspot/download/status.html',$data,true);
            $js = $this->load->view('hotspot/download/aes.js',[],true);
            $js2 = $this->load->view('hotspot/download/wifi.js',[],true);
            $js3 = $this->load->view('hotspot/download/jquery.min.js',[],true);
            $js4 = $this->load->view('hotspot/download/md5.js',[],true);
            $js5 = $this->load->view('hotspot/download/jquery-weui.min.js',[],true);
            $app_css = $this->load->view('hotspot/download/jquery-weui.min.css',[],true);
            $weui_css = $this->load->view('hotspot/download/weui.min.css',[],true);

            $path = "data/";
            $filename = $accesskey.".zip";
            $zip = new ZipArchive;
            $zip->open($path.$filename, ZipArchive::CREATE);
            $zip->addEmptyDir('hotspot');
            $zip->addFromString('hotspot/login.html', $login);
            $zip->addFromString('hotspot/logout.html', $logout);
            $zip->addFromString('hotspot/status.html', $status);
            $zip->addFromString('hotspot/aes.js', $js);
             $zip->addFromString('hotspot/wifi.js', $js2);
            $zip->addFromString('hotspot/jquery.min.js', $js3);
            $zip->addFromString('hotspot/md5.js', $js4);
            $zip->addFromString('hotspot/app.min.js', $js5);
            $zip->addFromString('hotspot/app.min.css', $app_css);
            $zip->addFromString('hotspot/ui.min.css', $weui_css);
            $zip->close();
            if(!file_exists($path.$filename)){
                exit("无法找到文件"); //即使创建，仍有可能失败。。。。
            }
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");
            header("Content-Type: application/force-download");
            header("Content-type: application/x-gzip");
            header("Content-Disposition: attachment; filename=".$accesskey.'_hotspot.zip');
            header("Content-Description: cloudshotspot Generated Data");
            header("X-Powered-By: Java");
            readfile(base_url().'/data/'.$filename);
        }

        public  function users(){
            $uid = $this->_organization['id'];
            $accesskey = $this->_organization['accesskey'];//getRequest()->getQuery('accesskey');
            /*var_dump($accesskey);*/
            $where = array('accesskey'=>$accesskey);
            $config_p = array('url'=>site_url('hotspot/users'),'table'=>'hotspot_users','per_page'=>10,'uri_segment'=>4);
            $this->load->model('Member_model');
            $offset = $this->uri->segment(4) ? $this->uri->segment(4) : 0;

            $data['page'] = $this->Member_model->feiyeconfig($config_p,$where);
            $query = $this->Member_model->getall($config_p['per_page'],$offset,'hotspot_users',$where,array('addtime'=>'DESC','id'=>'ASC'));

            $results = $query->result_array();

            $data = ['accesskey'=>$accesskey];
            $data['result'] = $results;


            $this->load->library('twig');
            $this->twig->display('hotspot/users.php',$data);
        }


        public function access(){
            /*$uid = $this->_organization['id'];
            $bid = $this->_organization['branch_id'];*/
            $accesskey = $this->_organization['accesskey'];
            $where = ['accesskey'=>$accesskey];
            $config_p = array('url'=>site_url('hotspot/access'),'table'=>'access_auth','per_page'=>10,'uri_segment'=>3);
            $this->load->model('Member_model');
            $offset = $this->uri->segment(3) ? $this->uri->segment(3) : 0;
            $data['page'] = $this->Member_model->feiyeconfig($config_p,$where);
            $query = $this->Member_model->getall($config_p['per_page'],$offset,'access_auth',$where,array('addtime'=>'DESC','id'=>'ASC'));
            $data['accesskey'] = $accesskey;
            $data['result'] =  $query->result_array();
            //var_dump($data);
            $this->load->library('twig');
            $this->twig->display('hotspot/access_log.php',$data);

        }

        public function wechat(){
            $uid = $this->_organization['id'];
            $accesskey = $this->_organization['accesskey'];//getRequest()->getQuery('accesskey');
            $branch_id = $this->_organization['branch_id'];
            $this->load->model('Portal_model');
            if($this->input->post()){
                $id = $this->input->post('id');
                $accesskey = $this->input->post('accesskey');
                $data = $this->input->post('data');
                $data['accesskey'] = $accesskey;
                $this->Portal_model->save( $data,'wifiapi',['uid'=>$uid,'id'=>$id]);
                echo json_encode(['status'=>'success']);
                exit();

            }

            $where = ["uid"=>$uid,'bid'=>$branch_id];
            $result =$this->Portal_model->first([],'wifiapi',$where);// $query->

            if(empty($result)){
                $newData = [
                    "uid"=>	$uid,
                    'bid'=>	$branch_id,
                    'accesskey'=>$accesskey,
                    'addtime'=>time()
                ];
                $result['id'] = $this->Portal_model->create('wifiapi',$newData,'id');
            }

            $data['result'] = $result;
            $data['accesskey'] = $accesskey;
            $this->load->library('twig');
            $this->twig->display('wechat/wifi.php',$data);

        }

        public function themes(){
            $uid = $this->_organization['id'];
            $bid = $this->_organization['branch_id'];
            $accesskey = $this->_organization['accesskey'];
            $_type = $this->input->get('token');

            if(!$_type) $_type = 1;

            $data['type'] = $_type;

            $where = array('type'=>$_type,'status'=>1);

            $this->load->model('Portal_model');

            $data['result'] = $this->Portal_model->get([],'themes',array('type'=>$_type));


            //查找当前节点激活主题
            switch ($_type) {
                case 1:
                    $_gets = array('normal_tid');
                    break;

                case 2:
                    $_gets = array('wechat_tid');

                    break;
                case 3:
                    $_gets = array('account_tid');

                    break;

                default:
                    $_gets = array('normal_tid');
                    break;
            }
            $where = ['id'=>$bid,'uid'=>$uid];
            $_active = $this->Portal_model->first($_gets,'hotspot_branch',$where);


            if(!empty($_active)){
                if($_type==1){
                    $data['active'] = $_active['normal_tid'];

                }

                if($_type==2){
                    $data['active'] = $_active['wechat_tid'];

                }

                if($_type==3){
                    $data['active'] = $_active['account_tid'];

                }


            }else{
                $data['active'] = '';
            }


            $data['accesskey'] = $accesskey;
            $this->load->library('twig');
            $this->twig->display('hotspot/themes.php',$data);
        }
        public function themes_update(){

            $id = $this->input->post('id');
            $type = $this->input->post('type');
            if(!in_array($type,[1,2,3])) exit();
            $uid = $this->_organization['id'];
            $bid = $this->_organization['branch_id'];
            $this->load->model('Portal_model');
            if($type==1) $prefix = 'normal_tid';
            if($type==2) $prefix = 'wechat_tid';
            if($type==3) $prefix = 'account_tid';

            $res = $this->Portal_model->save(array($prefix=>$id),'hotspot_branch',['id'=>$bid,'uid'=>$uid]);
            echo json_encode(array("status"=>"success","message"=>"ok"));

        }
        public function screen(){

            $bid = $this->_organization['branch_id'];
            $accesskey = $this->_organization['accesskey'];
            $where = array('accesskey'=>$accesskey);

            $config_p = array('url'=>site_url('hotspot/screen'),'table'=>'hotspot_slider','per_page'=>10,'uri_segment'=>4);
            $this->load->model('Member_model');
            $offset = $this->uri->segment(4) ? $this->uri->segment(4) : 0;
            $data['page'] = $this->Member_model->feiyeconfig($config_p,$where);
            $query = $this->Member_model->getall($config_p['per_page'],$offset,'hotspot_slider',$where,array('addtime'=>'DESC','id'=>'ASC'));
            $results = $query->result_array();
            $data = ['accesskey'=>$accesskey];
            $data['result'] = $results;
            $this->load->library('twig');
            $this->twig->display('hotspot/screen.php', $data);
        }
        public function screen_add(){
            $bid = $this->_organization['branch_id'];
            $accesskey = $this->_organization['accesskey'];
            if ($this->input->post()) {
                $data = $this->input->post('data');
                $data['accesskey'] = $accesskey;
                $data['addtime'] = time();
                $this->load->model('Portal_model');
                $this->Portal_model->create('hotspot_slider',$data);
                echo json_encode(['status'=>'success']);
                exit();
            }
            $data['accesskey'] = $accesskey;
            $this->load->library('twig');
            $this->twig->display('hotspot/screen_add.php',$data);
        }

        public function users_add(){
            $uid = $this->_organization['id'];
            $accesskey = $this->_organization['accesskey'];


            if($this->input->Post()){

                $data = $this->input->post('data');
                if(!empty($data['password'])){
                    $data['password'] = md5($data['password']);
                }
                $this->load->model('Portal_model');
                $data['accesskey'] = $accesskey;
                $data['start_time'] = strtotime($data['start_time']);
                $data['end_time'] = strtotime($data['end_time']);

                $res = $this->Portal_model->create('hotspot_users',$data);
                if($res){
                    echo json_encode(['status'=>'success']);
                }else{
                    echo json_encode(['status'=>'false']);
                }
                exit();
            }

            $data['accesskey'] = $accesskey;
            $this->load->library('twig');
            $this->twig->display('hotspot/user_add.php', $data);
        }
        public function usersupdate(){

            $id = $this->input->get_post('token');
            $uid = $this->_organization['id'];
            $accesskey = $this->_organization['accesskey'];

            $this->load->model('Portal_model');

            if($this->input->post()){

                $id =  $this->input->get_post('id');
                $data =  $this->input->get_post('data');
                if(!empty($data['password'])){
                    $data['password'] = md5($data['password']);
                }else{
                    unset($data['password']);
                }

                $data['start_time'] = strtotime($data['start_time']);
                $data['end_time'] = strtotime($data['end_time']);

                $res = $this->Portal_model->save($data,'hotspot_users',array('id'=>$id));

                if($res){
                    //_location('修改成功',site_url('member/users/'));
                    echo json_encode(['status'=>'success']);
                }else{
                    echo json_encode(['status'=>'false']);

                }
                exit();

            }
            $user = $this->Portal_model->first(["*"],'hotspot_users',array('id'=>$id));
            $data['user'] = $user;
            $data['accesskey'] = $accesskey;

            $this->load->library('twig');
            $this->twig->display('hotspot/users_modify.php', $data);

        }

        public function message(){

            $accesskey = $this->_organization['accesskey'];
            $where  = ['salt'=>	$accesskey];
            $config_p = array('url'=>site_url('hotspot/message'),'table'=>'message_code','per_page'=>10,'uri_segment'=>3);
            $this->load->model('Member_model');
            $offset = $this->uri->segment(3) ? $this->uri->segment(3) : 0;
            $data['page'] = $this->Member_model->feiyeconfig($config_p,$where);
            $query = $this->Member_model->getall($config_p['per_page'],$offset,'message_code',$where,array('addtime'=>'DESC','id'=>'ASC'));
            $data['result'] = $query->result_array();
            $data['accesskey'] = $accesskey;
            $this->load->library('twig');
            $this->twig->display('hotspot/message_log.php',$data);


        }
        public function screenupdate(){
            $bid = $this->_organization['branch_id'];

            $accesskey = $this->_organization['accesskey'];
            $id = $this->input->get('token');
            $this->load->model('Portal_model');
            if ($this->input->post()) {
                $data = $this->input->post('data');
                $id = $this->input->post('id');
                $res = $this->Portal_model->save($data,'hotspot_slider',['id'=>$id,'accesskey'=>$accesskey]);
                echo json_encode(['status'=>'success']);
                exit();
            }

            $_slider = $this->Portal_model->first([],'hotspot_slider',["id"=>$id,'accesskey'=>$accesskey]);

            $data['result'] = $_slider;
            $data['accesskey'] = $accesskey;

            $this->load->library('twig');
            $this->twig->display('hotspot/screen_update.php',$data);
        }
        public function screendel(){

            if(!$this->input->post()) exit();
            $id = $this->input->post('id');
            $this->load->model('Member_model');
            $where = ['id'=>$id];
            $res = $this->Member_model->delete($where,'hotspot_slider');

                echo json_encode(array('status' =>'success','message'=>'ok','res'=>$res));

        }

        public function banner(){

            $bid = $this->_organization['branch_id'];
            $accesskey = $this->_organization['accesskey'];
            $where = array('accesskey'=>$accesskey);
            $config_p = array('url'=>site_url('hotspot/screen'),'table'=>'hotspot_banner','per_page'=>10,'uri_segment'=>4);
            $this->load->model('Member_model');
            $offset = $this->uri->segment(4) ? $this->uri->segment(4) : 0;
            $data['page'] = $this->Member_model->feiyeconfig($config_p,$where);
            $query = $this->Member_model->getall($config_p['per_page'],$offset,'hotspot_banner',$where,array('addtime'=>'DESC','id'=>'ASC'));
            $results = $query->result_array();
            $data = ['accesskey'=>$accesskey];
            $data['result'] = $results;
            $this->load->library('twig');
            $this->twig->display('hotspot/banner.php', $data);
        }

        public function banner_add(){
            $bid = $this->_organization['branch_id'];
            $uid = $this->_organization['id'];
            $accesskey = $this->_organization['accesskey'];
            if ($this->input->post()) {
                $data = $this->input->post('data');
                $data['bid'] = $bid;
                $data['uid'] = $uid;
                $data['addtime'] = time();
                $this->load->model('Portal_model');
                $this->Portal_model->create('hotspot_banner',$data);
                echo json_encode(['status'=>'success']);
                exit();
            }

            $data['bid'] = $bid;
            $data['accesskey'] = $accesskey;
            $this->load->library('twig');
            $this->twig->display('hotspot/banner_add.php',$data);
        }
        public function bannerupdate(){
            $bid = $this->_organization['branch_id'];
            $uid = $this->_organization['id'];
            $accesskey = $this->_organization['accesskey'];
            $id = $this->input->get('token');
            $this->load->model('Portal_model');
            if ($this->input->post()) {
                $data = $this->input->post('data');
                $id = $this->input->post('id');
                $res = $this->Portal_model->save($data,'hotspot_banner',['id'=>$id,'accesskey'=>$accesskey,'bid'=>$bid]);
                echo json_encode(['status'=>'success']);
                exit();
            }

            $_slider = $this->Portal_model->first([],'hotspot_banner',["id"=>$id,'bid'=>$bid,'accesskey'=>$accesskey]);
            $data['bid'] = $bid;
            $data['result'] = $_slider;
            $data['accesskey'] = $accesskey;

            $this->load->library('twig');
            $this->twig->display('hotspot/banner_update.php',$data);
        }
        public function bannerdel(){

            if(!$this->input->post()) exit();
            $uid = $this->_organization['id'];
            $id = $this->input->post('id');
            $this->load->model('Member_model');
            $where = ['id'=>$id,'uid'=>$uid];
            $res = $this->Member_model->delete($where,'hotspot_banner');
            if($res)  echo json_encode(array('status' =>'success','message'=>'ok'));

        }
        public function themeset(){
            $bid = $this->_organization['branch_id'];
            $accesskey = $this->_organization['accesskey'];

            $where =['bid'=>$bid,'accesskey'=>$accesskey];
            $this->load->model('Portal_model');

            if($this->input->post()){

                $id = $this->input->post('id');
                $data = $this->input->post('data');
                $data['bid'] = $bid;


                if(!empty($id)){
                    $where['id'] = $id;
                    $res = $this->Portal_model->save($data,'themes_copyright',$where);
                }else{
                    $data['bid'] = $bid;
                    $data['accesskey'] = $accesskey;
                    $data['addtime'] = time();
                    $res = $this->Portal_model->create('themes_copyright',$data);
                }

                echo json_encode(['status'=>"success"]);
                exit;
            }

            $copyright = $this->Portal_model->first([],'themes_copyright',$where);

            if(!$copyright){
                $copyright = array('id'=>"",'company'=>"","number"=>'','type'=>"",'a_num'=>"",'b_num'=>"",'title'=>"");
            }
            $data['ret'] = $copyright;

            $data['bid'] = $bid;
            $data['accesskey'] = $accesskey;
            $this->load->library('twig');
            $this->twig->display('hotspot/themeset.php', $data);

        }

        public function init(){



            $bid = $this->_organization['branch_id'];
            $accesskey = $this->_organization['accesskey'];

            $where =['bid'=>$bid,'accesskey'=>$accesskey];

            $start_time = strtotime("-6 day");

            $end_time = time();
            $this->load->model('Portal_model');

            $where =[
                'and'=>[
                    'addtime[<]'=>$end_time,
                    'addtime[>]'=>$start_time,
                    'accesskey'=>$accesskey,
                    ]
                ];
            $data= $model->select('access_log',[
                'type','addtime'],$where);
        
            $date = [];

            $message = $model->first('hotspot_branch','message_total',['salt'=>$accesskey]);
            
            $date['message'] =  $message;
            $date['total'] = count($data);
            $date['wechat'] = 0;
            $date['normal'] = 0;
            
            for ($i=0; $i <= 1 ; $i++) { 

            /*  $date[$i]= ;*/
                $index= date('Y-m-d',strtotime(date('Y-m-d',$start_time)."+ $i day"));
                $date[$index]['total']= 0;
                $date[$index]['wechat']= 0;
                $date[$index]['normal']= 0;
                
                foreach ($data as $k => $v) {
                    if(date('Y-m-d',$v['addtime'])==$index){
                        switch ($v['type']) {
                            case '2':
                                $date['wechat'] = $date['wechat']+1;
                                $date[$index]['wechat']= $date[$index]['wechat'] + 1;
                                break;
                            
                            case '3':
                                $date['normal'] = $date['normal']+1;
                                $date[$index]['normal']= $date[$index]['normal'] + 1;
                                
                                break;

                            
                            
                        }
                        $date[$index]['total']= $date[$index]['total'] + 1;
                    }
                }


                

            }

            echo json_encode(['status'=>'success','data'=>$date]);
        }

 	}

    public function preview(){

        $accesskey = $this->input()->get_post('salt');


    }
 	
 	/* End of file Hotspot.php */
 


?>