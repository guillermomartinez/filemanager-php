<?php
namespace Php\Filemanager;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Filemanager
{
	private $config;
	public $mode = '';
	public $path = '';
	public $log = null;
	public $info = array("data"=>array(),"msg"=>"");

	public function __construct($extra=array()){
		$this->config = array(
			"doc_root" => "",
			"separator" => "userfiles",
			"debug" => true,
			"debugfile" => "log/filemanager.log",
			"security" => array(		        
		        "ext" => array("jpg","jpeg","gif","png","svg","txt","pdf","odp","ods","odt","rtf","doc","docx","xls","xlsx","ppt","pptx","csv","ogv","mp4","webm","m4v","ogg","mp3","wav","zip","rar"),
		    ),
		    "upload" => array(
		        "multiple" => true,
		        "number" => 5,
		        "overwrite" => false,
		        "size_max" => 10
		    ),
		  	"images" => array(
		        "images_ext" => array("jpg","jpeg","gif","png","svg"),
		        "resize" => array("enabled" => true,"maxWidth" => 1280,"maxHeight" => 1024)
		    ),
		);
		// if($this->config["doc_root"]=="") $this->config["doc_root"] = $_SERVER['DOCUMENT_ROOT'];
		if(isset($_SERVER['DOCUMENT_ROOT'])) $this->config['doc_root'] = $_SERVER['DOCUMENT_ROOT'];		
		if(count($extra)>0) $this->setup($extra);
		if($this->config['debug']){
			$this->log = new Logger('filemanager');
			$this->log->pushHandler(new StreamHandler($this->config['debugfile']));
		}
		// var_dump($this->config);
	}

	/**
	 * Cambia las configuraciones
	 * 
	 * @param array $extra Configuraciones a modificar
	 * @return void
	*/
	private function setup($extra){
		$this->config = array_replace_recursive($this->config,$extra);
	}

	/**
	 * Obtiene la ruta absoluta 
	 * 
	 * @return string
	*/
	public function getFullPath(){
		if($this->config['doc_root']=='')			
			return $this->config['separator'];
		else
			return $this->config['doc_root'].'/'.$this->config['separator'];
	}

	/**
	 * Valida la url path solo formato /directorio o /file.txt
	 * @param string $path 
	 * @return boolean
	 */
	public function validPath($path){
		if(preg_match_all("#^/{1}$|^/[a-z0-9A-Z]{1}([a-z0-9A-Z-._/])*(/|[a-z0-9A-Z-_]+[.]{1}[a-z0-9A-Z]{1,})+$#",$path) > 0){
			return true;
		}else{
			return false;
		}
	}

	/**
	* Todas las propiedades de un archivo o directorio
	* 
	* @param SplFileInfo $file Object of SplFileInfo
	* @param string $path Ruta de la carpeta o archivo
	* @return array|null Lista de propiedades o null si no es leible
	*/
	public function fileInfo($file,$path){	
		if($file->isReadable()){
			$item = array(
				"path" => '/'.$this->config['separator'].$path,
				"filename" => $file->getFilename(),
				"filetype" => $file->getExtension(),
				"filemtime" => $file->getMTime(),
				"filectime" => $file->getCTime(),
				"readable" => 0,
				"writable" => 0,
				"preview" => "",
				"properties" => array(
					"Size"=>"",
					"Height"=>"",
					"Width"=>"",
					"Date Modified"=>"",
					"filemtime"=>"",
					),
				"size" => $file->getSize(),
				"msg" => "",
				);
			if($file->isDir()){
				$item["filetype"] = '';
				$item['preview'] = '';
			}elseif($file->isFile()){
				$item['path'] = str_replace($item['filename'],'',$item['path']);
				// var_dump($item['path']);
				if($file->isWritable()==false)
					$item['writable'] = 1;			
			}
			// var_dump($item);
			return $item;
		}else{
			return ;
		}			
	}
	
	/**
	* Lista carpeta y archivos segun el path ingresado, no recursivo, ordenado por tipo y nombre
	* 
	* @param string $path Ruta de la carpeta o archivo
	* @return array|null Listado de archivos o null si no existe
	*/
	public function getAllFiles($path){
			
		$fullpath = $this->getFullPath().$path;
		// var_dump(__DIR__);
		// var_dump($fullpath);
		if(file_exists($fullpath)){
			$file = new \SplFileInfo($fullpath);
			if($file->isDir()){
				$r = array();
				$finder = new Finder();
				$directories = $finder->depth('0')->sortByType()->sortByName()->in($fullpath);
				foreach ($directories as $key => $directorie) {
					$t = $this->fileInfo($directorie,$path);
					if($t) $r[] = $t;
				}
				return $r;
			}elseif($file->isFile()){					
				$t = $this->fileInfo($file,$path);
				if($t){
					return $t;
				}else{
					$this->error("Archivo no leible");
					$this->log(__METHOD__." - Archivo no leible - $fullpath");
					return;
				} 
			}elseif($file->isLink()){
				$this->error("Archivo no permitido");
				$this->log(__METHOD__." - path desconocido - $fullpath");
				return ;
			}
		}else{
			$this->error("No existe el archivo");
			$this->log(__METHOD__." - No existe el archivo - $fullpath");
			return ;
		}

		
	}

	/**
	 * Agrega mensaje a cada peticion si se produce un error
	 * @param string $string 
	 * @return void
	 */
	public function error($string) {
		$this->info['msg'] = $string;		
	}

	/**
	 * Si debug active permite logear informacion
	 * @param string $string Texto a mostrar
	 * @return void
	 */
	public function log($string) {
		if($this->config['debug'])	$this->log->addError($string);
	}

	/**
	 * Clear var
	 * @param string $var 
	 * @return string
	 */
	private function sanitize($var) {
		$sanitized = strip_tags($var);
		$sanitized = str_replace('http://', '', $sanitized);
		$sanitized = str_replace('https://', '', $sanitized);
		$sanitized = str_replace('../', '', $sanitized);
		return $sanitized;
	}

	/**
	 * Remove extension
	 * @param string $filename 
	 * @return string
	 */
	public function removeExtension($filename)
	{
		return substr($filename,0,strrpos( $fileName, '.' ) ) ;
	}
	public function getMaxUploadFileSize() {
			
		$upload_max_filesize =  ini_get('upload_max_filesize');
		$post_max_size =  ini_get('post_max_size');
		$size_max = min($upload_max_filesize, $post_max_size);
		// var_dump($upload_max_filesize, $post_max_size);
		$this->log(__METHOD__.": $size_max MB");

		return $size_max;
	}
	public function upload($file,$path){
		if($file->getClientSize() > ($this->getMaxUploadFileSize() * 1024 * 1024) ){
			$this->error("file size no permitido server: ".$file->getClientSize());
			$this->log(__METHOD__." - file size no permitido server: ".$file->getClientSize());
			return ;
		}elseif($file->getClientSize() > ($this->config['upload']['size_max'] * 1024 * 1024) ){
			$this->error("file size no permitido: ".$file->getClientSize());
			$this->log(__METHOD__." - file size no permitido: ".$file->getClientSize());
			return ;
		}else{
			$dir = $this->getFullPath().$path;
			var_dump($dir);
			$namefile = $file->getClientOriginalName();
			$nametemp = $namefile;
			$ext = $file->getClientOriginalExtension();
			$i=0;
			while(true){
				$pathnametemp = $dir.$nametemp;
				if(file_exists($pathnametemp)){
					$i++;
					$nametemp = $this->removeExtension( $nametemp ) . '_' . $i . '.' . $ext ;
				}else{
					break;
				}
			}
			$file->move($dir,$nametemp);
			return $nametemp;		
		}
		
	}

	/**
	 * Upload all files
	 * @param array $files 
	 * @param string $path 
	 * @return array|null
	 */
	public function uploadAll($files,$path){
		$n = count($files);
		if( $n > 0 ){
			$r = array();
			$i = 0;
			foreach ($files as $key => $file) {
				$t = $this->upload($file,$path);
				if( $t ){
					$i++;
					$r[] = $t;
				}
			}
			// $this->error("File uploaded: $i of $n");
			return $r;			
		}else{
			return ;
		}		
	}

	/**
	 * Ejecuta todo las configuraciones
	 * @return JsonResponse
	 */
	public function run(){
		$request = Request::createFromGlobals();
		// var_dump($request->getMethod());
		// var_dump($request->query->all());
		// var_dump($request->query->post('accion'));
		$this->mode = $this->sanitize($request->request->get('mode'));
		$path = $this->sanitize($request->request->get('path'));
		// var_dump($this->validPath($path));
		// var_dump($this->getFullPath());
		$jsonResponse = new JsonResponse;
		if($this->validPath($path)==false){

		}else{
			// var_dump($request->getMethod());
			if($request->getMethod()=='POST'){
				if($this->mode==='getfolder'){
					$folders = $this->getAllFiles($path);
					if(is_array($folders))				
						$this->info['data'] = $folders;
				}elseif($this->mode==='getinfo'){
					$folders = $this->getAllFiles($path);
					if(is_array($folders))		
						$this->info['data'] = $folders;		
				}elseif($this->mode==='uploadfile' ){
					$files = $this->uploadAll($request->files->get('archivo'));
					if(is_array($files))		
						$this->info['data'] = $files;		

				}elseif($this->mode==='renamefile'){
					
				}elseif($this->mode==='deletefile'){
					
				}
			}
		}
		$jsonResponse->setData($this->info);
		return $jsonResponse->sendContent();
	}
}
?>