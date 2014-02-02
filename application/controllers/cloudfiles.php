<?php if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Cloudfiles extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->library('rs_cloudfiles');

        /**
         * Alternate Usage/Initialization
         *
         * $params = array('rs_location' => 'ORD', 'rs_container' => 'my_other_container');
         * $this->load->library('rs_cloudfiles', $params);
         */
    }

    public function index()
    {
        die('Nothing here');
    }

    public function create_container()
    {
        /*
         * Creating a container is done automatically for you, but you can also run it manually with
         * $this->rs_cloudfiles->create_container();
         *
         * Enable CDN
         * $this->rs_cloudfiles->enable_container_cnd();
         *
         * Enable CDN Logging
         * $this->rs_cloudfiles->enable_container_logging();
         */

        // Grab the CDN URL
        $container_url = $this->rs_cloudfiles->get_container_cdn_url();

        die('Your new CDN URL is: ' . $container_url);
    }

    /**
     * WARNING!!!
     *
     * Version 2.0 has not implemented anything below this point...do not use!!!
     */

    public function add_local_file()
    {
        $file_location = 'assets/images/';
        $file_name = 'logo.jpg';

        $this->cfiles->do_object('a', $file_name, $file_location);

        $this->_show_errors('Image Added!');
    }

    public function add_uploaded_file()
    {
        $file_location = 'assets/uploads/';

        $original_name = 'product_image.jpg';
        $file_name = '5a4794335cd2387a2280f1a1581ea45b.jpg';

        $this->cfiles->do_object('a', $file_name, $file_location, $original_name);

        /**
         * This is how it would look with the CI upload class
         *
         * if($this->upload->do_upload('my_uploaded_file') === false)
         * {
         *      //do something with errors
         * }
         * else
         * {
         *      $data = $this->upload->data();
         *
         *      $this->cfiles->do_object('a', $data['file_name'], $file_location, $data['orig_name']);
         *
         *      //delete local file here
         * }
         */

        $this->_show_errors('Image Added!');
    }

    public function add_local_file_folder()
    {
        $this->cfiles->cf_folder = 'images/';
        //$this->cfiles->cf_folder = 'as/many/levels/as/you/want/too/';

        $file_location = 'assets/images/';
        $file_name = 'logo.jpg';

        $this->cfiles->do_object('a', $file_name, $file_location);

        $this->_show_errors('Image Added!');
    }

    public function container_info()
    {
        if ($container_info = $this->cfiles->container_info()) {
            /**
             * [name]
             * [object_count]
             * [bytes_used]
             * [cdn_enabled]
             * [cdn_uri]
             * [cdn_ttl]
             * [cdn_log_retention]
             * [cdn_acl_user_agent]
             * [cdn_acl_referrer]
             */

            echo '<p>' . $container_info->name . '</p>';
            echo '<p>' . $container_info->cdn_uri . '</p>';
        } else {
            die('Container Invalid');
        }

        $this->_show_errors();
    }

    public function container_objects()
    {
        if ($objects = $this->cfiles->get_objects()) {
            foreach ($objects as $object) {
                //do something
                /**
                 * [name]
                 * [last_modified]
                 * [content_type]
                 * [content_length]
                 * [metadata] => Array
                 * (
                 *      [Original]
                 * )
                 *
                 * metadata will only be available if you originally put it in
                 */
                echo '<p>' . $object->name . ' - ' . $object->content_type . '</p>';
            }
        }

        $this->_show_errors();
    }

    public function container_objects_folder()
    {
        $this->cfiles->cf_folder = 'images/';
        if ($objects = $this->cfiles->get_objects()) {
            foreach ($objects as $object) {
                //do something
                /**
                 * [name]
                 * [last_modified]
                 * [content_type]
                 * [content_length]
                 * [metadata] => Array
                 * (
                 *      [Original]
                 * )
                 *
                 * metadata will only be available if you originally put it in
                 */
                echo '<p>' . $object->name . ' - ' . $object->content_type . '</p>';
            }
        }

        $this->_show_errors();
    }

    public function get_object()
    {
        $file_name = 'logo.jpg';

        if ($object = $this->cfiles->get_object($file_name)) {
            /**
             * [name]
             * [last_modified]
             * [content_type]
             * [content_length]
             * [metadata] => Array
             * (
             *      [Original]
             * )
             *
             * metadata will only be available if you originally put it in
             */

            echo '<p>' . $object->name . ' - ' . $object->content_type . '</p>';
        }

        $this->_show_errors();
    }

    public function download_object()
    {
        $cloud_file_name = 'logo.jpg';
        $local_file_name = 'downloaded_logo.jpg';
        $file_location = 'assets/images/';

        $this->cfiles->download_object($cloud_file_name, $local_file_name, $file_location);

        $this->_show_errors('Image Saved!');
    }

    public function delete_file()
    {
        $file_name = 'logo.jpg';

        if ($this->cfiles->do_object('d', $file_name)) {
            die('Image Deleted!');
        } else {
            die('Image NOT Deleted!');
        }
    }

    public function delete_fake_files()
    {
        $files = array('bad_reference1.jpg', 'bad_reference2.jpg', 'bad_reference3.jpg');

        foreach ($files as $file) {
            $this->cfiles->do_object('d', $file);
        }

        $this->_show_errors('All files deleted!');
    }

    public function delete_file_folder()
    {
        $this->cfiles->cf_folder = 'images/';

        $file_name = 'logo.jpg';

        $this->cfiles->do_object('d', $file_name);

        $this->_show_errors('Image Deleted!');
    }

    private function _show_errors($success_msg = 'All Good!')
    {
        if ($this->cfiles->has_errors()) {
            echo 'The following errors were found:<ul>';
            foreach ($this->cfiles->get_errors() as $error) {
                echo '<li>' . $error . '</li>';
            }
            echo '</ul>';
        } else {
            die($success_msg);
        }
    }
}

/* End of file cloudfiles.php */
/* Location: ./application/controllers/cloudfiles.php */