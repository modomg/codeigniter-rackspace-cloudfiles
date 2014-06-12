<?php 
if(!defined('BASEPATH')) exit('No direct script access allowed'); 
/**
 * CodeIgniter - Rackspace Cloudfiles API
 *
 * Description:
 * An easy to use library (wrapper) that utilizes the Rackspace Open Cloud API (previously cloud files).
 *
 * @version     2.0
 * @author      Chris Gmyr <chris@modomediagroup.com>
 * @license     MIT
 * @license     http://opensource.org/licenses/mit-license.php
 * @copyright   2011-2014 Modo Media Group
 * @link        http://www.modomediagroup.com
 */
class Cfiles{
    
    private $CI;                                   // CodeIgniter instance
	private $auth;                                 // Store authentication
	private $conn;                                 // Store connection

	// Cloud API parameters
	private $cf_username;                          // Cloud Username
	private $cf_api;                               // Cloud API Key
	
	// Main Variables
	public $cf_container;                          // Container to use
	public $cf_folder;                             // Folder to use
    
    // Error Handling
    public $stop_on_error = FALSE;                 // Stop script on error?
    private $errors = array();                     // Hold all errors
   
	function __construct($params = array())
	{
		$this->CI =& get_instance();
		
		log_message('debug', 'RS Cloudfiles Class Initialized');

		$this->initialize($params);
	}
	
	// Initializes the library parameters
	public function initialize($params = array())
	{
		// Set API preferences from the config file if they are not passed in the $params array
		foreach (array('cf_username', 'cf_api') as $key)
		{
			$this->$key = (isset($params[$key])) ? $params[$key] : $this->CI->config->item($key);
		}
        
        require_once(APPPATH.'libraries/cf/cloudfiles.php');
        
        //authenticate connection
		$this->auth = new CF_Authentication($this->cf_username, $this->cf_api);
        try
        {
            $this->auth->authenticate();
            
            try
            {
                //create the connection
                $this->conn = new CF_Connection($this->auth);
            }
            catch(Exception $e)
            {
                $this->_show_error($e);
            }
        }
        catch(Exception $e)
        {
            $this->_show_error($e);
        }
	}
	
	public function do_container($action='a')
	{
		if($action == 'a') //add
		{
			try
            {
                //Create a remote Container
                $new_container = $this->conn->create_container($this->cf_container);
                
                try
                {
                    //enable logs
                    $new_container->log_retention(TRUE);
                    
                    try
                    {
                        //publish and return URI
                        return $new_container->make_public();
                    }
                    catch(Exception $e)
                    {
                        $this->_handle_error($e);
                        return FALSE;
                    }
                }
                catch(Exception $e)
                {
                    $this->_handle_error($e);
                    return FALSE;
                }
            }
            catch(Exception $e)
            {
                $this->_handle_error($e);
                return FALSE;
            }
		}
		elseif($action == 'd') //delete
		{
            if($my_container = $this->container_info())
            {
                //get all objects
                if($objects = $this->get_objects())
                {
                    foreach($objects as $object)
                    {
                        $object = str_replace($this->cf_container.'/', '', $object->name);

                        //delete object
                        $this->do_object('d', $object);
                    }
                }

                //delete container
                $this->conn->delete_container($this->cf_container);
            }
		}
    }
	
	public function do_object($action='a', $file_name='', $file_location='', $original_file='')
	{
		$my_container = $this->container_info();
		if( $my_container === false ) {
			return false;
		}
		if($action == 'a') //add
		{
            try
            {
                //move local file to server
                $my_object = $my_container->create_object($this->cf_folder.$file_name);
                
                try
                {
                    $my_object->load_from_filename($file_location.$file_name);

                    if($original_file != '')
                    {
                        try
                        {
                            $my_object->metadata = array("original" => $original_file);
                            $my_object->sync_metadata();
                        }
                        catch(Exception $e)
                        {
                            $this->_handle_error($e);
                            return FALSE;
                        }
                    }
                }
                catch(Exception $e)
                {
                    $this->_handle_error($e);
                    return FALSE;
                }
            }
            catch(Exception $e)
            {
                $this->_handle_error($e);
                return FALSE;
            }
		}
		elseif($action == 'd') //delete
		{
			//delete file
            try
            {
                $my_container->delete_object($this->cf_folder.$file_name);
            }
            catch(Exception $e)
            {
                $this->_handle_error($e);
                return FALSE;
            }
            
            return TRUE;
		}
    }
	
	public function container_info()
	{
		try
        {
            $my_container = $this->conn->get_container($this->cf_container);
            
            if(is_object($my_container))
            {
                return $my_container;
            }
            else
            {
                throw new Exception('Container name is not valid.');
                return FALSE;
            }
        }
        catch(Exception $e)
        {
            $this->_handle_error($e);
            return FALSE;
        }
	}
	
	public function get_objects()
	{
		$my_container = $this->container_info();
		if( $my_container === false ) {
			return false;
		}
        
        try
        {
            return $my_container->get_objects(0, NULL, NULL, $this->cf_folder);
        }
        catch(Exception $e)
        {
            $this->_handle_error($e);
            return FALSE;
        }
	}
	
	public function get_object($name)
	{
		$my_container = $this->container_info();
		if( $my_container === false ) {
			return false;
		}
        try
        {
            return $my_container->get_object($name);
        }
        catch(Exception $e)
        {
            $this->_handle_error($e);
            return FALSE;
        }
	}
    
    public function download_object($current_name, $new_name, $location)
    {
        $my_container = $this->container_info();
	if( $my_container === false ) {
		return false;
	}
        try
        {
            $my_file = $my_container->get_object($current_name);
            
            try
            {
                return $my_file->save_to_filename($location.$new_name);
            }
            catch(Exception $e)
            {
                $this->_handle_error($e);
                return FALSE;
            }
        }
        catch(Exception $e)
        {
            $this->_handle_error($e);
            return FALSE;
        }
    }
    
    private function _show_error($e)
    {
        show_error($e->getMessage());
        log_message('error', $e->getMessage());
    }
    
    private function _handle_error($e)
    {
        if($this->stop_on_error == TRUE) 
            $this->_show_error($e);
        else 
            $this->_set_error($e);
    }
    
    private function _set_error($e)
    {
        $this->errors[] = $e->getMessage();
    }
    
    public function has_errors()
    {
        return count($this->errors) > 0 ? TRUE : FALSE;
    }
    
    public function get_errors()
    {
        return $this->errors;
    }
}

/* End of file cfiles.php */
/* Location: ./application/libraries/cfiles.php */
