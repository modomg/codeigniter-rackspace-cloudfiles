<?php

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

    public function delete_container()
    {
        $this->rs_cloudfiles->delete_container();

        die('Container Deleted!');
    }

    public function add_local_file()
    {
        $file_location = 'assets/images/';
        $file_name = 'logo.jpg';

        $this->rs_cloudfiles->upload_object($file_name, $file_location);

        die('Image Uploaded');
    }

    public function add_uploaded_file()
    {
        $file_location = 'assets/uploads/';

        $original_name = 'product_image.jpg';
        $file_name = '5a4794335cd2387a2280f1a1581ea45b.jpg';

        $this->rs_cloudfiles->upload_object($file_name, $file_location, array('original' => $original_name, 'Author' => 'Chris'));

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
         *      $this->rs_cloudfiles->upload_object($data['file_name'], $file_location, array('original' => $data['orig_name']));
         *
         *      //delete local file here
         * }
         */

        die('Image Uploaded');
    }

    public function set_meta()
    {
        $file_name = '5a4794335cd2387a2280f1a1581ea45b.jpg';

        $this->rs_cloudfiles->set_meta_data($file_name, array('original' => 'my_file_name.jpg', 'Author' => 'Chris'));

        die('Meta Set');
    }

    public function get_file_info()
    {
        $file_name = '5a4794335cd2387a2280f1a1581ea45b.jpg';

        $file_info = $this->rs_cloudfiles->get_object($file_name);

        /**
         * Available Methods:
         *
         * $file_info->getContainer();
         * $file_info->getName();
         * $file_info->getContent();
         * $file_info->getContentLength();
         * $file_info->getContentType();
         * $file_info->getEtag();
         * $file_info->getLastModified();
         */

        die($file_info->getContentType());
    }

    public function get_file_meta()
    {
        $file_name = '5a4794335cd2387a2280f1a1581ea45b.jpg';

        $meta_info = $this->rs_cloudfiles->get_meta_data($file_name);

        die($meta_info->original); // my_file_name.jpg from set_meta()
    }

    public function delete_file()
    {
        $file_name = 'logo.jpg';

        $this->rs_cloudfiles->delete_object($file_name);

        die('Image Deleted');
    }

    public function add_local_file_folder()
    {
        $this->rs_cloudfiles->virtual_folder = 'images/';
        //$this->rs_cloudfiles->virtual_folder = 'as/many/levels/as/you/want/';

        $file_location = 'assets/images/';
        $file_name = 'logo.jpg';

        $this->rs_cloudfiles->upload_object($file_name, $file_location);

        die('Image Added!');
    }

    public function delete_file_folder()
    {
        $this->rs_cloudfiles->virtual_folder = 'images/';

        $file_name = 'logo.jpg';

        $this->rs_cloudfiles->delete_object($file_name);

        die('Image Deleted!');
    }

    public function container_info()
    {
        $container = $this->rs_cloudfiles->get_container();

        /**
         * $container->getObjectCount();
         * $container->getBytesUsed();
         */

        die('File Count: ' . $container->getObjectCount());
    }

    public function container_objects()
    {
        if ($objects = $this->rs_cloudfiles->get_objects()) {
            foreach ($objects as $object) {

                /**
                 * Available Methods:
                 *
                 * $object->getContainer();
                 * $object->getName();
                 * $object->getContent();
                 * $object->getContentLength();
                 * $object->getContentType();
                 * $object->getEtag();
                 * $object->getLastModified();
                 */
                echo '<p>' . $object->getName() . ' - ' . $object->getContentType() . '</p>';
            }
        }
    }

    public function container_objects_folder()
    {
        $this->rs_cloudfiles->virtual_folder = 'images/';
        if ($objects = $this->rs_cloudfiles->get_objects()) {
            foreach ($objects as $object) {

                /**
                 * Available Methods:
                 *
                 * $object->getContainer();
                 * $object->getName();
                 * $object->getContent();
                 * $object->getContentLength();
                 * $object->getContentType();
                 * $object->getEtag();
                 * $object->getLastModified();
                 */
                echo '<p>' . $object->getName() . ' - ' . $object->getContentType() . '</p>';
            }
        }
    }

    public function download_object()
    {
        $cloud_file_name = 'logo.jpg';
        $local_file_name = 'downloaded_logo.jpg';
        $file_location = 'assets/images/';

        $this->rs_cloudfiles->download_object($cloud_file_name, $local_file_name, $file_location);

        die('Image Saved!');
    }

    public function delete_fake_files()
    {
        $files = array('bad_reference1.jpg', 'bad_reference2.jpg', 'bad_reference3.jpg');

        foreach ($files as $file) {
            $this->rs_cloudfiles->delete_object($file);
        }

        die('All files deleted!');
    }
}

/* End of file cloudfiles.php */
/* Location: ./application/controllers/cloudfiles.php */