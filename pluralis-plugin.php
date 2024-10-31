<?php
/*
Plugin Name: Pluralis Plugin
Plugin URI: Your Plugin URI
Version: 1.0.0
Author: <a href="pluralis.com">Pluralis</a>
Description: A plugin that adds pluralis' tag to your wordpress website
*/

if (!class_exists("PluralisPlugin")) {
	class PluralisPlugin {
        var $adminOptionsName = "tag_number";
		function PluralisPlugin() { //constructor

		}

        //Returns an array of admin options
        function getAdminOption() {
            $tag_num = '101';
            $devOptions = get_option($this->adminOptionsName);
            if (!empty($devOptions)) {
                $tag_num = $devOptions;
            }
            update_option($this->adminOptionsName, $tag_num);
            return $tag_num;
        }

        function init() {
            $this->getAdminOption();
        }

    //Prints out the admin page
        function printAdminPage() {
            $devOptions = $this->getAdminOption();

            if (isset($_POST['update_PluralisPluginSettings'])) {
                if($_POST['tag_number'] != '' && $this->sanityCheck($_POST['tag_number'], 'numeric'))
                {
                    if (isset($_POST['tag_number'])) {
                        $devOptions = $_POST['tag_number'];;
                        update_option($this->adminOptionsName, $devOptions);

                        ?>
                        <div class="updated"><p><strong><?php _e("Settings Updated.", "PluralisPlugin");?></strong></p></div>
                        <?php
                    }
                }
                else {
                    ?>
                    <div class="error"><p><strong><?php _e("Please Check your settings.", "PluralisPlugin");?></strong></p></div>
                <?php }
            }
            ?>

            <div class=wrap>
                <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
                    <h2>Pluralis Plugin</h2>
                    <h3>Enter the number you got at the end of the Pluralis contest creation wizard</h3>
                    <div style="padding-bottom: 15px">i.e - if the tag was <b>"//cache.pluralis.com/srv/juggler_101.js"</b>, you should insert <b>101</b></div>
                    <input name="tag_number" style="width: 3%; height: 20px;" value="<?php echo $devOptions; ?>">
                    <div class="submit">
                        <input type="submit" name="update_PluralisPluginSettings" value="<?php _e('Update Settings', 'PluralisPlugin') ?>" />
                    </div>
                </form>
            </div>
        <?php
        }//End function printAdminPage()

       function addHeaderCode() {
           $tag_num = $this->getAdminOption();
           wp_enqueue_script('juggler_handle', '//cache.pluralis.com/srv/juggler_' . $tag_num . '.js', '', false);
       }

        function remove_wp_ver_js( $src ) {
            if ( strpos( $src, 'ver=' . get_bloginfo( 'version' ) ) )
                $src = remove_query_arg( 'ver', $src );
            return $src;
        }

        function sanityCheck($string, $type){
            // assign the type
            $type = 'is_'.$type;

            if(!$type($string))
            {
                return FALSE;
            }
            else
            {
                // if all is well, we return TRUE
                return TRUE;
            }
        }
    }//End Class PluralisPlugin
}

if (class_exists("PluralisPlugin")) {
    $pluralisPlugin = new PluralisPlugin();
}

//Initialize the admin panel
if (!function_exists("PluralisPlugin_ap")) {
    function PluralisPlugin_ap() {
        global $pluralisPlugin;
        if (!isset($pluralisPlugin)) {
            return;
        }
        if (function_exists('add_options_page')) {
            add_options_page('Pluralis Plugin', 'Pluralis Plugin Admin Options', 9, basename(__FILE__), array(&$pluralisPlugin, 'printAdminPage'));
        }
    }
}

//Actions and Filters
if (isset($pluralisPlugin)) {
    //Actions
    add_action('admin_menu', 'PluralisPlugin_ap');
    add_action('wp_head', array(&$pluralisPlugin, 'addHeaderCode'), 1);
    add_action('activate_pluralis-plugin.php',  array(&$pluralisPlugin, 'init'));
    //Filters

    // remove wp version param from any enqueued scripts
    add_filter( 'script_loader_src', array(&$pluralisPlugin, 'remove_wp_ver_js'), 9999 );
}
?>