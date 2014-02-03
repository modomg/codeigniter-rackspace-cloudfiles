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
 * @copyright   2011-2014 Modo Media Group
 * @link        http://www.modomediagroup.com
 */

use OpenCloud\Rackspace;

class Rs_cloudfiles
{

    private $ci;
    private $client;
    private $service;
    private $container;

    private $rs_username;
    private $rs_api_key;
    private $rs_auth_url;
    public $rs_location;
    public $rs_container_name;


    function __construct($params = array())
    {
        $this->ci = get_instance();

        log_message('debug', 'Rackspace Cloudfiles Class Initialized');

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
            'apiKey' => $this->rs_api_key
        ));

        // now, connect to the ObjectStore service
        $this->service = $this->client->objectStoreService('cloudFiles', $this->rs_location);

        // see if the container is already made
        $containerList = $this->service->listContainers();
        while ($container = $containerList->next()) {
            if ($container->name == $this->rs_container_name) {
                $this->container = $this->service->getContainer($this->rs_container_name);
            }
        }

        // check if container was set, if not - create it
        if ($this->container === null) {
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
    }

    public function delete_container($force_delete_files = true)
    {
        $this->container->delete($force_delete_files);
    }

    public function enable_container_logging()
    {
        $this->container->enableLogging();
    }

    public function disable_container_logging()
    {
        $this->container->disableLogging();
    }

    public function enable_container_cnd()
    {
        $this->container->enableCdn();
    }

    public function disable_container_cnd()
    {
        $this->container->disableCdn();
    }

    public function get_container_cdn_url()
    {
        return $this->container->getCdn()->getCdnUri();
    }

    public function upload_object($file_name, $file_location, $meta_data = array())
    {
        $data = fopen($file_location . $file_name, 'r+');

        $this->container->uploadObject($file_name, $data, $meta_data);
    }

    public function delete_object($file_name)
    {
        try {
            $object = $this->container->getObject($file_name);
            $object->delete();
        } catch (Exception $e) {
            // do nothing, we don't want an error if we delete something that's not there
        }
    }
}