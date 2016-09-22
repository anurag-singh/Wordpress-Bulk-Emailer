<?php 
/*
* Plugin Name:      AS Bulk Email
 * Plugin URI:      http://anuragsingh.me/ 
 * Description:     Send bulk email to subscribers
 * Version:         1.0.0
 * Author:          Anurag Singh
 * Author URI:      http://anuragsingh.me/
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:     as-bulk-email
 * Domain Path:     /languages
 */
require_once("PHPMailer/PHPMailerAutoload.php");

add_action( 'init', 'create_cpt');
     function create_cpt()
    {
        $as_cpt = 'Bulk email';
        $sanitizedCptName = str_replace(' ', '_', strtolower($as_cpt));
        $last_character = substr($as_cpt, -1);
        if ($last_character === 'y') {
            $plural = substr_replace($as_cpt, 'ies', -1);
        }
        else {
            $plural = $as_cpt.'s'; // add 's' to convert singular name to plural
        }
        $textdomain = strtolower($as_cpt);
        $cap_type = 'post';
        $single = $as_cpt;
            $opts['can_export'] = TRUE;
            $opts['capability_type'] = $cap_type;
            $opts['description'] = '';
            $opts['exclude_from_search'] = FALSE;
            $opts['has_archive'] = TRUE;        // Enable 'Post type' archive page
            $opts['hierarchical'] = FALSE;
            $opts['map_meta_cap'] = TRUE;
            $opts['menu_icon'] = 'dashicons-email-alt';
            $opts['menu_position'] = 25;
            $opts['public'] = TRUE;
            $opts['publicly_querable'] = TRUE;
            $opts['query_var'] = TRUE;
            $opts['register_meta_box_cb'] = '';
            $opts['rewrite'] = FALSE;
            $opts['show_in_admin_bar'] = TRUE;  // 'Top Menu' bar
            $opts['show_in_menu'] = TRUE;
            $opts['show_in_nav_menu'] = TRUE;
            $opts['show_ui'] = TRUE;
            $opts['supports'] = array('title', 'editor');
            //$opts['supports'] = array('title', 'custom-fields');
            $opts['taxonomies'] = array();
            $opts['capabilities']['delete_others_posts'] = "delete_others_{$cap_type}s";
            $opts['labels']['add_new'] = __( "Add New {$single}", $textdomain );
            $opts['labels']['add_new_item'] = __( "Add New {$single}", $textdomain );
            $opts['labels']['all_items'] = __( 'All ' .$plural, $textdomain );
            $opts['labels']['edit_item'] = __( "Edit {$single}" , $textdomain);
            $opts['labels']['menu_name'] = __( $plural, $textdomain );
            $opts['labels']['name'] = __( $plural, $textdomain );
            $opts['labels']['name_admin_bar'] = __( $single, $textdomain );
            $opts['labels']['new_item'] = __( "New {$single}", $textdomain );
            $opts['labels']['not_found'] = __( "No {$plural} Found", $textdomain );
            $opts['labels']['not_found_in_trash'] = __( "No {$plural} Found in Trash", $textdomain );
            $opts['labels']['parent_item_colon'] = __( "Parent {$plural} :", $textdomain );
            $opts['labels']['search_items'] = __( "Search {$plural}", $textdomain );
            $opts['labels']['singular_name'] = __( $single, $textdomain );
            $opts['labels']['view_item'] = __( "View {$single}", $textdomain );
            $opts['rewrite']['ep_mask'] = EP_PERMALINK;
            $opts['rewrite']['feeds'] = FALSE;
            $opts['rewrite']['pages'] = TRUE;
            $opts['rewrite']['slug'] = __( strtolower( $single ), $textdomain );
            $opts['rewrite']['with_front'] = FALSE;
        register_post_type( $sanitizedCptName, $opts );
    }


function my_project_updated_send_email( $post_id ) {
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
	    return;
	}

	// If this is just a revision, don't send the email.
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	if (get_post_type($post->ID) !== 'bulk_email') {
		return;
	}

	send_email($post_id);
}
add_action( 'save_post', 'my_project_updated_send_email' );


function send_email ($post_id) {

	$subscribers = get_users( array ( 'role' => 'subscriber' ) );
    $emails      = array ();
    foreach ( $subscribers as $subscriber ){
        $emails[] = $subscriber->user_email;
    }

	$email_subject = get_the_title($post_id);
	$email_body = get_post_field('post_content', $post_id);

	
		//Create a new PHPMailer instance
        $mail = new PHPMailer;
        //Tell PHPMailer to use SMTP
        $mail->isSMTP();
        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $mail->SMTPDebug = 0;
        //Ask for HTML-friendly debug output
        $mail->Debugoutput = 'html';
        //Set the hostname of the mail server
        $mail->Host = 'smtp.gmail.com';
        // use
        // $mail->Host = gethostbyname('smtp.gmail.com');
        // if your network does not support SMTP over IPv6
        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $mail->Port = 587;
        //Set the encryption system to use - ssl (deprecated) or tls
        $mail->SMTPSecure = 'tls';
        //Whether to use SMTP authentication
        $mail->SMTPAuth = true;
        //Username to use for SMTP authentication - use full email address for gmail
        $mail->Username = "anurag.dselva@gmail.com";
        //Password to use for SMTP authentication
        $mail->Password = "Anurag1234567";
        //Set who the message is to be sent from
        $mail->setFrom('from@example.com', 'Vedic - IGNCA');
        //Set an alternative reply-to address
        $mail->addReplyTo('replyto@example.com', 'First Last');
        //Set who the message is to be sent to
        //$mail->addAddress($subscriberEmail);
        //$mail->addAddress($email);
        foreach($emails as $email)
		{
		   $mail->AddAddress($email);
		}
        //Set the subject line
        $mail->Subject = $email_subject;
        //Read an HTML message body from an external file, convert referenced images to embedded,

        $mail->isHTML(true);                                  // Set email format to HTML

        //convert HTML into a basic plain-text alternative body
        //$mail->Body = $email_body;
        $mail->Body = $email_body;

        //Replace the plain text body with one created manually
        //$mail->AltBody = 'This is a plain-text message body';
        //Attach an image file
        //$mail->addAttachment('images/phpmailer_mini.png');
        //send the message, check for errors
        if (!$mail->send()) {
        echo "Mailer Error: " . $mail->ErrorInfo;
        } else {
            $emailCounts++;
            return $emailCounts;
        //echo "Message sent!";
        }
}
