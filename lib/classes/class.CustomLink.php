<?php 
namespace nvRexHelper;

class CustomLink 
{
	private $href;
	private $target;

	function __construct($sPath) 
	{
		if(!$sPath) 
		{
			$this->href = "";
			$this->target = "";
			return;
		} 

		if(filter_var($sPath, FILTER_VALIDATE_URL)) 
		{
			// externe url
			$this->href = $sPath;
			$this->target = "_blank";
		}
		else 
		{
			if(filter_var($sPath, FILTER_VALIDATE_INT)) 
			{
				// interne url
				$this->href = rex_getUrl($sPath);
				$this->target = "_self";
			} 
			else 
			{
				// media
				$this->href = MEDIA . $sPath;
				$this->target = "_blank";
			}
		}


	}

	public function getHref() 
	{
		return $this->href ? $this->href : ""; 
	}

	public function getTarget() 
	{
		return $this->target ? $this->target : "";
	}
} 