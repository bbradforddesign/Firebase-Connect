<?php
/**
 * Plugin Name: Firebase Connect
 * Plugin URI: https://github.com/bbradforddesign/Firebase-Connect
 * Description: Plugin to connect Wordpress to Firebase
 * Version: 1.0
 * Author: Blake Bradford
 * Author URI: https://www.bbradforddesign.com
 */

    // register menu-building function
    add_action( 'admin_menu', 'firebase_connect_menu' );

    // admin-menu building function
    function firebase_connect_menu() {
        add_submenu_page( 
            'plugins.php', 
            'Firebase Connect',
            'Firebase Connect', 
            'manage_options', 
            'firebase-config', 
            'firebase_config_callback' );
    }

    // admin menu for users with high-level access
    function firebase_config_callback() {
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }
        ?> 
        <div class="wrap">
            <h2>Firebase Configuration</h2>
            <p>Enter your Firebase project's configuration options to connect your site.</p>
            <h3>Locate your options:</h3>
            <ol>
                <li>Log in at <a href="https://firebase.google.com">firebase.google.com</a>.</li>
                <li>Go to the Firebase console.</li>
                <li>On the sidebar, select the "Settings" icon.</li>
                <li>Select "Project Settings".</li>
                <li>Scroll down to "Your Apps".</li>
                <h4>If you haven't registered your site with your Firebase project:</h4>
                <ol>
                    <li>Select "Add App".</li>
                    <li>Select the "Web" icon.</li>
                    <li>Create a nickname for your site.</li>
                    <li>Select "Register App".</li>
                    <li>Scroll down through the code window to the section labeled under "Your web app's Firebase configuration".</li>
                    <li>Within the "firebaseConfig" variable, copy and paste the values under each label into the corresponding option below.</li>
                    <li>Once finished, select "Continue to Console" to exit registration.</li>
                </ol>
                <h4>If you have already registered your site:</h4>
                <ol>
                    <li>Select your site's nickname from the column labeled "Web Apps".</li>
                    <li>Scroll down through the "Firebase SDK Snippet" code to the section labeled under "Your web app's Firebase configuration".</li>
                    <li>Within the "firebaseConfig" variable, copy and paste the values under each label into the corresponding option below.</li>
                </ol>
            </ol>
            <form action="<?php echo esc_url(admin_url('admin-post.php'));?>" method="POST">
                <div style="display: flex; flex-direction: column;">
                <label for="key">API Key</label>
                <input type="text" id="key" name="api_key" placeholder="Unique API Key" style="width: 50%">
                </div>
                <div style="display: flex; flex-direction: column;">
                <label for="auth">Auth Domain</label>
                <input type="text" id="auth" name="auth_domain" placeholder="your-project.firebaseapp.com" style="width: 50%"></div>
                <div style="display: flex; flex-direction: column;">
                <label for="db">Database URL</label>
                <input type="text" id="db" name="database_url" placeholder="https://your-project.firebaseio.com" style="width: 50%">
                </div>
                <div style="display: flex; flex-direction: column;">
                <label for="project">Project ID</label>
                <input type="text" id="project" name="project_id" placeholder="your-project" style="width: 50%">
                </div>
                <div style="display: flex; flex-direction: column;">
                <label for="storage">Storage Bucket</label>
                <input type="text" id="storage" name="storage_bucket" placeholder="your-project.appspot.com" style="width: 50%">
                </div>
                <div style="display: flex; flex-direction: column;">
                <label for="messaging">Messaging Sender ID</label>
                <input type="text" id="messaging" name="messaging_sender_id" placeholder="123456" style="width: 50%">
                </div>
                <div style="display: flex; flex-direction: column;">
                <label for="app">App ID</label>
                <input type="text" id="app" name="app_id" placeholder="Unique ID" style="width: 50%">
                </div>
                <div style="display: flex; flex-direction: column;">
                <label for="measurement">Measurement ID</label>
                <input type="text" id="measurement" name="measurement_id" placeholder="Unique ID" style="width: 50%">
                </div>
                <input type="hidden" name="action" value="process_form">
                <input type="submit" name="submit" id="submit" value="Save">
            </form>
            </div>
        <?php
    }

    // update stored options, or create new ones if none exist
    function save_option($option) {
        if (isset($_POST[$option])) {
            $input = sanitize_text_field($_POST[$option]);
            $api_exists = get_option($option);
            if (!empty($input) && !empty($api_exists)) {
                update_option($option, $input);
            } else {
                add_option($option, $input);
            }
        }
    }

    // for each field of input, apply the function to update stored options
    function save_firebase_config() {
        save_option('api_key');
        save_option('auth_domain');
        save_option('database_url');
        save_option('project_id');
        save_option('storage_bucket');
        save_option('messaging_sender_id');
        save_option('app_id');
        save_option('measurement_id');
        wp_redirect($_SERVER['HTTP_REFERER']);
    }

    // retrieving Firebase scripts
    function get_firebase() {

        // get stored config options
        $firebase_params = array(
            'apiKey' => get_option('api_key'),
            'authDomain' => get_option('auth_domain'),
            'databaseUrl' => get_option('database_url'),
            'projectId' => get_option('project_id'),
            'storageBucket' => get_option('storage_bucket'),
            'messagingSenderId' => get_option('messaging_sender_id'),
            'appId' => get_option('app_id'),
            'measurementId' => get_option('measurement_id'),
        );

        wp_enqueue_script('firebase-app',"https://www.gstatic.com/firebasejs/7.23.0/firebase-app.js", null, null, true);
        wp_enqueue_script('firebase-analytics',"https://www.gstatic.com/firebasejs/7.23.0/firebase-analytics.js", null, null, true);
        wp_enqueue_script('firebase-firestore',"https://www.gstatic.com/firebasejs/7.22.1/firebase-firestore.js", null, null, true);
        wp_enqueue_script('firebase-connect', plugin_dir_url(__FILE__) . '/js/init.js', array('firebase-app','firebase-analytics','firebase-firestore'));

        // pass stored config options to the initializing script
        wp_localize_script('firebase-connect','firebaseConfig',$firebase_params);
    };

    // actions to process form submissions on admin page
    add_action( 'admin_post_nopriv_process_form', 'save_firebase_config');
    add_action( 'admin_post_process_form', 'save_firebase_config');

    // pass the configured initializing script to the WordPress site
    add_action('wp_enqueue_scripts', 'get_firebase');
 ?>