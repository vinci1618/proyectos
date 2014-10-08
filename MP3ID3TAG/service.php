<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Example
 *
 * @package     CodeIgniter
 * @subpackage  Rest Server
 * @category    Controller
 * @author      Jaime Mendoza
*/

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';
require APPPATH.'/libraries/getid3/getid3.php';
require APPPATH.'/libraries/getid3/write.php';


class Service extends REST_Controller
{

    
    public function events_get()
    {
    	$e = new Events();
    	
    	$data['events'] = $e->get()->all_to_array();    	                 
        $this->response($data, 200); // 200 being the HTTP response code
    }
    
    public function transaction_post(){
    	$this->load->library('session');
    	
    	if($this->post('name') && $this->post('mail') && $this->post('transaction')){
    			

    		$u = new User();
    		$u->name =  $this->post('name');
    		$u->mail =  $this->post('mail');
    		$u->transaction_id = $this->post('transaction');
    		
    		if($u->save()){
    			
    			
    			$userData = array(
    					'id'  => $u->id,
    					'name'     => $u->name,
    					'mail'     => $u->mail,
    					'transaction_id' =>$u->transaction_id,
    					'login' => TRUE
    			);
    			if(!$this->session->userdata('id')){
    				 
    				$this->session->set_userdata($userData);
    				$data['success'] = true;
    				$this->response($data, 200); // 200 being the HTTP response code
    			}else{
    				
    				$data['error'] = 'session already exists';
    				$this->response($data, 400); // 200 being the HTTP response code
    			}

    			
    		}else{
    			
    			$data['error'] = 'save user';
    			$this->response($data, 400); // 200 being the HTTP response code
    			
    		}
    		
    		
    	}else{
    		
    		$data['error'] = 'info';
    		$this->response($data, 400); // 200 being the HTTP response code
    		
    	}
    	   
    	
    	   
    	
    }
    public function index_post(){
    	$this->load->library('session');    	 
    	$this->load->helper('string');
    	$this->load->helper('file');
    	$this->load->helper('zipfile');
    	 
    	if($this->session->userdata('transaction_id')){
    	
	    	$basePath = str_replace('system/','',BASEPATH);
			$data64 = substr($this->input->post('imgBase64'), strpos($this->input->post('imgBase64'), ",") + 1);
	    	$decodedData = base64_decode($data64);
	    	
	    	$nameString = $this->session->userdata('transaction_id').'.png';
	    	$fileUrl = $basePath."dynamic/cover-art/".$nameString;
	    	    	
	    	if (write_file($fileUrl, $decodedData, 'wb')) {
	    		
	
	    	    		
	    		 
	    		$tracks = get_filenames('statics/album/');
	    		
	    		
	    		$TextEncoding = 'UTF-8';
	    		$tagwriter = new getid3_writetags;
	    		 
	    		 
	    		 
	    		//$tagwriter->tagformats = array('id3v1', 'id3v2.3');
	    		$tagwriter->tagformats = array('id3v2.3');
	    		
	    		// set various options (optional)
	    		$tagwriter->overwrite_tags = true;
	    		//$tagwriter->overwrite_tags = false;
	    		$tagwriter->tag_encoding = $TextEncoding;
	    		$tagwriter->remove_other_tags = true;
	    		
	    		// populate data array
				$cover = $nameString;
		
	    		    		 
	    		 
	    		$TagData['attached_picture'][]=array(
	    				'picturetypeid'=>2, // Cover. More: module.tag.id3v2.php -> function APICPictureTypeLookup
	    				'description'=>'cover', // text field
	    				'mime'=>'image/jpeg', // Mime type image
	    				'data'=> file_get_contents($basePath."dynamic/cover-art/".$cover) // Image data; not the file name    				
	    		);
	    		 
	    		foreach ($tracks as $key => $track) {
	    				
	    			$tagwriter->filename = $basePath.'statics/album/'.$track;
	    			$tagwriter->tag_data = $TagData;
	    		
	    			// write tags
	    			if ($tagwriter->WriteTags()) {
	    				//echo 'Successfully wrote tags<br>';
	    		
	    				$tracks[$key] = 'statics/album/'.$track;
	    		
	    		
	    				if (!empty($tagwriter->warnings)) {
	    					//echo 'There were some warnings:<br>'.implode('<br><br>', $tagwriter->warnings);
	    				}
	    			} else {
	    				//echo 'Failed to write tags!<br>'.implode('<br><br>', $tagwriter->errors);
	    			}
	    				
	    		}
	    		
	    		
	    		$zipFileName = str_replace('.png','', $cover);
	    		
	    		
				$result = create_zip($tracks, $basePath.'dynamic/processed/'.$zipFileName.'.zip');
	    		    		
	    		if($result){    		
	
	    			$data['success'] = 'true';
	    			$data['url'] = base_url('donwload/');
	    			
	    		}else{
	    			$data['error'] = 'zip no created';    			 
	    			
	    		}	    		   
	    	}else{    		
	    		 $data['error'] = 'no file created';    		   	
	    	}
    	}else{
    		
    		$data['error'] = 'no session';
    		
    	}
	    	        
    	 
    	$this->response($data, 200); // 200 being the HTTP response code
    	   
    }
    
}