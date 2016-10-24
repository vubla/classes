<?php
class CachedImage {
 
    var $image;
    var $image_type;
    var $image_link;
    var $wid;
    var $pid;
    var $pdo;
    var $loadedFromCache = false;
    var $cache_lifetime = 5184000;
 
 	function __construct($wid,$pid,$image_link)
 	{
 	   
        $this->image_link = $image_link;
 		$this->wid = $wid;	
		$this->pid = $pid;
		$this->pdo = vpdo::getVdo(VUBLA_CACHE);
	
 		if(!$this->loadFromCache())
 		{
 			$this->loadFromUrl($image_link);
            $this->cleverResize();
			$this->save();
 		}
		
 	}
    
    private function cache_lifetime()
    {
        return rand($this->cache_lifetime/2,$this->cache_lifetime*1.5);
    }
	
	function loadFromCache()
	{
		$stm = $this->pdo->prepare("select time, image, image_type, image_link from image_cache where wid = ? and pid = ?");
		$stm->execute(array($this->wid,$this->pid));
		if($stm->rowCount() != 1)
		{
            $stm->closeCursor();
			return false;
		}
        
		$obj = $stm->fetchObject();
        $stm->closeCursor();
        
        if($obj->time < time() - $this->cache_lifetime() || $obj->image_link != $this->image_link){
            $stm = $this->pdo->prepare("delete from image_cache where wid = ? and pid = ?");
            $stm->execute(array($this->wid,$this->pid));
            $stm->closeCursor();
            return false;
        }
        
		$this->image = base64_decode($obj->image);
		$this->image_type = $obj->image_type;
		$this->loadedFromCache = true;
		return true;
	}
	
	
 
   function loadFromUrl($filename) 
   {
 
      $image_info = getimagesize($filename);
      $this->image_type = $image_info[2];
    
      if( $this->image_type == IMAGETYPE_JPEG ) 
      {
         $this->image = imagecreatefromjpeg($filename);
      } 
      elseif( $this->image_type == IMAGETYPE_GIF ) 
      {
         $this->image = imagecreatefromgif($filename);
      } 
      elseif( $this->image_type == IMAGETYPE_PNG ) 
      {

         $this->image = imagecreatefrompng($filename);
           //imagesavealpha($this->image, false);
        
      }
      elseif( $this->image_type == IMAGETYPE_BMP ) 
      {

         $this->image = imagecreatefromwbmp($filename);
           //imagesavealpha($this->image, false);
        
       
     
      } else {
            trigger_error('No possible image type info');
       }
       imagealphablending($this->image, true);
   }
   
    static function setHeaderStatic($image_type) 
    {
        if( $image_type == IMAGETYPE_JPEG ) 
        {
            header('Content-Type: image/jpeg');
        } 
        elseif( $image_type == IMAGETYPE_GIF ) 
        {
            header('Content-Type: image/gif');
        } 
        elseif( $image_type == IMAGETYPE_PNG ) 
        {
            header('Content-Type: image/png');  
        }
         elseif( $image_type == IMAGETYPE_BMP ) 
        {
            header('Content-Type: image/bmp');  
        }
        else {
            trigger_error('No possible image type info');
        }
   }
   
 	 function setHeader() 
    {
    	self::setHeaderStatic( $this->image_type ); 
       
       
   }
  
   function save() 
   {
 		$stm = $this->pdo->prepare("insert into image_cache (wid,pid,time, image, image_type, image_link) values (?,?,?,?,?,?)");
		ob_start();
		$this->output();
		$this->image = ob_get_clean();
		$stm->execute(array($this->wid,$this->pid,time(), (base64_encode(($this->image))),$this->image_type, $this->image_link));
        $stm->closeCursor();
   }
   
    function output() 
    {
        
		if(!is_resource($this->image))
		{
    		echo $this->image;
    	} 
	    elseif( $this->image_type == IMAGETYPE_JPEG ) 
        {
           imagejpeg($this->image);
       } 
       elseif( $this->image_type == IMAGETYPE_GIF ) 
       {
          imagegif($this->image);
       } 
       elseif( $this->image_type == IMAGETYPE_PNG ) 
       {
          imagepng($this->image);
       }
       elseif( $this->image_type == IMAGETYPE_BMP ) 
       {
          imagewbmp($this->image);
       }
       else {
           echo "failed resolving";
       }
    }
   
    function getWidth() {
 
        return imagesx($this->image);
    }
   
	function getHeight() {
 
        return imagesy($this->image);
    }
	
    function resizeToHeight($height) {
 
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }
 
	function resizeToWidth($width) {
    	$ratio = $width / $this->getWidth();
    	$height = $this->getheight() * $ratio;
    	$this->resize($width,$height);
	}
 
  function scale($scale) {
     $width = $this->getWidth() * $scale/100;
     $height = $this->getheight() * $scale/100;
     $this->resize($width,$height);
  }
 
  function resize($width,$height) {
      $new_image = imagecreatetruecolor($width, $height);
    //  if($this->image_type == IMAGETYPE_PNG){
          imagealphablending($new_image, false);
          imagesavealpha($new_image, true);
      //}
      
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;
  }      
 
  function cleverResize() {
     $max = 135;
     $width = $this->getWidth();
     $height = $this->getHeight();
     if($width >= $height){
       
         $this->resizeToWidth(135);
         
     }
     if($width < $height){
         $this->resizeToHeight(135);
     }
  }
}