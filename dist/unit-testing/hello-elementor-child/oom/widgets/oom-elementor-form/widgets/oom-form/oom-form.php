<?php

/**
 * OOm Elelemtor Form Widget
 * @author: oom_cn
 * @since: 1.0.0
 * @version: 1.0.0
 */

use ElementorPro\Plugin;
use \Elementor\Controls_Manager;
use \Elementor\Widget_Base;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Typography;

add_action('elementor/widgets/widgets_registered', 'register_oom_form_widget');
function register_oom_form_widget() {
	if (class_exists('Elementor\Widget_Base')){
		class OOm_Form_Widget extends \Elementor\Widget_Base {

			/**
			 * Get widget name.
			 * @since 1.0.0
			 * @access public
			 *
			 * @return string Widget name.
			 */
			public function get_name() {
				return 'oom-elementor-form';
			}

			/**
			 * Get widget title.
			 * @since 1.0.0
			 * @access public
			 *
			 * @return string Widget title.
			 */
			public function get_title() {
				return 'OOm Form';
			}

			/**
			 * Get widget icon.
			 * @since 1.0.0
			 * @access public
			 *
			 * @return string Widget icon.
			 */
			public function get_icon() {
				return 'eicon-form-horizontal';
			}

			/**
			 * Get widget categories.
			 * @since 1.0.0
			 * @access public
			 *
			 * @return array Widget categories.
			 */
			public function get_categories() {
				return [ 'basic' ];
			}
			
			/**
			 * Get current post ID.
			 * @since 1.0.0
			 * @access public
			 *
			 * @return array current post ID.
			 */
			public static function get_current_post_id() {
				if ( isset( Plugin::elementor()->documents ) && Plugin::elementor()->documents->get_current() ) {
					return Plugin::elementor()->documents->get_current()->get_main_id();
				}
			
				return get_the_ID();
			}
			

			/**
			 * Register widget controls.
			 * @since 1.0.0
			 * @access protected
			 */
			protected function _register_controls() {

				// Repeater for form fields
				$repeater = new \Elementor\Repeater();
				
				$field_types = [
					'text' => esc_html__( 'Text', 'oom-elementor-form' ),
					'email' => esc_html__( 'Email', 'oom-elementor-form' ),
					'textarea' => esc_html__( 'Textarea', 'oom-elementor-form' ),
					'url' => esc_html__( 'URL', 'oom-elementor-form' ),
					'tel' => esc_html__( 'Tel', 'oom-elementor-form' ),
					'radio' => esc_html__( 'Radio', 'oom-elementor-form' ),
					'select' => esc_html__( 'Select', 'oom-elementor-form' ),
					'checkbox' => esc_html__( 'Checkbox', 'oom-elementor-form' ),
					'acceptance' => esc_html__( 'Acceptance', 'oom-elementor-form' ),
					'number' => esc_html__( 'Number', 'oom-elementor-form' ),
					'date' => esc_html__( 'Date', 'oom-elementor-form' ),
					'time' => esc_html__( 'Time', 'oom-elementor-form' ),
					'password' => esc_html__( 'Password', 'oom-elementor-form' ),
					'html' => esc_html__( 'HTML', 'oom-elementor-form' ),
					'hidden' => esc_html__( 'Hidden', 'oom-elementor-form' ),
				];

			
				$repeater->start_controls_tabs( 'form_fields_tabs' );

				$repeater->start_controls_tab( 'form_fields_content_tab', [
					'label' => esc_html__( 'Content', 'oom-elementor-form' ),
				] );

				$repeater->add_control(
					'field_type',
					[
						'label' => esc_html__( 'Type', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'options' => $field_types,
						'default' => 'text',
					]
				);

				$repeater->add_control(
					'field_label',
					[
						'label' => esc_html__( 'Label', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
						'dynamic' => [
							'active' => true,
						],
					]
				);

				$repeater->add_control(
					'placeholder',
					[
						'label' => esc_html__( 'Placeholder', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
						'conditions' => [
							'terms' => [
								[
									'name' => 'field_type',
									'operator' => 'in',
									'value' => [
										'tel',
										'text',
										'email',
										'textarea',
										'number',
										'url',
										'password',
									],
								],
							],
						],
						'dynamic' => [
							'active' => true,
						],
					]
				);

				$repeater->add_control(
					'required',
					[
						'label' => esc_html__( 'Required', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'return_value' => 'true',
						'default' => '',
						'conditions' => [
							'terms' => [
								[
									'name' => 'field_type',
									'operator' => '!in',
									'value' => [
										'checkbox',
										'hidden',
										'html',
										'step',
									],
								],
							],
						],
					]
				);

				$repeater->add_control(
					'field_options',
					[
						'label' => esc_html__( 'Options', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXTAREA,
						'default' => '',
						'description' => esc_html__( 'Enter each option in a separate line. To differentiate between label and value, separate them with a pipe char ("|"). For example: First Name|f_name', 'oom-elementor-form' ),
						'conditions' => [
							'terms' => [
								[
									'name' => 'field_type',
									'operator' => 'in',
									'value' => [
										'select',
										'checkbox',
										'radio',
									],
								],
							],
						],
					]
				);

				$repeater->add_control(
					'allow_multiple',
					[
						'label' => esc_html__( 'Multiple Selection', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'return_value' => 'true',
						'conditions' => [
							'terms' => [
								[
									'name' => 'field_type',
									'value' => 'select',
								],
							],
						],
					]
				);

				$repeater->add_control(
					'select_size',
					[
						'label' => esc_html__( 'Rows', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::NUMBER,
						'min' => 2,
						'step' => 1,
						'conditions' => [
							'terms' => [
								[
									'name' => 'field_type',
									'value' => 'select',
								],
								[
									'name' => 'allow_multiple',
									'value' => 'true',
								],
							],
						],
					]
				);

				$repeater->add_control(
					'inline_list',
					[
						'label' => esc_html__( 'Inline List', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'return_value' => 'elementor-subgroup-inline',
						'default' => '',
						'conditions' => [
							'terms' => [
								[
									'name' => 'field_type',
									'operator' => 'in',
									'value' => [
										'checkbox',
										'radio',
									],
								],
							],
						],
					]
				);

				$repeater->add_control(
					'field_html',
					[
						'label' => esc_html__( 'HTML', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXTAREA,
						'dynamic' => [
							'active' => true,
						],
						'conditions' => [
							'terms' => [
								[
									'name' => 'field_type',
									'operator' => 'in',
									'value' => [
										'html',
										'acceptance',
									],
								],
							],
						],
					]
				);

				$repeater->add_responsive_control(
					'width',
					[
						'label' => esc_html__( 'Column Width', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'options' => [
							'' => esc_html__( 'Default', 'oom-elementor-form' ),
							'100' => '100%',
							'80' => '80%',
							'75' => '75%',
							'70' => '70%',
							'66' => '66%',
							'60' => '60%',
							'50' => '50%',
							'40' => '40%',
							'33' => '33%',
							'30' => '30%',
							'25' => '25%',
							'20' => '20%',
						],
						'default' => '100',
						'conditions' => [
							'terms' => [
								[
									'name' => 'field_type',
									'operator' => '!in',
									'value' => [
										'hidden',
										'step',
									],
								],
							],
						],
					]
				);

				$repeater->add_control(
					'rows',
					[
						'label' => esc_html__( 'Rows', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::NUMBER,
						'default' => 4,
						'conditions' => [
							'terms' => [
								[
									'name' => 'field_type',
									'value' => 'textarea',
								],
							],
						],
					]
				);

				$repeater->add_control(
					'css_classes',
					[
						'label' => esc_html__( 'CSS Classes', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::HIDDEN,
						'default' => '',
						'title' => esc_html__( 'Add your custom class WITHOUT the dot. e.g: my-class', 'oom-elementor-form' ),
					]
				);

				$repeater->end_controls_tab();

				$repeater->start_controls_tab(
					'form_fields_advanced_tab',
					[
						'label' => esc_html__( 'Advanced', 'oom-elementor-form' ),
						'condition' => [
							'field_type!' => 'html',
						],
					]
				);

				$repeater->add_control(
					'field_value',
					[
						'label' => esc_html__( 'Default Value', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
						'dynamic' => [
							'active' => true,
						],
						'ai' => [
							'active' => false,
						],
						'conditions' => [
							'terms' => [
								[
									'name' => 'field_type',
									'operator' => 'in',
									'value' => [
										'text',
										'email',
										'textarea',
										'url',
										'tel',
										'radio',
										'select',
										'number',
										'date',
										'time',
										'hidden',
									],
								],
							],
						],
					]
				);

				$random_id = wp_generate_password( 6, false, false );

				$repeater->add_control(
					'custom_id',
					[
						'label' => esc_html__( 'ID', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'description' => esc_html__( 'Please make sure the ID is unique and not used elsewhere in this form. This field allows `A-z 0-9` & underscore chars without spaces.', 'oom-elementor-form' ),
						'render_type' => 'none',
						'required' => true,
						'default' => $random_id,
						'dynamic' => [
							'active' => true,
						],
						'ai' => [
							'active' => false,
						],
					]
				);

				$repeater->end_controls_tab();

				$repeater->end_controls_tabs();

				$this->start_controls_section(
					'section_form_fields',
					[
						'label' => esc_html__( 'Form Fields', 'oom-elementor-form' ),
					]
				);

				$this->add_control(
					'form_name',
					[
						'label' => esc_html__( 'Form Name', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => esc_html__( 'New Form', 'oom-elementor-form' ),
						'placeholder' => esc_html__( 'Form Name', 'oom-elementor-form' ),
					]
				);

				$this->add_control(
					'form_fields',
					[
						'type' => \Elementor\Controls_Manager::REPEATER,
						'fields' => $repeater->get_controls(),
						'default' => [
							[
								'custom_id' => 'name',
								'field_type' => 'text',
								'field_label' => esc_html__( 'Name', 'oom-elementor-form' ),
								'placeholder' => esc_html__( 'Name', 'oom-elementor-form' ),
								'width' => '100',
								'dynamic' => [
									'active' => true,
								],
							],
							[
								'custom_id' => 'email',
								'field_type' => 'email',
								'required' => 'true',
								'field_label' => esc_html__( 'Email', 'oom-elementor-form' ),
								'placeholder' => esc_html__( 'Email', 'oom-elementor-form' ),
								'width' => '100',
							],
							[
								'custom_id' => 'message',
								'field_type' => 'textarea',
								'field_label' => esc_html__( 'Message', 'oom-elementor-form' ),
								'placeholder' => esc_html__( 'Message', 'oom-elementor-form' ),
								'width' => '100',
							],
						],
						'title_field' => '{{{ field_label }}}',
					]
				);

				$this->add_control(
					'input_size',
					[
						'label' => esc_html__( 'Input Size', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'options' => [
							'xs' => esc_html__( 'Extra Small', 'oom-elementor-form' ),
							'sm' => esc_html__( 'Small', 'oom-elementor-form' ),
							'md' => esc_html__( 'Medium', 'oom-elementor-form' ),
							'lg' => esc_html__( 'Large', 'oom-elementor-form' ),
							'xl' => esc_html__( 'Extra Large', 'oom-elementor-form' ),
						],
						'default' => 'sm',
						'separator' => 'before',
					]
				);

				$this->add_control(
					'show_labels',
					[
						'label' => esc_html__( 'Label', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'label_on' => esc_html__( 'Show', 'oom-elementor-form' ),
						'label_off' => esc_html__( 'Hide', 'oom-elementor-form' ),
						'return_value' => 'true',
						'default' => 'true',
						'separator' => 'before',
					]
				);

				$this->add_control(
					'mark_required',
					[
						'label' => esc_html__( 'Required Mark', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'label_on' => esc_html__( 'Show', 'oom-elementor-form' ),
						'label_off' => esc_html__( 'Hide', 'oom-elementor-form' ),
						'return_value' => 'true',
						'default' => 'false',
						'condition' => [
							'show_labels!' => '',
						],
					]
				);

				$this->add_control(
					'label_position',
					[
						'label' => esc_html__( 'Label Position', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::HIDDEN,
						'options' => [
							'above' => esc_html__( 'Above', 'oom-elementor-form' ),
							'inline' => esc_html__( 'Inline', 'oom-elementor-form' ),
						],
						'default' => 'above',
						'condition' => [
							'show_labels!' => '',
						],
					]
				);

				$this->end_controls_section();

				$this->start_controls_section(
					'section_buttons',
					[
						'label' => esc_html__( 'Buttons', 'oom-elementor-form' ),
					]
				);

				$this->add_control(
					'button_size',
					[
						'label' => esc_html__( 'Size', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => 'sm',
						'options' => [
								'xs' => esc_html__( 'Extra Small', 'oom-elementor-form' ),
								'sm' => esc_html__( 'Small', 'oom-elementor-form' ),
								'md' => esc_html__( 'Medium', 'oom-elementor-form' ),
								'lg' => esc_html__( 'Large', 'oom-elementor-form' ),
								'xl' => esc_html__( 'Extra Large', 'oom-elementor-form' ),
							],
					]
				);

				$this->add_responsive_control(
					'button_width',
					[
						'label' => esc_html__( 'Column Width', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'options' => [
							'' => esc_html__( 'Default', 'oom-elementor-form' ),
							'100' => '100%',
							'80' => '80%',
							'75' => '75%',
							'70' => '70%',
							'66' => '66%',
							'60' => '60%',
							'50' => '50%',
							'40' => '40%',
							'33' => '33%',
							'30' => '30%',
							'25' => '25%',
							'20' => '20%',
						],
						'default' => '100',
						'frontend_available' => true,
					]
				);

				$this->add_control(
					'heading_submit_button',
					[
						'label' => esc_html__( 'Submit Button', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::HEADING,
					]
				);

				$this->add_control(
					'button_text',
					[
						'label' => esc_html__( 'Submit', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => esc_html__( 'Send', 'oom-elementor-form' ),
						'placeholder' => esc_html__( 'Send', 'oom-elementor-form' ),
						'dynamic' => [
							'active' => true,
						],
						'ai' => [
							'active' => false,
						],
					]
				);

				$this->add_control(
					'selected_button_icon',
					[
						'label' => esc_html__( 'Icon', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::ICONS,
						'skin' => 'inline',
						'label_block' => false,
					]
				);

				$start = is_rtl() ? 'right' : 'left';
				$end = is_rtl() ? 'left' : 'right';

				$this->add_control(
					'button_icon_align',
					[
						'label' => esc_html__( 'Icon Position', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::CHOOSE,
						'default' => is_rtl() ? 'row-reverse' : 'row',
						'options' => [
							'row' => [
								'title' => esc_html__( 'Start', 'oom-elementor-form' ),
								'icon' => "eicon-h-align-{$start}",
							],
							'row-reverse' => [
								'title' => esc_html__( 'End', 'oom-elementor-form' ),
								'icon' => "eicon-h-align-{$end}",
							],
						],
						'selectors_dictionary' => [
							'left' => is_rtl() ? 'row-reverse' : 'row',
							'right' => is_rtl() ? 'row' : 'row-reverse',
						],
						'selectors' => [
							'{{WRAPPER}} .elementor-button-content-wrapper' => 'flex-direction: {{VALUE}};',
						],
						'condition' => [
							'button_text!' => '',
							'selected_button_icon[value]!' => '',
						],
					]
				);

				$this->add_control(
					'button_icon_indent',
					[
						'label' => esc_html__( 'Icon Spacing', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'size_units' => [ 'px', 'em', 'rem', 'custom' ],
						'range' => [
							'px' => [
								'max' => 100,
							],
							'em' => [
								'max' => 10,
							],
							'rem' => [
								'max' => 10,
							],
						],
						'condition' => [
							'button_text!' => '',
							'selected_button_icon[value]!' => '',
						],
						'selectors' => [
							'{{WRAPPER}} .elementor-button span' => 'gap: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'button_css_id',
					[
						'label' => esc_html__( 'Button ID', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
						'ai' => [
							'active' => false,
						],
						'title' => esc_html__( 'Add your custom id WITHOUT the Pound key. e.g: my-id', 'oom-elementor-form' ),
						'description' => sprintf(
							esc_html__( 'Please make sure the ID is unique and not used elsewhere on the page this form is displayed. This field allows %1$sA-z 0-9%2$s & underscore chars without spaces.', 'oom-elementor-form' ),
							'<code>',
							'</code>'
						),
						'separator' => 'before',
						'dynamic' => [
							'active' => true,
						],
					]
				);

				$this->end_controls_section();

				// Email Settings Section
				$this->start_controls_section(
					'email_settings_section',
					[
						'label' => __( 'Email', 'oom-elementor-form' ),
						'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
					]
				);

				$this->add_control(
					'email_to',
					[
						'label' => __( 'To', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => __( 'project@oom.com.sg', 'oom-elementor-form' ),
						'description' => __( 'Email address to send the form data to.', 'oom-elementor-form' ),
					]
				);

				$this->add_control(
					'custom_panel_notice',
					[
						'type' => \Elementor\Controls_Manager::NOTICE,
						'notice_type' => 'warning',
						'dismissible' => true,
						'heading' => esc_html__( 'Shortcode Notice', 'oom-elementor-form' ),
						'content' => esc_html__( 'You can use shortcode for all the fields like with [field_fieldid] eg:[field_email]', 'oom-elementor-form' ),
					]
				);

				$this->add_control(
					'email_subject',
					[
						'label' => __( 'Subject', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => __( 'Email Subject', 'oom-elementor-form' ),
						'description' => __( 'Subject line for the email.', 'oom-elementor-form' ),
					]
				);

				$this->add_control(
					'email_message',
					[
						'label' => __( 'Message', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXTAREA,
						'default' => '[all-fields]',
						'description' => esc_html__( 'By default, all form fields are sent via <code>[all-fields]</code>  shortcode. To customize sent fields, copy the shortcode that appears inside each field and paste it above.' ),
					]
				);

				$this->add_control(
					'email_from',
					[
						'label' => __( 'From Email', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => __( 'email@domail.com', 'oom-elementor-form' ),
					]
				);

				$this->add_control(
					'email_reply_to',
					[
						'label' => __( 'Reply-To', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => __( 'email@domail.com', 'oom-elementor-form' ),
					]
				);

				$this->add_control(
					'email_cc',
					[
						'label' => __( 'Cc', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
					]
				);

				$this->add_control(
					'email_bcc',
					[
						'label' => __( 'Bcc', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
					]
				);

				$this->add_control(
					'meta_data',
					[
						'label' => __( 'Meta Data', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::SELECT2,
						'multiple' => true,
						'options' => [
							'date'       => __( 'Date', 'oom-elementor-form' ),
							'time'       => __( 'Time', 'oom-elementor-form' ),
							'page_url'   => __( 'Page URL', 'oom-elementor-form' ),
							'user_agent' => __( 'User Agent', 'oom-elementor-form' ),
							'remote_ip'  => __( 'Remote IP', 'oom-elementor-form' ),
						],
						'label_block' => true,
					]
				);

				$this->end_controls_section();
				
				// Email2 Settings Section
				$this->start_controls_section(
					'email2_settings_section',
					[
						'label' => __( 'Email 2', 'oom-elementor-form' ),
						'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
					]
				);

				$this->add_control(
					'email2_to',
					[
						'label' => __( 'To', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => __( '', 'oom-elementor-form' ),
						'description' => __( 'Email address to send the form data to.', 'oom-elementor-form' ),
					]
				);

				$this->add_control(
					'custom_panel_notice2',
					[
						'type' => \Elementor\Controls_Manager::NOTICE,
						'notice_type' => 'warning',
						'dismissible' => true,
						'heading' => esc_html__( 'Shortcode Notice', 'oom-elementor-form' ),
						'content' => esc_html__( 'You can use shortcode for all the fields like with [field_fieldid] eg:[field_email]', 'oom-elementor-form' ),
					]
				);

				$this->add_control(
					'email2_subject',
					[
						'label' => __( 'Subject', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => __( 'Email Subject', 'oom-elementor-form' ),
						'description' => __( 'Subject line for the email.', 'oom-elementor-form' ),
					]
				);

				$this->add_control(
					'email2_message',
					[
						'label' => __( 'Message', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXTAREA,
						'default' => '[all-fields]',
						'description' => esc_html__( 'By default, all form fields are sent via <code>[all-fields]</code>  shortcode. To customize sent fields, copy the shortcode that appears inside each field and paste it above.' ),
					]
				);

				$this->add_control(
					'email2_from',
					[
						'label' => __( 'From Email', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => __( 'email@domail.com', 'oom-elementor-form' ),
					]
				);

				$this->add_control(
					'email2_reply_to',
					[
						'label' => __( 'Reply-To', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => __( 'email@domail.com', 'oom-elementor-form' ),
					]
				);

				$this->add_control(
					'email2_cc',
					[
						'label' => __( 'Cc', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
					]
				);

				$this->add_control(
					'email2_bcc',
					[
						'label' => __( 'Bcc', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
					]
				);

				$this->add_control(
					'meta_data2',
					[
						'label' => __( 'Meta Data', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::SELECT2,
						'multiple' => true,
						'options' => [
							'date'       => __( 'Date', 'oom-elementor-form' ),
							'time'       => __( 'Time', 'oom-elementor-form' ),
							'page_url'   => __( 'Page URL', 'oom-elementor-form' ),
							'user_agent' => __( 'User Agent', 'oom-elementor-form' ),
							'remote_ip'  => __( 'Remote IP', 'oom-elementor-form' ),
						],
						'label_block' => true,
					]
				);

				$this->end_controls_section();

				// Redirect Settings Section
				$this->start_controls_section(
					'redirect_settings_section',
					[
						'label' => __( 'Redirect', 'oom-elementor-form' ),
						'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
					]
				);

				$this->add_control(
					'redirect_link',
					[
						'label' => esc_html__( 'Redirect To', 'oom-elementor-form' ),
						'type' => \Elementor\Controls_Manager::URL,
						'options' => false,
						'label_block' => true,
						'dynamic' => [
							'active' => true,
						],
					]
				);

				$this->end_controls_section();


				$this->start_controls_section(
					'section_form_style',
					[
						'label' => esc_html__( 'Form', 'oom-elementor-form' ),
						'tab' => Controls_Manager::TAB_STYLE,
					]
				);

				$this->add_control(
					'column_gap',
					[
						'label' => esc_html__( 'Columns Gap', 'oom-elementor-form' ),
						'type' => Controls_Manager::SLIDER,
						'size_units' => [ 'px', 'em', 'rem', 'custom' ],
						'default' => [
							'size' => 10,
						],
						'range' => [
							'px' => [
								'max' => 60,
							],
							'em' => [
								'max' => 6,
							],
							'rem' => [
								'max' => 6,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .elementor-field-group' => 'padding-right: calc( {{SIZE}}{{UNIT}}/2 ); padding-left: calc( {{SIZE}}{{UNIT}}/2 );',
							'{{WRAPPER}} .elementor-form-fields-wrapper' => 'margin-left: calc( -{{SIZE}}{{UNIT}}/2 ); margin-right: calc( -{{SIZE}}{{UNIT}}/2 );',
						],
					]
				);

				$this->add_control(
					'row_gap',
					[
						'label' => esc_html__( 'Rows Gap', 'oom-elementor-form' ),
						'type' => Controls_Manager::SLIDER,
						'size_units' => [ 'px', 'em', 'rem', 'custom' ],
						'default' => [
							'size' => 10,
						],
						'range' => [
							'px' => [
								'max' => 60,
							],
							'em' => [
								'max' => 6,
							],
							'rem' => [
								'max' => 6,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .elementor-field-group' => 'margin-bottom: {{SIZE}}{{UNIT}};',
							'{{WRAPPER}} .elementor-field-group.recaptcha_v3-bottomleft, {{WRAPPER}} .elementor-field-group.recaptcha_v3-bottomright' => 'margin-bottom: 0;',
							'{{WRAPPER}} .elementor-form-fields-wrapper' => 'margin-bottom: -{{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'heading_label',
					[
						'label' => esc_html__( 'Label', 'oom-elementor-form' ),
						'type' => Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

				$this->add_control(
					'label_spacing',
					[
						'label' => esc_html__( 'Spacing', 'oom-elementor-form' ),
						'type' => Controls_Manager::SLIDER,
						'size_units' => [ 'px', 'em', 'rem', 'custom' ],
						'default' => [
							'size' => 0,
						],
						'range' => [
							'px' => [
								'max' => 60,
							],
							'em' => [
								'max' => 6,
							],
							'rem' => [
								'max' => 6,
							],
						],
						'selectors' => [
							'body {{WRAPPER}} label' => 'padding-bottom: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'label_color',
					[
						'label' => esc_html__( 'Text Color', 'oom-elementor-form' ),
						'type' => Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} label' => 'color: {{VALUE}};',
						],
						'global' => [
							'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_TEXT,
						],
					]
				);

				$this->add_control(
					'mark_required_color',
					[
						'label' => esc_html__( 'Mark Color', 'oom-elementor-form' ),
						'type' => Controls_Manager::COLOR,
						'default' => '',
						'selectors' => [
							'{{WRAPPER}} label:after' => 'color: {{COLOR}};',
						],
						'condition' => [
							'mark_required' => 'yes',
						],
					]
				);

				$this->add_group_control(
					Group_Control_Typography::get_type(),
					[
						'name' => 'label_typography',
						'selector' => '{{WRAPPER}} label',
						'global' => [
							'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_TEXT,
						],
					]
				);

				$this->add_control(
					'heading_html',
					[
						'label' => esc_html__( 'HTML Field', 'oom-elementor-form' ),
						'type' => Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

				$this->add_control(
					'html_spacing',
					[
						'label' => esc_html__( 'Spacing', 'oom-elementor-form' ),
						'type' => Controls_Manager::SLIDER,
						'size_units' => [ 'px', 'em', 'rem', 'custom' ],
						'default' => [
							'size' => 0,
						],
						'range' => [
							'px' => [
								'max' => 60,
							],
							'em' => [
								'max' => 6,
							],
							'rem' => [
								'max' => 6,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .field-type-html' => 'padding-bottom: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'html_color',
					[
						'label' => esc_html__( 'Color', 'oom-elementor-form' ),
						'type' => Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .field-type-html' => 'color: {{VALUE}};',
						],
						'global' => [
							'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_TEXT,
						],
					]
				);

				$this->add_group_control(
					Group_Control_Typography::get_type(),
					[
						'name' => 'html_typography',
						'selector' => '{{WRAPPER}} .field-type-html',
						'global' => [
							'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_TEXT,
						],
					]
				);

				$this->end_controls_section();

				$this->start_controls_section(
					'section_field_style',
					[
						'label' => esc_html__( 'Field', 'oom-elementor-form' ),
						'tab' => Controls_Manager::TAB_STYLE,
					]
				);

				$this->add_control(
					'field_text_color',
					[
						'label' => esc_html__( 'Text Color', 'oom-elementor-form' ),
						'type' => Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .elementor-field-group .elementor-field' => 'color: {{VALUE}};',
						],
						'global' => [
							'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_TEXT,
						],
					]
				);

				$this->add_group_control(
					Group_Control_Typography::get_type(),
					[
						'name' => 'field_typography',
						'selector' => '{{WRAPPER}} .elementor-field-group .elementor-field, {{WRAPPER}} .elementor-field-subgroup label',
						'global' => [
							'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_TEXT,
						],
					]
				);

				$this->add_control(
					'field_background_color',
					[
						'label' => esc_html__( 'Background Color', 'oom-elementor-form' ),
						'type' => Controls_Manager::COLOR,
						'default' => '#ffffff',
						'selectors' => [
							'{{WRAPPER}} .elementor-field-group:not(.elementor-field-type-upload) .elementor-field:not(.elementor-select-wrapper)' => 'background-color: {{VALUE}};',
							'{{WRAPPER}} .elementor-field-group .elementor-select-wrapper select' => 'background-color: {{VALUE}};',
						],
						'separator' => 'before',
					]
				);

				$this->add_control(
					'field_border_color',
					[
						'label' => esc_html__( 'Border Color', 'oom-elementor-form' ),
						'type' => Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .elementor-field-group:not(.elementor-field-type-upload) .elementor-field:not(.elementor-select-wrapper)' => 'border-color: {{VALUE}};',
							'{{WRAPPER}} .elementor-field-group .elementor-select-wrapper select' => 'border-color: {{VALUE}};',
							'{{WRAPPER}} .elementor-field-group .elementor-select-wrapper::before' => 'color: {{VALUE}};',
						],
						'separator' => 'before',
					]
				);

				$this->add_control(
					'field_border_width',
					[
						'label' => esc_html__( 'Border Width', 'oom-elementor-form' ),
						'type' => Controls_Manager::DIMENSIONS,
						'placeholder' => '1',
						'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
						'selectors' => [
							'{{WRAPPER}} .elementor-field-group:not(.elementor-field-type-upload) .elementor-field:not(.elementor-select-wrapper)' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							'{{WRAPPER}} .elementor-field-group .elementor-select-wrapper select' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'field_border_radius',
					[
						'label' => esc_html__( 'Border Radius', 'oom-elementor-form' ),
						'type' => Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' => [
							'{{WRAPPER}} .elementor-field-group:not(.elementor-field-type-upload) .elementor-field:not(.elementor-select-wrapper)' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							'{{WRAPPER}} .elementor-field-group .elementor-select-wrapper select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->end_controls_section();

				$this->start_controls_section(
					'section_button_style',
					[
						'label' => esc_html__( 'Buttons', 'oom-elementor-form' ),
						'tab' => Controls_Manager::TAB_STYLE,
					]
				);

				$this->add_responsive_control(
					'button_align',
					[
						'label' => esc_html__( 'Position', 'oom-elementor-form' ),
						'type' => Controls_Manager::CHOOSE,
						'options' => [
							'start' => [
								'title' => esc_html__( 'Left', 'oom-elementor-form' ),
								'icon' => 'eicon-h-align-left',
							],
							'center' => [
								'title' => esc_html__( 'Center', 'oom-elementor-form' ),
								'icon' => 'eicon-h-align-center',
							],
							'end' => [
								'title' => esc_html__( 'Right', 'oom-elementor-form' ),
								'icon' => 'eicon-h-align-right',
							],
							'stretch' => [
								'title' => esc_html__( 'Stretch', 'oom-elementor-form' ),
								'icon' => 'eicon-h-align-stretch',
							],
						],
						'default' => 'stretch',
						'prefix_class' => 'elementor%s-button-align-',
					]
				);

				$this->add_responsive_control(
					'button_content_align',
					[
						'label' => esc_html__( 'Alignment', 'oom-elementor-form' ),
						'type' => Controls_Manager::CHOOSE,
						'options' => [
							'start'    => [
								'title' => esc_html__( 'Start', 'oom-elementor-form' ),
								'icon' => "eicon-text-align-{$start}",
							],
							'center' => [
								'title' => esc_html__( 'Center', 'oom-elementor-form' ),
								'icon' => 'eicon-text-align-center',
							],
							'end' => [
								'title' => esc_html__( 'End', 'oom-elementor-form' ),
								'icon' => "eicon-text-align-{$end}",
							],
							'space-between' => [
								'title' => esc_html__( 'Space between', 'oom-elementor-form' ),
								'icon' => 'eicon-text-align-justify',
							],
						],
						'default' => '',
						'selectors' => [
							'{{WRAPPER}} .elementor-button span' => 'justify-content: {{VALUE}};',
						],
						'condition' => [ 'button_align' => 'stretch' ],
					]
				);

				$this->add_group_control(
					Group_Control_Typography::get_type(),
					[
						'name' => 'button_typography',
						'global' => [
							'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_ACCENT,
						],
						'selector' => '{{WRAPPER}} .elementor-button',
					]
				);

				$this->add_group_control(
					Group_Control_Border::get_type(), [
						'name' => 'button_border',
						'selector' => '{{WRAPPER}} .elementor-button',
						'exclude' => [
							'color',
						],
					]
				);

				$this->start_controls_tabs( 'tabs_button_style' );

				$this->start_controls_tab(
					'tab_button_normal',
					[
						'label' => esc_html__( 'Normal', 'oom-elementor-form' ),
					]
				);

				$this->add_control(
					'heading_next_submit_button',
					[
						'label' => esc_html__( 'Next & Submit Button', 'oom-elementor-form' ),
						'type' => Controls_Manager::HEADING,
					]
				);

				$this->add_control(
					'button_background_color',
					[
						'label' => esc_html__( 'Background Color', 'oom-elementor-form' ),
						'type' => Controls_Manager::COLOR,
						'global' => [
							'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_ACCENT,
						],
						'selectors' => [
							'{{WRAPPER}} .e-form__buttons__wrapper__button-next' => 'background-color: {{VALUE}};',
							'{{WRAPPER}} .elementor-button[type="submit"]' => 'background-color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'button_text_color',
					[
						'label' => esc_html__( 'Text Color', 'oom-elementor-form' ),
						'type' => Controls_Manager::COLOR,
						'default' => '#ffffff',
						'selectors' => [
							'{{WRAPPER}} .e-form__buttons__wrapper__button-next' => 'color: {{VALUE}};',
							'{{WRAPPER}} .elementor-button[type="submit"]' => 'color: {{VALUE}};',
							'{{WRAPPER}} .elementor-button[type="submit"] svg *' => 'fill: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'button_border_color',
					[
						'label' => esc_html__( 'Border Color', 'oom-elementor-form' ),
						'type' => Controls_Manager::COLOR,
						'default' => '',
						'selectors' => [
							'{{WRAPPER}} .e-form__buttons__wrapper__button-next' => 'border-color: {{VALUE}};',
							'{{WRAPPER}} .elementor-button[type="submit"]' => 'border-color: {{VALUE}};',
						],
						'condition' => [
							'button_border_border!' => '',
						],
					]
				);

				$this->add_control(
					'heading_previous_button',
					[
						'label' => esc_html__( 'Previous Button', 'oom-elementor-form' ),
						'type' => Controls_Manager::HEADING,
					]
				);

				$this->add_control(
					'previous_button_background_color',
					[
						'label' => esc_html__( 'Background Color', 'oom-elementor-form' ),
						'type' => Controls_Manager::COLOR,
						'global' => [
							'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_ACCENT,
						],
						'selectors' => [
							'{{WRAPPER}} .e-form__buttons__wrapper__button-previous' => 'background-color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'previous_button_text_color',
					[
						'label' => esc_html__( 'Text Color', 'oom-elementor-form' ),
						'type' => Controls_Manager::COLOR,
						'default' => '#ffffff',
						'selectors' => [
							'{{WRAPPER}} .e-form__buttons__wrapper__button-previous' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'previous_button_border_color',
					[
						'label' => esc_html__( 'Border Color', 'oom-elementor-form' ),
						'type' => Controls_Manager::COLOR,
						'default' => '',
						'selectors' => [
							'{{WRAPPER}} .e-form__buttons__wrapper__button-previous' => 'border-color: {{VALUE}};',
						],
						'condition' => [
							'button_border_border!' => '',
						],
					]
				);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'tab_button_hover',
					[
						'label' => esc_html__( 'Hover', 'oom-elementor-form' ),
					]
				);

				$this->add_control(
					'heading_next_submit_button_hover',
					[
						'label' => esc_html__( 'Next & Submit Button', 'oom-elementor-form' ),
						'type' => Controls_Manager::HEADING,
					]
				);

				$this->add_control(
					'button_background_hover_color',
					[
						'label' => esc_html__( 'Background Color', 'oom-elementor-form' ),
						'type' => Controls_Manager::COLOR,
						'default' => '',
						'selectors' => [
							'{{WRAPPER}} .e-form__buttons__wrapper__button-next:hover' => 'background-color: {{VALUE}};',
							'{{WRAPPER}} .elementor-button[type="submit"]:hover' => 'background-color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'button_hover_color',
					[
						'label' => esc_html__( 'Text Color', 'oom-elementor-form' ),
						'type' => Controls_Manager::COLOR,
						'default' => '#ffffff',
						'selectors' => [
							'{{WRAPPER}} .e-form__buttons__wrapper__button-next:hover' => 'color: {{VALUE}};',
							'{{WRAPPER}} .elementor-button[type="submit"]:hover' => 'color: {{VALUE}};',
							'{{WRAPPER}} .elementor-button[type="submit"]:hover svg *' => 'fill: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'button_hover_border_color',
					[
						'label' => esc_html__( 'Border Color', 'oom-elementor-form' ),
						'type' => Controls_Manager::COLOR,
						'default' => '',
						'selectors' => [
							'{{WRAPPER}} .e-form__buttons__wrapper__button-next:hover' => 'border-color: {{VALUE}};',
							'{{WRAPPER}} .elementor-button[type="submit"]:hover' => 'border-color: {{VALUE}};',
						],
						'condition' => [
							'button_border_border!' => '',
						],
					]
				);

				$this->add_control(
					'heading_previous_button_hover',
					[
						'label' => esc_html__( 'Previous Button', 'oom-elementor-form' ),
						'type' => Controls_Manager::HEADING,
					]
				);

				$this->add_control(
					'previous_button_background_color_hover',
					[
						'label' => esc_html__( 'Background Color', 'oom-elementor-form' ),
						'type' => Controls_Manager::COLOR,
						'default' => '',
						'selectors' => [
							'{{WRAPPER}} .e-form__buttons__wrapper__button-previous:hover' => 'background-color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'previous_button_text_color_hover',
					[
						'label' => esc_html__( 'Text Color', 'oom-elementor-form' ),
						'type' => Controls_Manager::COLOR,
						'default' => '#ffffff',
						'selectors' => [
							'{{WRAPPER}} .e-form__buttons__wrapper__button-previous:hover' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'previous_button_border_color_hover',
					[
						'label' => esc_html__( 'Border Color', 'oom-elementor-form' ),
						'type' => Controls_Manager::COLOR,
						'default' => '',
						'selectors' => [
							'{{WRAPPER}} .e-form__buttons__wrapper__button-previous:hover' => 'border-color: {{VALUE}};',
						],
						'condition' => [
							'button_border_border!' => '',
						],
					]
				);

				$this->add_control(
					'hover_transition_duration',
					[
						'label' => esc_html__( 'Transition Duration', 'oom-elementor-form' ),
						'type' => Controls_Manager::SLIDER,
						'size_units' => [ 's', 'ms', 'custom' ],
						'default' => [
							'unit' => 'ms',
						],
						'selectors' => [
							'{{WRAPPER}} .e-form__buttons__wrapper__button-previous' => 'transition-duration: {{SIZE}}{{UNIT}};',
							'{{WRAPPER}} .e-form__buttons__wrapper__button-next' => 'transition-duration: {{SIZE}}{{UNIT}};',
							'{{WRAPPER}} .elementor-button[type="submit"] svg *' => 'transition-duration: {{SIZE}}{{UNIT}};',
							'{{WRAPPER}} .elementor-button[type="submit"]' => 'transition-duration: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'button_hover_animation',
					[
						'label' => esc_html__( 'Animation', 'oom-elementor-form' ),
						'type' => Controls_Manager::HOVER_ANIMATION,
					]
				);

				$this->end_controls_tab();

				$this->end_controls_tabs();

				$this->add_control(
					'button_border_radius',
					[
						'label' => esc_html__( 'Border Radius', 'oom-elementor-form' ),
						'type' => Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' => [
							'{{WRAPPER}} .elementor-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
						'separator' => 'before',
					]
				);

				$this->add_control(
					'button_text_padding',
					[
						'label' => esc_html__( 'Text Padding', 'oom-elementor-form' ),
						'type' => Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' => [
							'{{WRAPPER}} .elementor-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->end_controls_section();

			}

			/**
			 * Render widget output on the frontend.
			 *
			 * Written in PHP and used to generate the final HTML.
			 *
			 * @since 1.0.0
			 * @access protected
			 */
			protected function render() {

				$settings = $this->get_settings_for_display();
				$nonce = wp_create_nonce( 'oom_form_submit_action' );

				$this->add_render_attribute(
					[
						'wrapper' => [
							'class' => [
								'elementor-form-fields-wrapper',
								'elementor-labels-' . $settings['label_position'],
							],
						],
						'submit-group' => [
							'class' => [
								'elementor-field-group',
								'elementor-column',
								'elementor-field-type-submit',
							],
						],
						'button' => [
							'class' => 'elementor-button oom_form_submit_button',
							'type' => 'submit',
						],
						'button-content-wrapper' => [
							'class' => 'elementor-button-content-wrapper',
						],
						'button-icon' => [
							'class' => 'elementor-button-icon',
						],
						'button-text' => [
							'class' => 'elementor-button-text',
						],
					]
				);

				if ( empty( $settings['button_width'] ) ) {
					$settings['button_width'] = '100';
				}

				$this->add_render_attribute( 'submit-group', 'class', 'elementor-col-' . $settings['button_width'] . ' e-form__buttons' );

				if ( ! empty( $settings['button_width_tablet'] ) ) {
					$this->add_render_attribute( 'submit-group', 'class', 'elementor-md-' . $settings['button_width_tablet'] );
				}

				if ( ! empty( $settings['button_width_mobile'] ) ) {
					$this->add_render_attribute( 'submit-group', 'class', 'elementor-sm-' . $settings['button_width_mobile'] );
				}

				if ( ! empty( $settings['button_size'] ) ) {
					$this->add_render_attribute( 'button', 'class', 'elementor-size-' . $settings['button_size'] );
				}

				if ( ! empty( $settings['button_type'] ) ) {
					$this->add_render_attribute( 'button', 'class', 'elementor-button-' . $settings['button_type'] );
				}

				if ( ! empty( $settings['form_id'] ) ) {
					$this->add_render_attribute( 'form', 'id', $settings['form_id'] );
				}

				if ( ! empty( $settings['form_name'] ) ) {
					$this->add_render_attribute( 'form', 'name', $settings['form_name'] );
				}

				if ( ! empty( $settings['button_css_id'] ) ) {
					$this->add_render_attribute( 'button', 'id', $settings['button_css_id'] );
				}

				echo '<div class="oom-form-widget">';
							// Start the form markup
					echo '<form id="oom-form-' . esc_attr( $this->get_id() ) . '" class="oom-custom-form elementor-form" method="post" enctype="multipart/form-data">';
						echo '<div class="elementor-form-fields-wrapper">';
						echo '<input type="hidden" name="form_id" value="'. esc_attr( $this->get_id() ) .'"/>';
						echo '<input type="hidden" name="post_id" value="'. $this->get_current_post_id()  .'">';
						echo '<input type="hidden" name="page_id" value="'. esc_attr(get_the_ID()) .'"/>';
						echo '<input type="hidden" name="form_name" value="'.  $settings['form_name'] .'"/>';
						if (!empty($settings['form_fields'])) {
							foreach ($settings['form_fields'] as $index => $field) {
								// Generate a unique ID for each form field
								$field_id = 'field_' . esc_attr($field['custom_id']);

								// Determine the field type and output the appropriate input
								switch ($field['field_type']) {
									case 'text':
									case 'email':
									case 'url':
									case 'number':
									case 'password':
									case 'date':
									case 'time':
										echo '<div class="form-field elementor-field-group elementor-column elementor-col-' . esc_attr($field['width']). ($settings['mark_required'] === 'true' ? ' elementor-field-required elementor-mark-required' : '') . '">';
										if (!empty($settings['show_labels']) && $settings['show_labels'] === 'true') {
											echo '<label class="elementor-field-label" for="' . esc_attr($field_id) . '">' . esc_html($field['field_label']) . '</label>';
										}
										echo '<input type="' . esc_attr($field['field_type']) . '" id="' . esc_attr($field_id) . '" name="' . esc_attr($field_id) . '" placeholder="' . esc_attr($field['placeholder']) . '" class="elementor-field elementor-field-textual elementor-size-' . esc_attr($settings['input_size']) . '" ' . ($field['required'] === 'true' ? 'required' : '') . '>';
										echo '</div>';
										break;

									case 'textarea':
										echo '<div class="form-field elementor-field-group elementor-column elementor-col-' . esc_attr($field['width']) . ($settings['mark_required'] === 'true' ? ' elementor-field-required elementor-mark-required' : '') . '">';
										if (!empty($settings['show_labels']) && $settings['show_labels'] === 'true') {
											echo '<label class="elementor-field-label" for="' . esc_attr($field_id) . '">' . esc_html($field['field_label']) . '</label>';
										}
										echo '<textarea id="' . esc_attr($field_id) . '" name="' . esc_attr($field_id) . '" placeholder="' . esc_attr($field['placeholder']) . '" rows="' . esc_attr($field['rows']) . '" class="elementor-field elementor-field-textual elementor-size-' . esc_attr($settings['input_size']) . '" ' . ($field['required'] === 'true' ? 'required' : '') . '></textarea>';
										echo '</div>';
										break;

									case 'select':
										echo '<div class="form-field elementor-field-group elementor-column elementor-col-' . esc_attr($field['width']) . ($settings['mark_required'] === 'true' ? ' elementor-field-required elementor-mark-required' : '') . '">';
										if (!empty($settings['show_labels']) && $settings['show_labels'] === 'true') {
											echo '<label class="elementor-field-label" for="' . esc_attr($field_id) . '">' . esc_html($field['field_label']) . '</label>';
										}
										echo '<select id="' . esc_attr($field_id) . '" name="' . esc_attr($field_id) . '" class="elementor-field elementor-field-textual elementor-size-' . esc_attr($settings['input_size']) . '" ' . ($field['allow_multiple'] === 'true' ? 'multiple' : '') . '>';
										$options = explode("\n", $field['field_options']);
										foreach ($options as $option) {
											$option_value = esc_attr( $option );
											$option_label = esc_html( $option );
											if ( false !== strpos( $option, '|' ) ) {
												list( $label, $value ) = explode( '|', $option );
												$option_value = esc_attr( $value );
												$option_label = esc_html( $label );
											}
											echo '<option value="' . esc_attr(trim($option_value)) . '">' . esc_html(trim($option_label)) . '</option>';
										}
										echo '</select>';
										echo '</div>';
										break;

									case 'radio':
										echo '<div class="form-field elementor-field-group elementor-column elementor-col-' . esc_attr($field['width']) . ($settings['mark_required'] === 'true' ? ' elementor-field-required elementor-mark-required' : '') . '">';
										if (!empty($settings['show_labels']) && $settings['show_labels'] === 'true') {
											echo '<label class="elementor-field-label">' . esc_html($field['field_label']) . '</label>';
										}
										echo '<div class="elementor-field-subgroup ' . $field['inline_list'] . ' " style="flex-basis: 100%; max-width: 100%;">';
											$options = explode("\n", $field['field_options']);
											foreach ($options as $option) {
												$option_value = esc_attr( $option );
												$option_label = esc_html( $option );
												if ( false !== strpos( $option, '|' ) ) {
													list( $label, $value ) = explode( '|', $option );
													$option_value = esc_attr( $value );
													$option_label = esc_html( $label );
												}
												echo '<span class="elementor-field-option">';
													echo '<label class="elementor-field-label" style="display: flex; gap: 5px;"><input type="radio" name="' . esc_attr($field_id) . '" value="' . esc_attr(trim($option_value)) . '">' . esc_html(trim($option_label)) . '</label>';
												echo '</span>';
											}
										echo '</div>';
										echo '</div>';
										break;

									case 'checkbox':
										echo '<div class="form-field elementor-field-group elementor-column elementor-col-' . esc_attr($field['width']) . ($settings['mark_required'] === 'true' ? ' elementor-field-required elementor-mark-required' : '') . '">';
										if (!empty($settings['show_labels']) && $settings['show_labels'] === 'true') {
											echo '<label class="elementor-field-label">' . esc_html($field['field_label']) . '</label>';
										}
										echo '<div class="elementor-field-subgroup ' . $field['inline_list'] . ' " style="flex-basis: 100%; max-width: 100%;">';
										$options = explode("\n", $field['field_options']);
											foreach ($options as $option) {
												$option_value = esc_attr( $option );
												$option_label = esc_html( $option );
												if ( false !== strpos( $option, '|' ) ) {
													list( $label, $value ) = explode( '|', $option );
													$option_value = esc_attr( $value );
													$option_label = esc_html( $label );
												}
												echo '<span class="elementor-field-option">';
													echo '<label class="elementor-field-label" style="display: flex; gap: 5px;"><input type="checkbox" name="' . esc_attr($field_id) . '[]" value="' . esc_attr(trim($option_value)) . '">' . esc_html(trim($option_label)) . '</label>';
												echo '</span>';
											}
										echo '</div>';
										echo '</div>';
										break;

									case 'html':
										echo '<div class="form-field elementor-field-group elementor-column elementor-col-' . esc_attr($field['width']) . ($settings['mark_required'] === 'true' ? ' elementor-field-required elementor-mark-required' : '') . '">' . $field['field_html'] . '</div>';
										break;

									case 'hidden':
										echo '<input type="hidden" id="' . esc_attr($field_id) . '" name="' . esc_attr($field_id) . '" value="' . esc_attr($field['field_value']) . '">';
										break;
										
									case 'acceptance':
										echo '<div class="form-field elementor-field-group elementor-column elementor-col-' . esc_attr($field['width']) . ($settings['mark_required'] === 'true' ? ' elementor-field-required elementor-mark-required' : '') . '">';
											echo '<label class="elementor-field-label"><input type="checkbox" id="' . esc_attr($field_id) . '" name="' . esc_attr($field_id) . '" value="yes" ' . ($field['required'] === 'true' ? 'required' : '') . ' style="margin-right: 5px;">' . $field['field_html'] . '</label>';
										echo '</div>';
										break;
									
									case 'tel':
										echo '<div class="form-field elementor-field-group elementor-column elementor-col-' . esc_attr($field['width']). ($settings['mark_required'] === 'true' ? ' elementor-field-required elementor-mark-required' : '') . '">';
										if (!empty($settings['show_labels']) && $settings['show_labels'] === 'true') {
											echo '<label class="elementor-field-label" for="' . esc_attr($field_id) . '">' . esc_html($field['field_label']) . '</label>';
										}
										echo '<input type="tel" id="' . esc_attr($field_id) . '" name="' . esc_attr($field_id) . '" placeholder="' . esc_attr($field['placeholder']) . '" class="oom-phone elementor-field elementor-field-textual elementor-size-' . esc_attr($settings['input_size']) . '" ' . ($field['required'] === 'true' ? 'required' : '') . '>';
										echo '<span class="output-msg"></span>';
										echo '</div>';
										break;


									default:
										// Handle other field types or custom implementations
										break;
								}
							}
						}
						?>

						<div style="display:none;">
							<input type="text" id="oom_form" name="oom_form">
						</div>

						<div <?php $this->print_render_attribute_string( 'submit-group' ); ?>>
							<button <?php $this->print_render_attribute_string( 'button' ); ?>>
								<span <?php $this->print_render_attribute_string( 'button-content-wrapper' ); ?>>
									<?php if ( ! empty( $settings['selected_button_icon'] ) ) : ?>
										<span <?php $this->print_render_attribute_string( 'button-icon' ); ?>>
											<div class="icon-wrapper">
												<?php \Elementor\Icons_Manager::render_icon( $settings['selected_button_icon'], [ 'aria-hidden' => 'true' ] ); ?>
											</div>
											<?php if ( empty( $settings['button_text'] ) ) : ?>
												<span class="elementor-screen-only"><?php echo esc_html__( 'Submit', 'oom-elementor-form' ); ?></span>
											<?php endif; ?>
										</span>
									<?php endif; ?>
									<?php if ( ! empty( $settings['button_text'] ) ) : ?>
										<span <?php $this->print_render_attribute_string( 'button-text' ); ?>><?php $this->print_unescaped_setting( 'button_text' ); ?></span>
									<?php endif; ?>
								</span>
							</button>
						</div>
						<div class="loading-spinner"></div>
						<div class="form-message"></div>
					<!-- Add-ons validation error message -->
					<p class="error-addons" style="display: none; color: #dc3545; margin-top: 10px; font-size: 14px; width: 100%; text-align: center; border-radius: 4px; padding: 10px 15px;"></p>
						</div>
					</form>

					<style>
						.field-error {
							border-color: #dc3545 !important;
							box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
						}
					</style>
					<script>
						jQuery(document).ready(function ($) {
							const formId = '<?php echo esc_attr($this->get_id()); ?>';
							const formSelector = `#oom-form-${formId}`;
								const phoneInputSelector = `${formSelector} input[type='tel']`;
							const messageSelector = `${formSelector} .form-message`;
							const spinnerSelector = `${formSelector} .loading-spinner`;

								const phoneInputs = document.querySelectorAll(phoneInputSelector);
								const phoneFields = [];

								// Initialize intl-tel-input and formatting for all phone inputs in this form
								if (phoneInputs && phoneInputs.length) {
									phoneInputs.forEach((phoneInput) => {
										const outputMsg = phoneInput.closest('.form-field')?.querySelector('.output-msg');
										const iti = window.intlTelInput(phoneInput, {
											nationalMode: true,
											initialCountry: "auto",
											geoIpLookup: function (callback) {
												fetch("https://ipapi.co/json")
													.then((res) => res.json())
													.then((data) => callback(data.country_code))
													.catch(() => callback("SG")); // Default fallback to Singapore
											},
											utilsScript: "../js/utils.js", // Or CDN version
										});

										const updateValidationIcon = () => {
											let iconHtml = '';
											if (phoneInput.value) {
												iconHtml = iti.isValidNumber()
													? '<span style="color:green;">&#10003;</span>'
													: '<span style="color:red;">&#10007;</span>';
											} else {
												iconHtml = '<span style="color:red;">&#10007;</span>';
											}
											if (outputMsg) outputMsg.innerHTML = iconHtml;
										};

										// Generic xxxx xxxx formatting for all phone fields
										const formatXXXX = (raw) => {
											const digitsOnly = (raw || '').replace(/\D/g, '').slice(0, 8);
											if (digitsOnly.length <= 4) return digitsOnly;
											return digitsOnly.slice(0, 4) + ' ' + digitsOnly.slice(4);
										};

										const applyFormatting = () => {
											const prevCursor = phoneInput.selectionStart;
											const prevDigitsBeforeCursor = phoneInput.value.slice(0, prevCursor).replace(/\D/g, '').length;
											const formatted = formatXXXX(phoneInput.value);
											phoneInput.value = formatted;

											// Restore cursor position to same digit index
											let newCursor = 0;
											let digitsSeen = 0;
											while (newCursor < phoneInput.value.length && digitsSeen < prevDigitsBeforeCursor) {
												if (/\d/.test(phoneInput.value.charAt(newCursor))) {
													digitsSeen++;
												}
												newCursor++;
											}
											phoneInput.setSelectionRange(newCursor, newCursor);
											updateValidationIcon();
										};

										phoneInput.addEventListener('input', applyFormatting);
										phoneInput.addEventListener('paste', function () { setTimeout(applyFormatting, 0); });
										phoneInput.addEventListener('change', updateValidationIcon);
										phoneInput.addEventListener('keyup', updateValidationIcon);

										phoneFields.push({ input: phoneInput, iti, updateValidationIcon });
									});
								}

							// Function to clear vehicle category field error
							function clearVehicleCategoryError() {
								const vehicleCategoryField = $(formSelector).find('#field_c_vehicle_category');
								vehicleCategoryField.removeClass('field-error');
								// Clear any error messages related to vehicle category
								const currentMessage = $(messageSelector).html();
								if (currentMessage.includes('Please select at least one vehicle category')) {
									$(messageSelector).html('');
								}
							}
							
							// Make function globally accessible
							window.clearVehicleCategoryError = clearVehicleCategoryError;

							// Monitor vehicle category field for changes
							$(formSelector).find('#field_c_vehicle_category').on('input change', function() {
								if ($(this).val().trim()) {
									clearVehicleCategoryError();
								}
							});

							// Form submission handling
							$(formSelector).on('submit', function (e) {
								e.preventDefault();

								// Validate vehicle category field (required readonly field)
								const vehicleCategoryField = $(formSelector).find('#field_c_vehicle_category');
								if (vehicleCategoryField.length && vehicleCategoryField.prop('required') && !vehicleCategoryField.val().trim()) {
									$(messageSelector).html('<p style="color: #dc3545; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; margin: 10px 0;">Please select at least one vehicle category before submitting.</p>');
									// Add visual highlight to the field
									vehicleCategoryField.addClass('field-error');
									vehicleCategoryField.focus();
									// Scroll to the field
									$('html, body').animate({
										scrollTop: vehicleCategoryField.offset().top - 100
									}, 500);
									return false;
								}

								// Check if we're on a page with add-ons validation
								if (typeof window.validateOomAddOns === 'function') {
									console.log('Add-ons validation function found, running validation...');
									if (!window.validateOomAddOns()) {
										console.log('Add-ons validation failed, preventing form submission');
										// Add-ons validation failed, don't proceed
										return false;
									}
									console.log('Add-ons validation passed');
								} else {
									console.log('Add-ons validation function not found');
								}

								// Validate postal code fields
								const postalCodeFields = $(formSelector).find('input[name*="postcode"], input[name*="post_code"]');
								for (let i = 0; i < postalCodeFields.length; i++) {
									const field = postalCodeFields[i];
									const value = field.value.trim();
									if (value && !/^\d{6}$/.test(value)) {
										$(messageSelector).html('<p>Postal code must be exactly 6 digits.</p>');
										field.focus();
										return;
									}
								}

								// Validate all phone fields
								for (let i = 0; i < phoneFields.length; i++) {
									const { input, iti } = phoneFields[i];
									if (!iti.isValidNumber()) {
										$(messageSelector).html('<p>Invalid phone number. Please enter a valid phone number.</p>');
										input.focus();
										return;
									}
								}

								$(spinnerSelector).show();

								const formData = $(this).serialize();

								$.ajax({
									url: '<?php echo admin_url('admin-ajax.php'); ?>',
									type: 'POST',
									data: {
										action: 'dynamic_form_submit',
										nonce: '<?php echo esc_js($nonce); ?>',
										form_data: formData,
									},
									success: function (response) {
										console.log(response);
										$(messageSelector).html(`<p>${response.data.message}</p>`);
										$(formSelector)[0].reset();
										if (response.data.redirect_url) {
											window.location.href = response.data.redirect_url;
										}
									},
									error: function (response) {
										console.error(response);
										$(messageSelector).html('<p>An error occurred. Please try again.</p>');
									},
									complete: function () {
										$(spinnerSelector).hide();
									},
								});
							});
						});
					</script>



			<?php
				echo '</div>';
				
			}

		}
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new OOm_Form_Widget() );
	}
}
