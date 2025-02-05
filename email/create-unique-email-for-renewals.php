<?php

/**
* Create A Unique Email For Membership Renewals in Paid Memberships Pro
* Make sure there is a folder /email/ in your plugin folder with the file named checkout_renewal.html
* 
* link: https://www.paidmembershipspro.com/customize-membership-checkout-confirmation-email-membership-renewals/
* 
* title: create a unique email for renewals
* layout: snippet
* collection: email
* category: email templates
*
* You can add this recipe to your site by creating a custom plugin
* or using the Code Snippets plugin available for free in the WordPress repository.
* Read this companion article for step-by-step directions on either method.
* https://www.paidmembershipspro.com/create-a-plugin-for-pmpro-customizations/
*/

// Add a filter to the pmpro_email filter
function my_pmpro_email_checkout_renewal( $email ) {	
	// Only filter emails with orders.
	if ( empty( $email->data['order_id'] ) ) {
		return $email;
	}
	//Bail if the template indicates is an admin email
	if ( strpos( $email->template, 'admin' ) !== false ) {
		return $email;
	}

	// Get order.
	$order = new MemberOrder( $email->data['order_id'] );

	// Make sure we have a real order.
	if ( empty( $order ) || empty( $order->id ) ) {
		return $email;
	}

	//If it's a proper Order let's fetch the user
	$user = get_userdata( $order->user_id );

	// Bail if it's not a renewal.
	if ( ! $order->is_renewal() ) {
		return $email;
	}

	//Instantiate Renew Email Template and assign its values to the filtered email object
	$renew = new PMPro_Email_Template_Renew( $user, $order );
	$email->email = $renew->get_recipient_email();
	$email->subject = $renew->get_default_subject();
	$email->template = $renew->get_template_slug();
	$email->body = $renew->get_default_body();
	$email->data = $renew->get_email_template_variables();
	return $email;
}
add_filter( 'pmpro_email_filter', 'my_pmpro_email_checkout_renewal', 20 );

// Create a new email template class for renewals
class PMPro_Email_Template_Renew extends PMPro_Email_Template {
	protected $user;
	protected $order;
	function __construct( $user, $order ) {
		$this->user = $user;
		$this->order = $order;
	}

	public static function get_template_slug() {
		return 'renew';
	}

	public static function get_template_name() {
		return esc_html__( 'Renew', 'paid-memberships-pro' );
	}

	public static function get_template_description() {
		return esc_html__( 'This email is sent to the member where the membership is renewed', 'paid-memberships-pro' );
	}

	public static function get_default_subject() {
		return esc_html__( 'Thank you for your renewal.', 'paid-memberships-pro' );
	}

	// This is the default body of the email template, which can be overridden by the user.
	public static function get_default_body() {
		return  wp_kses_post( '<p>You have renewed your membership successfully.</p>

<p>Account: !!display_name!! (!!user_email!!)</p>
<p>Membership Level: !!membership_level_name!!</p>
<p>Membership Fee: !!membership_cost!!</p>
!!membership_expiration!! !!discount_code!!

<p>
	Order #!!order_id!! on !!order_date!!<br />
	Total Billed: !!order_total!!
</p>
<p>
	Billing Information:<br />
	!!billing_address!!
</p>

<p>
	!!cardtype!!: !!accountnumber!!<br />
	Expires: !!expirationmonth!!/!!expirationyear!!
</p>

<p>Log in to your membership account here: !!login_url!!</p>', 'paid-memberships-pro' );
	}

	public function get_recipient_email() {
		return $this->user->user_email;
	}

	public function get_recipient_name() {
		return $this->user->display_name;
	}

	public static function get_email_template_variables_with_description() {
		return array(
			'!!subject!!' => esc_html__( 'The subject of the email.', 'paid-memberships-pro' ),
			'!!name!!' => esc_html__( 'The name of the email recipient.', 'paid-memberships-pro' ),
			'!!display_name!!' => esc_html__( 'The name of the email recipient.', 'paid-memberships-pro' ),
			'!!user_login!!' => esc_html__( 'The login name of the email recipient.', 'paid-memberships-pro' ),
			'!!membership_id!!' => esc_html__( 'The ID of the membership level.', 'paid-memberships-pro' ),
			'!!membership_level_name!!' => esc_html__( 'The name of the membership level.', 'paid-memberships-pro' ),
			'!!confirmation_message!!' => esc_html__( 'The confirmation message for the membership level.', 'paid-memberships-pro' ),
			'!!membership_cost!!' => esc_html__( 'The cost of the membership level.', 'paid-memberships-pro' ),
			'!!user_email!!' => esc_html__( 'The email address of the email recipient.', 'paid-memberships-pro' ),
			'!!membership_expiration!!' => esc_html__( 'The expiration date of the membership level.', 'paid-memberships-pro' ),
			'!!discount_code!!' => esc_html__( 'The discount code used for the membership level.', 'paid-memberships-pro' ),
			'!!order_id!!' => esc_html__( 'The ID of the order.', 'paid-memberships-pro' ),
			'!!order_date!!' => esc_html__( 'The date of the order.', 'paid-memberships-pro' ),
			'!!order_total!!' => esc_html__( 'The total cost of the order.', 'paid-memberships-pro' ),
			'!!billing_name!!' => esc_html__( 'Billing Info Name', 'paid-memberships-pro' ),
			'!!billing_street!!' => esc_html__( 'Billing Info Street', 'paid-memberships-pro' ),
			'!!billing_street2!!' => esc_html__( 'Billing Info Street 2', 'paid-memberships-pro' ),
			'!!billing_city!!' => esc_html__( 'Billing Info City', 'paid-memberships-pro' ),
			'!!billing_state!!' => esc_html__( 'Billing Info State', 'paid-memberships-pro' ),
			'!!billing_zip!!' => esc_html__( 'Billing Info Zip', 'paid-memberships-pro' ),
			'!!billing_country!!' => esc_html__( 'Billing Info Country', 'paid-memberships-pro' ),
			'!!billing_phone!!' => esc_html__( 'Billing Info Phone', 'paid-memberships-pro' ),
			'!!billing_address!!' => esc_html__( 'Billing Info Complete Address', 'paid-memberships-pro' ),
			'!!cardtype!!' => esc_html__( 'Credit Card Type', 'paid-memberships-pro' ),
			'!!accountnumber!!' => esc_html__( 'Credit Card Number (last 4 digits)', 'paid-memberships-pro' ),
			'!!expirationmonth!!' => esc_html__( 'Credit Card Expiration Month (mm format)', 'paid-memberships-pro' ),
			'!!expirationyear!!' => esc_html__( 'Credit Card Expiration Year (yyyy format)', 'paid-memberships-pro' ),
		);
	}

	public function get_email_template_variables() {
		global $wpdb;
		$user = $this->user;
		$order = $this->order;
		$level = pmpro_getSpecificMembershipLevelForUser( $user->ID, $order->membership_id );

		$membership_expiration = '';
		$enddate = $wpdb->get_var(
			"SELECT UNIX_TIMESTAMP(CONVERT_TZ(enddate, '+00:00', @@global.time_zone)) 
			 FROM $wpdb->pmpro_memberships_users 
			 WHERE user_id = '" . $user->ID . "' AND status = 'active' LIMIT 1");

		if( $enddate ) {
			$membership_expiration = "<p>" . sprintf( __( "This membership will expire on %s.", 'paid-memberships-pro' ), 
				date_i18n( get_option( 'date_format' ), $enddate ) ) . "</p>\n";
		}

		$discount_code = '';
		if( $order->getDiscountCode() ) {
			$discount_code = "<p>" . esc_html__( "Discount Code", 'paid-memberships-pro' ) . ": " . $order->discount_code->code . "</p>\n";
		}

		//We need base variables to be able to use them in the email template because the method that gets the email template variables is protected
		$email_template_variables = array(
			'sitename' => get_option( 'blogname' ),
			'siteemail' => get_option( 'pmpro_from_email' ),
			'site_url'  => home_url(),
			'levels_url' => pmpro_url( 'levels' ),
			'levels_link' => pmpro_url( 'levels' ),
			'login_link' => pmpro_login_url(), 
			'login_url' => pmpro_login_url(),
			'header_name' => $this->get_recipient_name(),
			'user_login' => $user->user_login,
			'user_email' => $user->user_email,
			'display_name' => $user->display_name,
			'membership_id' => $order->membership_id,
			'membership_level_name' => $level->name,
			'membership_cost' => pmpro_getLevelCost( $level ),
			'discount_code' => $discount_code,
			'membership_expiration' => $membership_expiration,
			'order_id' => $order->code,
			'order_total' => $order->total,
			'order_date' => date_i18n( get_option( 'date_format' ), $order->timestamp ),
			'billing_name' => $order->billing->name,
			'billing_street' => $order->billing->street,
			'billing_street2' => $order->billing->street2,
			'billing_city' => $order->billing->city,
			'billing_state' => $order->billing->state,
			'billing_zip' => $order->billing->zip,
			'billing_country' => $order->billing->country,
			'billing_phone' => $order->billing->phone,
			'cardtype' => $order->cardtype,
			'accountnumber' => hideCardNumber( $order->accountnumber ),
			'expirationmonth' => $order->expirationmonth,
			'expirationyear' => $order->expirationyear,
			'order_link' => pmpro_login_url( pmpro_url( 'invoice', '?invoice=' . $order->code ) ),
			'order_url' => pmpro_login_url( pmpro_url( 'invoice', '?invoice=' . $order->code ) ),
			'billing_address' => pmpro_formatAddress(
				$order->billing->name,
				$order->billing->street,
				$order->billing->street2,
				$order->billing->city,
				$order->billing->state,
				$order->billing->zip,
				$order->billing->country,
				$order->billing->phone
			),
		);
		return $email_template_variables;
	}
}
// Add the email template to the list of email templates
function pmpro_email_templates_renew( $email_templates ) {
	$email_templates['renew'] = 'PMPro_Email_Template_Renew';

	return $email_templates;
}
add_filter( 'pmpro_email_templates', 'pmpro_email_templates_renew' );
