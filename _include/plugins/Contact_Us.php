<?php
// Site-specific plugin class
if ( ! class_exists( 'Contact_Us_Plugin' ) ) {
	class Contact_Us_Plugin extends _Base_Class {
		protected $subjects = array(
			'info'       => 'Request for Information',
			'rsvp'       => 'R.S.V.P. to an Open Event',
			'directions' => 'Request for directions to an event',
			'other'      => '{other}',
		);
		protected $from_email;
		protected $from_name;

		public function __construct() {
			parent::__construct();

			//$this->init_filters();
		}

		/**
		 * Called when the class is first initialized
		 *
		 * @return    void
		 */
		public function on_class_init() {
			$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
			if (! $action) {
			    return;
            }

			$method = str_replace( '-', "_", $action );
			// Hooking up our function to process form submission
			$this->add_action( 'wp_ajax_nopriv_' . $action, $method );
			$this->add_action( 'wp_ajax_' . $action, $method );

		}

		public function submit_contact_us() {
			$success = true;
			if ( ! check_ajax_referer( 'khc-ajax-nonce', 'security', false ) ) {
				$success = false;
			}
			$success  = $this->send_contact_email( $_REQUEST );
			$response = json_encode( array(
				'success' => $success,
				'nextUrl' => ( $success ) ? site_url( 'thankyou' ) : null
			) );

			// response output
			header( "Content-Type: application/json" );
			echo $response;

			// IMPORTANT: don't forget to "exit"
			exit;
		}

		public function change_mail_from( $email ) {
			return $this->from_email;
		}

		public function change_mail_from_name( $name ) {
			return $this->from_name;
		}

		// Use filters to change the from address and name to that of the sender
		public function init_filters() {
			add_filter( 'wp_mail_from', array( 'Contact_Us_Plugin', 'change_mail_from' ) );
			add_filter( 'wp_mail_from_name', array( 'Contact_Us_Plugin', 'change_mail_from_name' ) );
		}

		private function send_contact_email( $request ) {
			$this->from_name  = $request['contact_name'];
			$this->from_email = $request['contact_email'];
			$to               = get_option( 'admin_email' ); //'khc@sacredwheel.org';
			$reason           = $request['contact_reason'];
			$other            = ( isset( $request['contact_other_reason'] ) ) ? $request['contact_other_reason'] : '';
			$subject          = ( $reason == 'other' ) ? $other : $this->subjects[ $reason ];
			$text             = $request['contact_text'];
			$message          = 'From: ' . $this->from_name . PHP_EOL;
			$message .= 'At: ' . $this->from_email . PHP_EOL;
			$message .= 'Message: ' . $text . PHP_EOL;

			$result = wp_mail( $to, $subject, $message );

			return $result;
		}

	}

	$plugin = new Contact_Us_Plugin();

}
