<?php

////// Set site to maintenance mode //////

function tt_maintenance_mode() {

    // Check if the user is logged in and has admin privileges

    if (!current_user_can('administrator') ) {

        // Set a 503 (Service Unavailable) HTTP response code

        http_response_code(503);

        

        // Customize your maintenance page content here

        echo '<!DOCTYPE html>';

        echo '<html>';

        echo '<head>';

        echo '<title>Site Under Maintenance</title>';

        echo '<meta charset="UTF-8">';

        echo '<style>

                body { font-family: Arial, sans-serif; text-align: center; margin-top: 20%; color: #333; }

                h1 { font-size: 40px; margin-bottom: 20px; }

                p { font-size: 18px; }

                body {background: #f0f0f1;}

            </style>';

        echo '</head>';

        echo '<body>';

        // echo '<img style="width: 200px;" src="' . get_site_icon_url() . '"/>';

        echo '<h1>' . get_bloginfo('name') . ' will be back soon!</h1>';

        echo '<p>Our website is currently undergoing scheduled maintenance.</p>';

        echo '<p>Sorry for the inconvenience. Please check back later.</p>';

        echo '</body>';

        echo '</html>';

        

        // Stop further execution to prevent loading of the WordPress theme

        exit();

    }

}

////// END Set site to maintenance mode //////


////// HIDE/SHOW - USER LOGGED IN  //////

function critical_logged_in_styles() {

    if (is_user_logged_in()) {

        echo '<style id="logged-in-critical-css">

            .th_hidden_to_logged_in { display: none !important; }

        </style>';

    } else {

        echo '<style id="logged-out-critical-css">

            .th_hidden_to_logged_out { display: none !important; }

        </style>';

    }

}

add_action('wp_head', 'critical_logged_in_styles', 1);





// Keep your existing function for any additional styles

function show_hide_logged_in() {

    if (is_user_logged_in()) {

        echo '<style>

            .th_visible_to_logged_in { display: block; }

            /* Additional logged-in styles */

        </style>';

    } else {

        echo '<style>

            .th_visible_to_logged_out { display: block; }

            /* Additional logged-out styles */

        </style>';

    }

}

add_action('wp_footer', 'show_hide_logged_in');



////// END HIDE/SHOW //////





////// LOGOUT WIDGET //////

    // Widget class

    class Hello_Renos_Widget extends WP_Widget {



        // Widget setup

        function __construct() {

            parent::__construct(

                'hello_renos_widget', // Base ID

                'Hello Renos Widget', // Widget name

                array('description' => __('A widget that echoes a Logout button', 'text_domain'))

            );

        }



        // Front-end display of the widget

        public function widget($args, $instance) {

            echo $args['before_widget'];

            // WORKING CODE:

            if (is_user_logged_in()) {

                echo '<a class="uk-button-small uk-button uk-button-secondary DDel-link DDuk-button-text" href="' . esc_url(wp_logout_url(get_home_url())) . '"> ' . __('Logout', 'hello-renos-widget') . ' ' . esc_html(wp_get_current_user()->nickname) . '</a>';

            }



            // if (is_user_logged_in()) {

            //     $current_user = wp_get_current_user();

            //     $logout_url   = esc_url(wp_logout_url(get_home_url()));

            //     $first_name   = esc_html($current_user->first_name);



            //     $html = sprintf(

            //         '<a class="uk-button uk-button-primary DDel-link OR DDuk-button DDuk-button-text" href="%s">Logout %s</a>',

            //         $logout_url,

            //         $first_name

            //     );



            //     echo $html; // or return $html; depending on usage

            // }



            // COMMENTED OUT CODE:

            //echo '<a class="uk-button uk-button-primary" href="' . wp_logout_url(get_home_url()) . '">Logout</a>';

            echo $args['after_widget'];

        }



        // Back-end widget form

        public function form($instance) {

            // There are no widget settings to configure.

        }



        // Sanitize widget form values as they are saved

        public function update($new_instance, $old_instance) {

            $instance = array();

            return $instance;

        }

    }



    // Register the widget

    function register_hello_renos_widget() {

        register_widget('Hello_Renos_Widget');

    }

    add_action('widgets_init', 'register_hello_renos_widget');

////// END LOGOUT WIDGET //////







// RESTRICTION - NOTIFICATION LOGIN REQUIRED - LOGGED OUT USERS - Enqueue jQuery

function enqueue_login_required_script() {

    if (is_user_logged_in()) return;



    // Enqueue jQuery and assume UIkit is already loaded

    wp_enqueue_script('jquery');



    // Inline JS to check and show notification

    $script = <<<JS

    jQuery(document).ready(function($) {

        $('#is_logged_in a.el-link, .is_logged_in_menu_item a').on('click', function(e) {

            e.preventDefault();

            UIkit.notification({

                message: 'You need to log in to create an Initiative!',

                status: 'warning'

            });

        });

    });

    JS;

    wp_add_inline_script('jquery', $script); // or use your own custom handle if needed

}

add_action('wp_enqueue_scripts', 'enqueue_login_required_script');

// END NOTIFICATION TO LOGIN - LOGGED OUT USERS




// RESTRICTION - INITIATIVES /// SHOW EDIT BUTTON ONLY TO POST OWNER ///

function show_hide_edit_button() {

    $current_user_id = get_current_user_id();

    $author_id = get_the_author_meta('ID');



    if ($current_user_id !== $author_id) {

        // Hide button

        //echo "Not author! Id is: " . $current_user_id;



        ?>

        <style>

            /* SHOW EDIT BUTTON ONLY TO POST OWNER */

            body.single-post .modal-button.render-form {

                display: none!important;

            }          

        </style>

        <?php

    }

}

add_action('wp_footer', 'show_hide_edit_button');

// START CUSTOM AUTHOR COMMENTS aka "Contributions"
function current_author_comments_shortcode() {
    // Get the queried author (profile being viewed)
    $author = get_queried_object();

    if (!$author || !isset($author->ID)) {
        return '<p>No author found.</p>';
    }

    $author_id = $author->ID;

    // Get comments by this author
    $comments = get_comments(array(
        'user_id' => $author_id,
        'status' => 'approve',
        'number' => 10, // change this to control how many comments to show
    ));

    if (empty($comments)) {
        return '<p>No contributions made yet :( </p>';
    }

    $output = '<div class="author-contributions">';

    foreach ($comments as $comment) {
        $comment_post_link = get_permalink($comment->comment_post_ID);
        $comment_post_title = get_the_title($comment->comment_post_ID);
        $author_nickname = get_the_author_meta('nickname', $comment->user_id);
        $author_profile_url = get_author_posts_url($comment->user_id);

        $output .= '<div class="author-comment" style="margin-bottom: 1em; display: flex; align-items: flex-start; gap: 10px;">';
        
        // Avatar
        $output .= get_avatar($comment->user_id, 40, '', '', array('class' => 'author-avatar'));

        // Content block
        $output .= '<div class="comment-meta">';
        $output .= '<a href="' . esc_url($author_profile_url) . '">' . esc_html($author_nickname) . '</a><br>';
        $output .= '<strong><a href="' . esc_url($comment_post_link) . '">' . esc_html($comment_post_title) . '</a></strong><br>';
        $output .= '<small>' . esc_html(get_comment_date('F j, Y \a\t g:i a', $comment)) . '</small><br>';
        $output .= '<p style="margin: 0;">' . esc_html(wp_trim_words($comment->comment_content, 45)) . '</p>';
        $output .= '</div>';

        $output .= '</div>';
    }

    $output .= '</div>';

    return $output;
}
add_shortcode('current_author_comments', 'current_author_comments_shortcode');

// END CUSTOM AUTHOR COMMENTS

/**

 * WP CORE COMMENTS SECTION CUSTOMIZATIONS

 * https://www.malcare.com/blog/change-wordpress-login-url/

 * 1. Change "Logged in as [Nickname]. Log out?" message [Logged-in] - NOT WORKING

 * 2. Customize "You must be logged in" message with custom login page [Logged-out] - OK

 * 2.2 Redirect current user to his profile page if trying to visit his public profile

 * 3. Show [Nickname] instead of display name in comments [Public] - OK

 * 4. Link comment author name to their profile page [Public] - OK

 */

// 1.  


// 2. Login Redirect - Global WP - DISABLED

function redirect_user_login_url($login_url, $redirect){

    $frontend_login_url = $login_url; // store the original login URL

    $login_url = site_url( '/registration/', 'login' );

    // code to run on frontend only

    if (!is_admin() && strpos(wp_parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/wp-admin') === false) {

        // do nothing, use the custom login URL

    } else {

        $login_url = $frontend_login_url; // use the original login URL for /wp-admin

    }

	return $login_url;

}

add_filter( 'login_url', 'redirect_user_login_url', 10, 3 );



// 2.2 Redirect current user to his profile page - Bypass public profile - except admins.

function redirect_current_user_to_profile() {

    if ( is_author() ) {

        $current_user = wp_get_current_user();

        $user_profile = get_queried_object();

        // Only redirect if the user is logged in, viewing their own profile, and is NOT an administrator

        if (

            is_user_logged_in() &&

            $current_user->ID === $user_profile->ID &&

            !in_array('administrator', $current_user->roles)

        ) {

            wp_redirect(home_url('/profile'));

            exit;

        }

    }

}

add_action( 'template_redirect', 'redirect_current_user_to_profile' );

// 3 & 4: Handle comment author display (nickname + profile link)

function customize_comment_author_display($author, $comment_ID, $comment) {

    $user_id = $comment->user_id;

    if ($user_id) {

        // For registered users

        $user_info = get_userdata($user_id);

        if ($user_info && !empty($user_info->nickname)) {

            $display_name = $user_info->nickname;


            // Check if current user is the comment author

            if (get_current_user_id() == $user_id) {

                // Return just the nickname without link for current user

                return esc_html($display_name);

            } else {

                // Return linked nickname for other users

                return sprintf(

                    '<a href="%s" class="comment-author-link" rel="author">%s</a>',

                    esc_url(get_author_posts_url($user_id)),

                    esc_html($display_name)

                );

            }

        }

    }

    

    // For guest commenters (fallback to default with optional website link)

    if (!empty($comment->comment_author_url) && $comment->comment_author_url != 'http://') {

        return sprintf(

            '<a href="%s" rel="external nofollow ugc">%s</a>',

            esc_url($comment->comment_author_url),

            esc_html($author)

        );

    }

    

    return esc_html($author); // Default return

}

add_filter('get_comment_author', 'customize_comment_author_display', 10, 3);




// RESTRICTIONS - ACF

// Restrict only to youtube links
function validate_youtube_video_url($valid, $value, $field, $input) {
    if (!$value) {
        return $valid; // Allow empty if the field is not required
    }

    // Regex pattern to match YouTube video URLs
    $pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)[\w\-]{11}/';

    if (!preg_match($pattern, $value)) {
        return 'Please enter a valid YouTube video URL (e.g. https://www.youtube.com/watch?v=sample).';
    }

    return $valid;
}
add_filter('acf/validate_value/name=youtube_video_link', 'validate_youtube_video_url', 10, 4);


////// BACKEND - SHOW USER ROLE //////

function add_user_role_column($columns) {

    $columns['your_role'] = __('Registered as');

    return $columns;

}
add_filter('manage_users_columns', 'add_user_role_column');
function display_user_role($value, $column_name, $user_id) {

    if ($column_name == 'your_role') {

        $user_role = get_field('your_role', 'user_'.$user_id);

        return $user_role;

    }

    return $value;

}

add_action('manage_users_custom_column', 'display_user_role', 10, 3);

////// END BACKEND SHOW USER ROLE //////

// BACKEND - SHOW COMMENT COUNT IN USER LIST
// Add a new column to the Users list table
function add_comments_column_to_users($columns) {
    $columns['user_comments'] = 'Comments';
    return $columns;
}
add_filter('manage_users_columns', 'add_comments_column_to_users');

// Fill the custom column with data
function show_comments_column_in_users($value, $column_name, $user_id) {
    if ($column_name === 'user_comments') {
        // Count approved comments by user
        $count = get_comments(array(
            'user_id' => $user_id,
            'count' => true,
            'status' => 'approve',
        ));

        // Link to the comments list filtered by user
        $url = admin_url('edit-comments.php?user_id=' . $user_id);

        return '<a href="' . esc_url($url) . '">' . intval($count) . '</a>';
    }

    return $value;
}
add_filter('manage_users_custom_column', 'show_comments_column_in_users', 10, 3);

