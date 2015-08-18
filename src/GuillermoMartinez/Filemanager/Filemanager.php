<?php
namespace GuillermoMartinez\Filemanager;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Gregwar\Image\Image as Imagen;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Filemanager
{
	/**
	 * configurate options
	 * 
	 * @var [array]
	 */
	private $config;

	/**
	 * actions = getfolder|uploadfile|newfolder|renamefile|deletefile|download
	 * 
	 * @var string
	 */
	public $action = '';

	/**
	 * path directory to search
	 * 
	 * @var string
	 */
	public $path = '';

	/**
	 * instance of Logger
	 * 
	 * @var Logger
	 */
	public $log = null;

	/**
	 * data of return
	 * data=[fileDetails|stringResult]
	 * 1=ok , 0=warning
	 * // query="BE_UPLOADALL_UPLOADS %s / %s" , params=[1, 2]
	 * 
	 * @var array
	 */
	private $info = array(
		"data"=>array(),
		"status"=>1,
		"msg"=>array("query"=>"","params"=>array())
		);

	/**
	 * details of file
	 * 
	 * @var array
	 */
	private $fileDetails = array(
		"urlfolder" => '',
		"filename" => "",
		"filetype" => "",
		"isdir" => false,
		"lastmodified" => "",
		"previewfull" => "",				
		"preview" => "",				
		"size" => "",
		);

	/**
	 * Create new Filemanager
	 * 
	 * @param array $extra overwrites settings
	 */
	public function __construct($extra=array()){
		$this->config = array(
			"doc_root" => "",
			"url" => "/",
			"source" => "userfiles", // Relative the doc root
			"debug" => false,
			"debugfile" => "log/filemanager.log",
			"ext" => array("jpg","jpeg","gif","png","svg","txt","pdf","odp","ods","odt","rtf","doc","docx","xls","xlsx","ppt","pptx","csv","ogv","mp4","webm","m4v","ogg","mp3","wav","zip","rar"),
			"upload" => array(
				"number" => 5,
				"overwrite" => false,
				"size_max" => 10
				),
			"images" => array(
				"images_ext" => array("jpg","jpeg","gif","png"),
				"resize" => array("thumbWidth" => 120,"thumbHeight" => 90)
				),
			"type_file" => null,
			);		
		if(isset($_SERVER['DOCUMENT_ROOT'])) $this->config['doc_root'] = $_SERVER['DOCUMENT_ROOT'];		
		if(count($extra)>0) $this->setup($extra);
		if($this->config['type_file']=='images'){
			$this->config['ext'] = $this->config['images']['images_ext'];
		}
		if($this->config['debug']){
			$this->log = new Logger('filemanager');
			$this->log->pushHandler(new StreamHandler($this->config['debugfile']));
		}		
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
			return $this->config['source'];
		else
			return $this->config['doc_root'].'/'.$this->config['source'];
	}

	/**
	 * Valida la url path solo formato /directorio/ o /file.txt
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
	 * Valida nombre de archivo
	 * @param string $filename 
	 * @return boolean
	 */
	public function validNameFile($filename,$ext=true){
		$filename = trim($filename);
		if($filename!="." && $filename!=".." && $filename!=" " && preg_match_all("#^[a-zA-Z0-9-_.\s]+$#",$filename) > 0){
			if($ext) return $this->validExt($filename);
			else return true;
		}else{
			return false;
		}
	}

	/**
	 * Valida extension de archivos
	 * @param string $filename 
	 * @return boolean
	 */
	public function validExt($filename){
		$exts = $this->config['ext'];
		$ext = $this->getExtension($filename);
		if(array_search($ext,$exts)===false){
			return false;
		}else{
			return true;
		}


	}

	/**
	 * Agrega mensaje a cada peticion si se produce un error
	 * @param array $data 
	 * @return void
	 */
	public function setInfo($data=array()) {
		if(isset($data['data'])) $this->info['data'] = $data['data'];
		if(isset($data['status'])) $this->info['status'] = $data['status'];
		if(isset($data['msg'])) $this->info['msg'] = $data['msg'];
		
	}

	/**status
	 * Si debug active permite logear informacion
	 * @param string $string Texto a mostrar
	 * @return void
	 */
	public function _log($string) {
		$this->log->addError($string);
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
		$sanitized = str_replace('./', '', $sanitized);
		return $sanitized;
	}
	
	/**
	 * Clear name folder
	 * @param string $var 
	 * @return string
	 */
	private function sanitizeNameFolder($var) {
		$sanitized = strip_tags($var);
		$sanitized = str_replace('.', '', $sanitized);
		return $sanitized;
	}

	/**
	 * Remove extension
	 * @param string $filename 
	 * @return string
	 */
	public function removeExtension($filename)
	{
		return substr($filename,0,strrpos( $filename, '.' ) ) ;
	}

	/**
	 * Obtiene la extensión de un nombre de archivo
	 * @param string $nameFile 
	 * @return string
	 */
	public function getExtension($nameFile){
		$extension = substr( $nameFile, ( strrpos($nameFile, '.') + 1 ) ) ;
		$extension = strtolower( $extension ) ;
		return $extension;
	}

	/**
	 * Obtiene el tamaño maximo permitido por el servidor
	 * @return int
	 */
	public function getMaxUploadFileSize() {

		$upload_max_filesize =  ini_get('upload_max_filesize');
		$post_max_size =  ini_get('post_max_size');
		$size_max = min($upload_max_filesize, $post_max_size);

		return $size_max;
	}
	
	/**
	 * Limpia el nombre de archivo
	 * @param string $namefile 
	 * @return string
	 */
	public function clearNameFile($namefile){
		$namefile = strip_tags($namefile);
		$namefile = trim($namefile);
		$buscar = array("á","é","í","ó","ú","ñ","Ñ","Á","É","Í","Ó","Ú","ü","Ü");
		$reemplazar = array("a","e","i","o","u","n","n","a","e","i","o","u","u","U");
		$namefile = str_replace($buscar,$reemplazar,$namefile);
		$namefile = preg_replace("/[\s]+/", '-', $namefile);
		$namefile = preg_replace("/[^a-zA-Z0-9._-]/", '', $namefile);
		$namefile = strtolower($namefile);
		return $namefile;
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
			$item = $this->fileDetails;
			$item["filename"] = $file->getFilename();
			$item["filetype"] = $file->getExtension();
			$item["lastmodified"] = $file->getMTime();
			$item["size"] = $file->getSize();
			
			if($file->isDir()){
				$item["filetype"] = '';
				$item["isdir"] = true;
				$item["urlfolder"] = $path.$item["filename"].'/';
				$item['preview'] = '';
			}elseif($file->isFile()){
				$thumb =  $this->createThumb($file,$path);
				if($thumb){
					$item['preview'] = $this->config['url'].$this->config['source'].'/_thumbs'.$path.$thumb;
				}								
				$item['previewfull'] = $this->config['url'].$this->config['source'].$path.$item["filename"];
			}
			return $item;
		}else{
			return ;
		}			
	}

	/**
	 * Crea la miniatura de imagenes
	 * @param UploadedFile $file 
	 * @param string $path 
	 * @return string Nombre del nuevo archivo
	 */
	public function createThumb($file,$path){
		$ext = $file->getExtension();
		$search = $this->config["images"]["images_ext"];
		if(array_search($ext, $search) !== false){
			$fullpath = $this->getFullPath().$path;
			$fullpaththumb = $this->getFullPath().'/_thumbs'.$path;
			$filename = $file->getFilename();
			$filename_new = $this->removeExtension($filename).'-'.$this->config['images']['resize']['thumbWidth'].'x'.$this->config['images']['resize']['thumbHeight'].'.'.$file->getExtension();
			$fullpaththumb_name = $this->getFullPath().'/_thumbs'.$path.$filename_new;
			if( $this->config['debug'] ) $this->_log(__METHOD__." - $fullpaththumb");
			$filethumb = new Filesystem;
			if($filethumb->exists($fullpaththumb_name) == false){			
				if( $this->config['debug'] ) $this->_log(__METHOD__." - ".$fullpaththumb_name);
				if($filethumb->exists($fullpaththumb) == false){
					$filethumb->mkdir($fullpaththumb);
				}								
				Imagen::open($fullpath.$file->getFilename())
					->zoomCrop($this->config['images']['resize']['thumbWidth'],$this->config['images']['resize']['thumbHeight'])
					->save($fullpaththumb_name);

			}
			return $filename_new;
		}else{
			if( $this->config['debug'] ) $this->_log(__METHOD__." - $ext");
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
		if(file_exists($fullpath)){
			$file = new \SplFileInfo($fullpath);
			if($file->isDir()){
				$r = array();
				// if($path != "/") $r[] = $this->folderParent($path);
				$finder = new Finder();
				$directories = $finder->notName('_thumbs')->notName('web.config')->notName('.htaccess')->depth(0)->sortByType();
				
				// $directories = $directories->files()->name('*.jpg');
				$directories = $directories->in($fullpath);
				foreach ($directories as $key => $directorie) {
					$namefile = $directorie->getFilename();
					if($directorie->isDir() && $this->validNameFile($namefile,false)){
						$t = $this->fileInfo($directorie,$path);
						if($t) $r[] = $t;
					}elseif($directorie->isFile() && $this->validNameFile($namefile)){					
						$t = $this->fileInfo($directorie,$path);
						if($t) $r[] = $t;
					}					
				}
				return $r;
			}elseif($file->isFile()){					
				$t = $this->fileInfo($file,$path);
				if($t){
					return $t;
				}else{					
					$result = array("query"=>"BE_GETFILEALL_NOT_LEIBLE","params"=>array());
					$this->setInfo(array("msg"=>$result));
					if( $this->config['debug'] ) $this->_log(__METHOD__." - Archivo no leible - $fullpath");
					return;
				} 
			}elseif($file->isLink()){
				$result = array("query"=>"BE_GETFILEALL_NOT_PERMITIDO","params"=>array());
				$this->setInfo(array("msg"=>$result));
				if( $this->config['debug'] ) $this->_log(__METHOD__." - path desconocido - $fullpath");
				return ;
			}
		}else{
			$result = array("query"=>"BE_GETFILEALL_NOT_EXISTED","params"=>array());
			$this->setInfo(array("msg"=>$result));
			if( $this->config['debug'] ) $this->_log(__METHOD__." - No existe el archivo - $fullpath");
			return ;
		}
	}

	/**
	 * Mueve un arcivo subido
	 * @param UploadedFile $file 
	 * @param string $path 
	 * @return SplFileInfo|null
	 */
	public function upload($file,$path){
		if( $this->validExt($file->getClientOriginalName())){
			if($file->getClientSize() > ($this->getMaxUploadFileSize() * 1024 * 1024) ){
				
				$result = array("query"=>"BE_UPLOAD_FILE_SIZE_NOT_SERVER","params"=>array($file->getClientSize()));
				$this->setInfo(array("msg"=>$result));

				if( $this->config['debug'] ) $this->_log(__METHOD__." - file size no permitido server: ".$file->getClientSize());
				return ;
			}elseif($file->getClientSize() > ($this->config['upload']['size_max'] * 1024 * 1024) ){

				$result = array("query"=>"BE_UPLOAD_FILE_SIZE_NOT_PERMITIDO","params"=>array($file->getClientSize()));
				$this->setInfo(array("msg"=>$result));

				if( $this->config['debug'] ) $this->_log(__METHOD__." - file size no permitido: ".$file->getClientSize());
				return ;
			}else{
				if($file->isValid()){
					$dir = $this->getFullPath().$path;
					$namefile = $file->getClientOriginalName();
					$namefile = $this->clearNameFile($namefile);
					$nametemp = $namefile;
					if( $this->config["upload"]["overwrite"] ==false){
						$ext = $this->getExtension($namefile);
						$i=0;
						while(true){
							$pathnametemp = $dir.$nametemp;
							if(file_exists($pathnametemp)){
								$i++;
								$nametemp = $this->removeExtension( $namefile ) . '_' . $i . '.' . $ext ;
							}else{
								break;
							}
						}
					}
					
					$file->move($dir,$nametemp);
					$file = new  \SplFileInfo($dir.$nametemp);
					return $file;		
				}
				
			}
		}else{
			if( $this->config['debug'] ) $this->_log(__METHOD__." - file extension no permitido: ".$file->getExtension());
		}
	}

	/**
	 * Upload all files
	 * @param array $files 
	 * @param string $path 
	 * @return array|null
	 */
	public function uploadAll($files,$path){
		if( is_array($files) && count($files) > 0 ){
			$n = count($files);
			if( $n <= $this->config['upload']['number'] ){
				$res = array();
				$notresult = array();
				foreach ($files as $key => $value) {
					$file = $this->upload($value,$path);
					if( $file ){					
						$this->createThumb($file,$path);
						$res[] = $file->getFilename();
					}else{
						$notresult[] = $value->getClientOriginalName();
					}
				}
				$r = '';
				$n2 = count($res);			
				$result = array("query"=>"BE_UPLOADALL_UPLOADS %s / %s","params"=>array($n2,$n));
				if(count($notresult)>0){
					$result['query'] = $result['query'].' | BE_UPLOADALL_NOT_UPLOADS '; 
					$i=0;
					$n = count($notresult);
					foreach ($notresult as $value) {
						$result['query'] = $result['query'] . ' %s';
						if( $n - 1 > $i ){
							$result['query'] = $result['query'] . ',';
							$value = $value.',';
						}
						$result['params'][] = $value;
						$i++;
						
					}
					$this->setInfo(array("status"=>0));
				}				
				$this->setInfo(array("msg"=>$result));
				return $res;
			}else{
				$result = array("query"=>"BE_UPLOAD_MAX_UPLOAD %s MB","params"=>array($this->config['upload']['number']));
				$this->setInfo(array("msg"=>$result,"status"=>0));				
			}
		}else{
			return ;
		}		
	}

	/**
	 * Renombra una carpeta
	 * @param string $namefile 
	 * @param string $path 
	 * @return boolean
	 */
	public function newFolder($namefile,$path){
		$fullpath = $this->getFullPath().$path;
		$namefile = $this->clearNameFile($namefile);
		$namefile = $this->sanitizeNameFolder($namefile);
		$dir = new Filesystem;
		if($dir->exists($fullpath.$namefile)){
			$result = array("query"=>"BE_NEW_FOLDER_EXISTED %s","params"=>array($path.$namefile));
			$this->setInfo(array("msg"=>$result,"status"=>0));
			if( $this->config['debug'] ) $this->_log(__METHOD__." - Ya existe: ".$path.$namefile);
			return false;
		}else{
			$dir->mkdir($fullpath.$namefile);
			$result = array("query"=>"BE_NEW_FOLDER_CREATED %s","params"=>array($path.$namefile));
			$this->setInfo(array("msg"=>$result,"data"=>array( "path" => $path, "namefile" => $namefile )));
			return true;
		}
	}

	/**
	 * Borra un archivo
	 * @param string or array $namefile 
	 * @param string $path 
	 * @return void
	 */
	public function delete($namefiles,$path){	
		if(is_string($namefiles)){
			$namefile = $this->sanitize($namefiles);
			$fullpath = $this->getFullPath().$path;
			$namefile = $this->clearNameFile($namefile);
			$file = new Filesystem;
			if($this->validNameFile($namefile,false) && $file->exists($fullpath.$namefile)){
				if( $this->config['debug'] ) $this->_log('$fullpath.$namefile - '.$fullpath.$namefile);
				if(is_dir($fullpath.$namefile)){
					$file->remove($fullpath.$namefile);
					$file->remove($this->getFullPath().'/_thumbs'.$path.$namefile);
					$result = array("query"=>"BE_DELETE_DELETED","params"=>array());
					$this->setInfo(array("msg"=>$result));
				}elseif($this->validNameFile($namefile) && is_file($fullpath.$namefile)){
					$file2 = new \SplFileInfo($fullpath.$namefile);				
					$filename = $file2->getFilename();
					$filename_new = $this->removeExtension($filename).'-'.$this->config['images']['resize']['thumbWidth'].'x'.$this->config['images']['resize']['thumbHeight'].'.'.$file2->getExtension();
					$fullpaththumb_name = $this->getFullPath().'/_thumbs'.$path.$filename_new;
					$file->remove($fullpaththumb_name);
					$file->remove($fullpath.$namefile);
					$result = array("query"=>"BE_DELETE_DELETED","params"=>array());
					$this->setInfo(array("msg"=>$result));
				}else{
					if( $this->config['debug'] ) $this->_log('$fullpath.$namefile - '.$fullpath.$namefile);
					$result = array("query"=>"BE_DELETE_NOT_EXIED","params"=>array());
					$this->setInfo(array("msg"=>$result, "status"=> 0));
				}
			}else{
				if( $this->config['debug'] ) $this->_log('$fullpath.$namefile - '.$fullpath.$namefile);
				$result = array("query"=>"BE_DELETE_NOT_EXIED","params"=>array());
				$this->setInfo(array("msg"=>$result, "status"=> 0));
			}
		}elseif(is_array($namefiles)){
			if(count($namefiles)>0){
				$fullpath = $this->getFullPath().$path;
				$data = array();
				foreach ($namefiles as $key => $namefile) {
					$file = new Filesystem;
					if($this->validNameFile($namefile,false) && $file->exists($fullpath.$namefile)){
						if( $this->config['debug'] ) $this->_log('$fullpath.$namefile - '.$fullpath.$namefile);
						if(is_dir($fullpath.$namefile)){
							$file->remove($fullpath.$namefile);
							$file->remove($this->getFullPath().'/_thumbs'.$path.$namefile);
							$data[] = array("status"=>1,"namefile"=>$namefile,"query"=>"BE_DELETE_DELETED","params"=>array());
						}elseif($this->validNameFile($namefile) && is_file($fullpath.$namefile)){
							$file2 = new \SplFileInfo($fullpath.$namefile);				
							$filename = $file2->getFilename();
							$filename_new = $this->removeExtension($filename).'-'.$this->config['images']['resize']['thumbWidth'].'x'.$this->config['images']['resize']['thumbHeight'].'.'.$file2->getExtension();
							$fullpaththumb_name = $this->getFullPath().'/_thumbs'.$path.$filename_new;
							$file->remove($fullpaththumb_name);
							$file->remove($fullpath.$namefile);
							$data[] = array("status"=>1,"namefile"=>$namefile,"query"=>"BE_DELETE_DELETED","params"=>array());
						}else{
							$data[] = array("status"=>0,"namefile"=>$namefile,"query"=>"BE_DELETE_NOT_EXIED","params"=>array());
						}
					}else{
						$data[] = array("status"=>0,"namefile"=>$namefile,"query"=>"BE_DELETE_NOT_EXIED","params"=>array());
					}
				}
				$this->setInfo(array("data"=>$data));
			}else{
				$result = array("query"=>"BE_DELETE_NOT_EXIED","params"=>array());
				$this->setInfo(array("msg"=>$result, "status"=> 0));
			}
		}
	}

	/**
	 * Renombra un archivo
	 * @param string $nameold 
	 * @param string $namenew 
	 * @param string $path 
	 * @return void
	 */
	public function rename($nameold,$namenew,$path){		
		if($this->validNameFile($nameold,false) && $this->validNameFile($namenew,false)){
			$fullpath = $this->getFullPath().$path;
			$nameold = $this->clearNameFile($nameold);
			$namenew = $this->clearNameFile($namenew);
			
			$file = new Filesystem;
			if($file->exists($fullpath.$nameold)){
				if( $this->config['debug'] ) $this->_log('$fullpath.$nameold - '.$fullpath.$nameold);
				if(is_dir($fullpath.$nameold)){
					$namenew = $this->sanitizeNameFolder($namenew);
					if($file->exists($fullpath.$namenew)==false){
						$file->rename($fullpath.$nameold,$fullpath.$namenew);
						$result = array("query"=>"BE_RENAME_MODIFIED","params"=>array());
						$this->setInfo(array("msg"=>$result,"data"=>array("namefile" => $namenew )));

					}else{
						$result = array("query"=>"BE_RENAME_EXISTED","params"=>array());
						$this->setInfo(array("msg"=>$result,"status"=>0));
					}
				}elseif(is_file($fullpath.$nameold)){
					if($this->validExt($nameold)){
						$extold = $this->getExtension($nameold);
						$namenew = $namenew.'.'.$extold;					
						if($file->exists($fullpath.$namenew)==false){
							$file2 = new \SplFileInfo($fullpath.$nameold);				
							if($file2->getExtension() == 'jpg' || $file2->getExtension() == 'jpeg' || $file2->getExtension() == 'png' || $file2->getExtension() == 'gif'){
								$filename = $file2->getFilename();
								$filename_old = $this->removeExtension($filename).'-'.$this->config['images']['resize']['thumbWidth'].'x'.$this->config['images']['resize']['thumbHeight'].'.'.$file2->getExtension();
								$fullpaththumb_name = $this->getFullPath().'/_thumbs'.$path.$filename_old;
								$file->remove($fullpaththumb_name);
							}
							$file->rename($fullpath.$nameold,$fullpath.$namenew);
							$file3 = new \SplFileInfo($fullpath.$namenew);				
							$this->createThumb($file3,$path);				
							$result = array("query"=>"BE_RENAME_MODIFIED","params"=>array());
							$this->setInfo(array("msg"=>$result,"data"=>array("namefile" => $namenew )));
						}else{
							$result = array("query"=>"BE_RENAME_EXISTED","params"=>array());
							$this->setInfo(array("msg"=>$result,"status"=>0));
						}
					}else{
						$result = array("query"=>"BE_RENAME_FILENAME_NOT_VALID","params"=>array());
						$this->setInfo(array("msg"=>$result, "status"=>0));
					}
				}
			}else{
				$result = array("query"=>"BE_RENAME_NOT_EXISTS","params"=>array());
				$this->setInfo(array("msg"=>$result, "status"=>0));
			}
		}else{
			$result = array("query"=>"BE_RENAME_FILENAME_NOT_VALID","params"=>array());
			$this->setInfo(array("msg"=>$result, "status"=>0));
		}
	}

	public function download($name,$path){
		$ruta = $this->getFullPath().$path.$name;
		if( $this->config['debug'] ) $this->_log('$ruta - '.$ruta);
		if($this->validNameFile($name) && file_exists($ruta) && is_file($ruta) ){
			$response = new Response(file_get_contents($ruta));
			$response->headers->set('Content-Type', 'application/octet-stream');
			$d = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,$name);
			$response->headers->set('Content-Disposition', $d);
			$response->send();			
		}else{
			exit();
			// return null;
		}
	}

	/**
	 * Ejecuta todo las configuraciones
	 * @return JsonResponse
	 */
	public function run(){
		$request = Request::createFromGlobals();
		$request->getPathInfo();
		$jsonResponse = new JsonResponse;

		if($request->getMethod()=='POST'){
			$this->action = $this->sanitize($request->request->get('action'));
			$path = $this->sanitize($request->request->get('path'));
			if($this->validPath($path)==false){
				$result = array("query"=>"BE_RUN_NOT_VALID %s","params"=>array($path));
				$this->setInfo(array("msg"=>$result));
				if( $this->config['debug'] ) $this->_log(__METHOD__.' - No valido $path: '.$path);
			}else{				
				if($this->action==='getfolder'){
					$folders = $this->getAllFiles($path);
					if(is_array($folders))				
						$this->setInfo(array( "data" => $folders ));				
				}elseif($this->action==='uploadfile' ){
					$files = $this->uploadAll($request->files->get('file'),$path);
					if($files)		
						$this->setInfo(array( "data" => $files ));
				}elseif($this->action==='newfolder'){
					$name = $this->sanitize($request->request->get('name'));
					$this->newFolder($name,$path);

				}elseif($this->action==='renamefile'){
					$nameold = $this->sanitize($request->request->get('nameold'));
					$namenew = $this->sanitize($request->request->get('name'));					
					$this->rename($nameold,$namenew,$path);
				}elseif($this->action==='deletefile'){
					$name = $request->request->get('name');
					$this->delete($name,$path);
				}
			}

		}elseif($request->getMethod()=='GET'){
			$this->action = $this->sanitize($request->query->get('action'));
			$path = $this->sanitize($request->query->get('path'));
			$name = $this->sanitize($request->query->get('name'));
			if($this->validPath($path)==false){
				$result = array("query"=>"BE_RUN_NOT_VALID %s","params"=>array($path));
				$this->setInfo(array("msg"=>$result));
				if( $this->config['debug'] ) $this->_log(__METHOD__.' - No valido $path: '.$path);
			}else{
				if($this->action==='download'){
					$this->download($name,$path);
				}
			}
		}
		$jsonResponse->setData($this->info);
		return $jsonResponse->sendContent();
	}
}
?>
