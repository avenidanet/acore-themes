<?php
/**
 * ACM OneFile (themes) Modulo para crear sitios.
 *
 * @author Brian Salazar [Avenidanet]
 * @link http://www.avenidanet.com
 * @copyright Brian Salazar 2006-2013
 * @license http://mit-license.org
 *
 */

include "config.php";

class acThemes extends AbstractModule{

	private $page_data = '';
	private $page_name = '';
	private $app;
	private $children;
	
	public function init($acore){
		$this->app = $acore;
		
		if(is_dir($this->acore->theme)){
			
			//Get url vars
			$url = explode("?", $_SERVER['REQUEST_URI']);
			$url = str_replace($this->acore->folder, '', $url);
			$vars = explode("/",$url[0]);

			$vars = array_filter($vars);
			$vars = array_values($vars);
			
			//If exist page child
			if(isset($vars[1])){
				$parent = $this->get_page($vars[0]);
				if(!empty($parent)){
					$child = $this->get_page($vars[1]);
					if($child['parent'] == $parent['id']){
						$this->page_name = $vars[1];
					}
				}
			//If exist page	
			}elseif(isset($vars[0])){
				$this->page_name = $vars[0];
			}else{
				$this->page_name = 'index';
			}
		
			$this->get_functions();
			$this->get_template();
		}else{
			A::error("themes", "Theme not found :(");
		}		
	}
	
	private function get_file($file,$option = ''){
		if(file_exists($this->acore->theme."/".$file.'.php')){
			require_once $this->acore->theme."/".$file.'.php';
		}elseif($option !=''){
			if(file_exists($this->acore->theme."/".$option.'.php')){
				require_once $this->acore->theme."/".$option.'.php';
			}
		}
	}

	private function get_template(){
		if($this->page_name != ''){
			
			//Search file page name
			$file = $this->acore->theme."/".$this->page_name.".php";
			
			if(file_exists($file)){
				require_once $file;
			}else{
				$this->page_data = $this->get_page($this->page_name);
				if(!empty($this->page_data)){
					$this->children = $this->get_children($this->page_data[id]);
					if(empty($this->children)){
						$this->get_file('page', 'index');
					}else{
						$this->page_data['children'] = $this->children;
						$this->get_file('category', 'page');
					}
				}else{
					$this->get_file('404', 'index');
				}
			}
			
		}else{
			$this->get_file('404', 'index');
		}
	}
	
	private function get_functions(){
		$this->get_file('functions');
	}

	/*
	 * Page
	 */
	public function __call($name,$params){
		$property = str_replace("page_", "", $name);
		if (array_key_exists($property, $this->page_data)) {
			echo $this->page_data[$property];
		}
	}
    public function __get($name) {
    	$property = str_replace("page_", "", $name);
		if (array_key_exists($property, $this->page_data)) {
			return $this->page_data[$property];
		}
    }
	
	/*
	 * Template
	 */
	public function header(){
		$this->get_file('header');
	}
	public function footer(){
		$this->get_file('footer');
	}
	public function theme_url(){
		return $this->acore->theme;
	}
	public function theme_name(){
		return $this->acore->name;
	}


	/*
	 * Model
	 */
	private function get_page($page){
		if(is_int($page)){
			$result = $this->model->querySelect("ac_pages",'*','id = :id',array('id'=>$page));
		}else{
			$result = $this->model->querySelect("ac_pages",'*','url = :url',array('url'=>$page));
		}
		
		if(!empty($result)){
			$properties = $this->model->querySelect("ac_properties",'*','page_id = :id',array('id'=>$result[0]['id']));
			if(!empty($properties)){
				foreach ($properties as $property){
					$result[0][$property['property']] = $property['value'];
				}
			}
			return $result[0];
		}else{
			return 0;
		}
	}
	
	private function get_children($parent){
		$result = $this->model->querySelect("ac_pages",'*','parent = :parent',array('parent'=>$parent));
		if(!empty($result)){
			return $result;
		}else{
			return 0;
		}
	}
}