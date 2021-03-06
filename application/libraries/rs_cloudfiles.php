<?php
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
 * @copyright   2011-2015 Modo Media Group
 * @link        http://www.modomediagroup.com
 */

use OpenCloud\Rackspace;
use OpenCloud\Common\Exceptions\IOError;
use OpenCloud\ObjectStore\Resource\DataObject;

class Rs_cloudfiles
{

    private $ci;
    private $client;
    private $service;
    private $container;
    private $object;

    private $rs_username;
    private $rs_api_key;
    private $rs_auth_url;
    public $rs_location;
    public $rs_container_name;
    public $virtual_folder;


    function __construct($params = array())
    {
        $this->ci = get_instance();

        log_message('debug', 'Rackspace Cloudfiles: Class Initialized');

        $this->initialize($params);
    }

    // Initializes the library parameters
    public function initialize($params = array())
    {
        // Set API preferences from the config file if they are not passed in the $params array
        foreach (array('rs_username', 'rs_api_key', 'rs_auth_url', 'rs_location', 'rs_container_name') as $key) {
            $this->$key = (isset($params[$key])) ? $params[$key] : config_item($key);
        }

        $this->fix_auth_url();

        $this->client = new Rackspace($this->rs_auth_url, array(
            'username' => $this->rs_username,
            'apiKey'   => $this->rs_api_key
        ));

        // now, connect to the ObjectStore service
        $this->service = $this->client->objectStoreService('cloudFiles', $this->rs_location);

        // try to connect to the container
        try {
            $this->container = $this->service->getContainer($this->rs_container_name);
        } catch (Guzzle\Http\Exception\ClientErrorResponseException $e) {
            // container not found...create it
            $this->create_container();
        }
    }

    private function fix_auth_url()
    {
        $this->rs_auth_url = $this->rs_auth_url == 'UK' ? Rackspace::UK_IDENTITY_ENDPOINT : Rackspace::US_IDENTITY_ENDPOINT;
    }

    public function create_container($logs = true, $cdn = true)
    {
        $this->container = $this->service->createContainer($this->rs_container_name);

        if ($cdn) {
            $this->enable_container_cnd();
        }

        if ($logs) {
            $this->enable_container_logging();
        }

        log_message('debug', 'Rackspace Cloudfiles: New container "' . $this->rs_container_name . '" was created');
    }

    public function delete_container($force_delete_files = true)
    {
        $this->container->delete($force_delete_files);

        log_message('debug', 'Rackspace Cloudfiles: Container ' . $this->rs_container_name . ' was deleted');
    }

    public function enable_container_logging()
    {
        $this->container->enableLogging();

        log_message('debug', 'Rackspace Cloudfiles: Container ' . $this->rs_container_name . ' enabled logging');
    }

    public function disable_container_logging()
    {
        $this->container->disableLogging();

        log_message('debug', 'Rackspace Cloudfiles: Container ' . $this->rs_container_name . ' disabled logging');
    }

    public function enable_container_cnd()
    {
        $this->container->enableCdn();

        log_message('debug', 'Rackspace Cloudfiles: Container ' . $this->rs_container_name . ' enabled CDN');
    }

    public function disable_container_cnd()
    {
        $this->container->disableCdn();

        log_message('debug', 'Rackspace Cloudfiles: Container ' . $this->rs_container_name . ' disabled CDN');
    }

    public function get_container()
    {
        return $this->container;
    }

    public function get_container_cdn_url()
    {
        return $this->container->getCdn()->getCdnUri();
    }

    public function upload_object($file_name, $file_location, array $meta_data = array())
    {
        $data = fopen($file_location . $file_name, 'r+');

        $metaHeaders = DataObject::stockHeaders($meta_data);

        $this->container->uploadObject($this->virtual_folder . $file_name, $data, $metaHeaders);

        log_message('debug', 'Rackspace Cloudfiles: File ' . $this->virtual_folder . $file_name . ' was uploaded');
    }

    public function get_object($file_name)
    {
        return $this->object = $this->container->getObject($this->virtual_folder . $file_name);
    }

    public function get_objects()
    {
        return $this->container->objectList(array('prefix' => $this->virtual_folder));
    }

    public function set_meta_data($file_name, $meta_data = array())
    {
        $this->get_object($this->virtual_folder . $file_name);
        $this->object->saveMetadata($meta_data);
    }

    public function get_meta_data($file_name)
    {
        return $this->get_object($file_name)->getMetadata();
    }

    public function download_object($file_name, $local_file_name, $local_file_location)
    {
        $object = $this->get_object($file_name);

        if (!$fp = @fopen($local_file_location . $local_file_name, 'wb')) {
            throw new IOError(sprintf(
                'Could not open file [%s] for writing',
                $local_file_location . $local_file_name
            ));
        }
        if (fwrite($fp, $object->getContent()) === false) {
            throw new Exception('Cannot write to file (' . $local_file_location . $file_name . ')');
        }

        log_message('debug', 'Rackspace Cloudfiles: The file ' . $local_file_location . $file_name . ' was downloaded');
    }

    public function delete_object($file_name)
    {
        try {
            $object = $this->container->getObject($this->virtual_folder . $file_name);
            $object->delete();
        } catch (Exception $e) {
            // do nothing, we don't want an error if we delete something that's not there
        }

        log_message('debug', 'Rackspace Cloudfiles: The file ' . $this->virtual_folder . $file_name . ' was deleted');
    }
}