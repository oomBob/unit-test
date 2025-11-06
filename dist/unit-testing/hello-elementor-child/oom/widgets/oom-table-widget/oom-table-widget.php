<?php
/**
 * OOm Table Widgets
 * Custom Elementor Widgets
 * @version  		1.5.0
 * @widget_version	1.0.0
 * @author 			oom_cn
 */

 if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Typography;
use \Elementor\Plugin;
use \Elementor\Utils;
use \Elementor\Widget_Base;
use \Elementor\Repeater;
use Elementor\Icons_Manager;

// Ensure Elementor is loaded
add_action('elementor/widgets/widgets_registered', 'register_oom_table_widget');

function register_oom_table_widget() {
    // Check if the Elementor plugin is active
    if (class_exists('Elementor\Widget_Base')) {

        class OOm_Table extends Widget_Base {
            public $unique_id = null;
            public function get_name()
            {
                return 'oom-table';
            }
        
            public function get_title()
            {
                return esc_html__('OOm Table', 'oom-custom-elementor-widgets');
            }
        
            public function get_icon()
            {
                return 'eicon-table';
            }
        
            public function get_categories()
            {
                return ['basic'];
            }
        
            public function get_keywords()
            {
                return [
                    'table',
                    'data table',
                ];
            }
        
            protected function register_controls()
            {
        
                /**
                 * Data Table Header
                 */
                $this->start_controls_section(
                    'oom_section_table_header',
                    [
                        'label' => esc_html__('Header', 'oom-custom-elementor-widgets')
                    ]
                );
        
                $repeater = new Repeater();
        
                $repeater->add_control(
                    'oom_table_header_col',
                    [
                        'label' => esc_html__('Column Name', 'oom-custom-elementor-widgets'),
                        'default' => esc_html__('Table Header', 'oom-custom-elementor-widgets'),
                        'type' => Controls_Manager::TEXT,
                        'dynamic'   => ['active' => true],
                        'label_block' => false,
                        'ai' => [
                            'active' => false,
                        ],
                    ]
                );
        
                $repeater->add_control(
                    'oom_table_header_col_span',
                    [
                        'label' => esc_html__('Column Span', 'oom-custom-elementor-widgets'),
                        'default' => '',
                        'type' => Controls_Manager::TEXT,
                        'dynamic'   => ['active' => true],
                        'label_block' => false,
                        'ai' => [
                            'active' => false,
                        ],
                    ]
                );
        
                $repeater->add_control(
                    'oom_table_header_col_icon_enabled',
                    [
                        'label' => esc_html__('Enable Header Icon', 'oom-custom-elementor-widgets'),
                        'type' => Controls_Manager::SWITCHER,
                        'label_on' => __('yes', 'oom-custom-elementor-widgets'),
                        'label_off' => __('no', 'oom-custom-elementor-widgets'),
                        'default' => 'false',
                        'return_value' => 'true',
                    ]
                );
        
                $repeater->add_control(
                    'oom_table_header_icon_type',
                    [
                        'label'    => esc_html__('Header Icon Type', 'oom-custom-elementor-widgets'),
                        'type'    => Controls_Manager::CHOOSE,
                        'options'               => [
                            'none'        => [
                                'title'   => esc_html__('None', 'oom-custom-elementor-widgets'),
                                'icon'    => 'fa fa-ban',
                            ],
                            'icon'        => [
                                'title'   => esc_html__('Icon', 'oom-custom-elementor-widgets'),
                                'icon'    => 'fa fa-star',
                            ],
                            'image'       => [
                                'title'   => esc_html__('Image', 'oom-custom-elementor-widgets'),
                                'icon'    => 'eicon-image-bold',
                            ],
                        ],
                        'default'               => 'icon',
                        'condition' => [
                            'oom_table_header_col_icon_enabled' => 'true'
                        ]
                    ]
                );
        
                // Comment on this control
                $repeater->add_control(
                    'oom_table_header_col_icon_new',
                    [
                        'label' => esc_html__('Icon', 'oom-custom-elementor-widgets'),
                        'type' => Controls_Manager::ICONS,
                        'fa4compatibility' => 'oom_table_header_col_icon',
                        'default' => [
                            'value' => 'fas fa-star',
                            'library' => 'solid',
                        ],
                        'condition' => [
                            'oom_table_header_col_icon_enabled' => 'true',
                            'oom_table_header_icon_type'	=> 'icon'
                        ]
                    ]
                );
        
                $repeater->add_control(
                    'oom_table_header_col_img',
                    [
                        'label' => esc_html__( 'Image', 'oom-custom-elementor-widgets'),
                        'type' => Controls_Manager::MEDIA,
                        'default' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                        'condition' => [
                            'oom_table_header_icon_type'	=> 'image'
                        ],
                        'ai' => [
                            'active' => false,
                        ],
                    ]
                );
        
                $repeater->add_control(
                    'oom_table_header_col_img_size',
                    [
                        'label' => esc_html__( 'Image Size(px)', 'oom-custom-elementor-widgets'),
                        'default' => '25',
                        'type' => Controls_Manager::NUMBER,
                        'label_block' => false,
                        'condition' => [
                            'oom_table_header_icon_type'	=> 'image'
                        ]
                    ]
                );
        
                $repeater->add_control(
                    'oom_table_header_css_class',
                    [
                        'label'			=> esc_html__( 'CSS Class', 'oom-custom-elementor-widgets'),
                        'type'			=> Controls_Manager::TEXT,
                        'dynamic'     => [ 'active' => true ],
                        'label_block' 	=> false,
                        'ai' => [
                            'active' => false,
                        ],
                    ]
                );
        
                $repeater->add_control(
                    'oom_table_header_css_id',
                    [
                        'label'			=> esc_html__( 'CSS ID', 'oom-custom-elementor-widgets'),
                        'type'			=> Controls_Manager::TEXT,
                        'dynamic'     => [ 'active' => true ],
                        'label_block'	=> false,
                        'ai' => [
                            'active' => false,
                        ],
                    ]
                );
        
                  $this->add_control(
                    'oom_table_header_cols_data',
                    [
                        'type' => Controls_Manager::REPEATER,
                        'seperator' => 'before',
                        'default' => [
                            [ 'oom_table_header_col' => 'Table Header' ],
                            [ 'oom_table_header_col' => 'Table Header' ],
                            [ 'oom_table_header_col' => 'Table Header' ],
                            [ 'oom_table_header_col' => 'Table Header' ],
                        ],
                        'fields'      =>  $repeater->get_controls() ,
                        'title_field' => '{{oom_table_header_col}}',
                    ]
                );
        
                  $this->end_controls_section();
        
                  /**
                   * Data Table Content
                   */
                  $this->start_controls_section(
                      'oom_section_table_cotnent',
                      [
                          'label' => esc_html__( 'Content', 'oom-custom-elementor-widgets')
                      ]
                  );
        
                $repeater = new Repeater();
        
                $repeater->add_control(
                    'oom_table_content_row_type',
                    [
                        'label' => esc_html__( 'Row Type', 'oom-custom-elementor-widgets'),
                        'type' => Controls_Manager::SELECT,
                        'default' => 'row',
                        'label_block' => false,
                        'options' => [
                            'row' => esc_html__( 'Row', 'oom-custom-elementor-widgets'),
                            'col' => esc_html__( 'Column', 'oom-custom-elementor-widgets'),
                        ]
                    ]
                );
        
                $repeater->add_control(
                    'oom_table_content_row_colspan',
                    [
                        'label'			=> esc_html__( 'Col Span', 'oom-custom-elementor-widgets'),
                        'type'			=> Controls_Manager::NUMBER,
                        'description'	=> esc_html__( 'Default: 1 (optional).'),
                        'default' 		=> 1,
                        'min'     		=> 1,
                        'label_block'	=> true,
                        'condition' 	=> [
                            'oom_table_content_row_type' => 'col'
                        ]
                    ]
                );
        
                $repeater->add_control(
                    'oom_table_content_type',
                    [
                        'label'		=> esc_html__( 'Content Type', 'oom-custom-elementor-widgets'),
                        'type'	=> Controls_Manager::CHOOSE,
                        'options'               => [
                            'icon' => [
                                'title' => esc_html__( 'Icon', 'oom-custom-elementor-widgets'),
                                'icon' => 'fa fa-info',
                            ],
                            'textarea'        => [
                                'title'   => esc_html__( 'Textarea', 'oom-custom-elementor-widgets'),
                                'icon'    => 'fa fa-text-width',
                            ],
                            'editor'       => [
                                'title'   => esc_html__( 'Editor', 'oom-custom-elementor-widgets'),
                                'icon'    => 'eicon-pencil',
                            ]
                        ],
                        'default'	=> 'textarea',
                        'condition' => [
                            'oom_table_content_row_type' => 'col'
                        ]
                    ]
                );
        
                $repeater->add_control(
                    'oom_table_content_row_rowspan',
                    [
                        'label'			=> esc_html__( 'Row Span', 'oom-custom-elementor-widgets'),
                        'type'			=> Controls_Manager::NUMBER,
                        'description'	=> esc_html__( 'Default: 1 (optional).'),
                        'default' 		=> 1,
                        'min'     		=> 1,
                        'label_block'	=> true,
                        'condition' 	=> [
                            'oom_table_content_row_type' => 'col'
                        ]
                    ]
                );
        
                $repeater->add_control(
                    'oom_table_icon_content_new',
                    [
                        'label' => esc_html__( 'Icon', 'oom-custom-elementor-widgets'),
                        'type' => Controls_Manager::ICONS,
                        'fa4compatibility' => 'oom_table_icon_content',
                        'default' => [
                            'value' => 'fas fa-home',
                            'library' => 'fa-solid',
                        ],
                        'condition' => [
                            'oom_table_content_type' => [ 'icon' ]
                        ]
                    ]
                );
        
                $repeater->add_control(
                    'oom_table_content_row_title',
                    [
                        'label' => esc_html__( 'Cell Text', 'oom-custom-elementor-widgets'),
                        'type' => Controls_Manager::TEXTAREA,
                        'dynamic'   => ['active' => true],
                        'label_block' => true,
                        'default' => esc_html__( 'Content', 'oom-custom-elementor-widgets'),
                        'condition' => [
                            'oom_table_content_row_type' => 'col',
                            'oom_table_content_type' => 'textarea'
                        ]
                    ]
                );
        
                $repeater->add_control(
                    'oom_table_content_row_content',
                    [
                        'label' => esc_html__( 'Cell Text', 'oom-custom-elementor-widgets'),
                        'type' => Controls_Manager::WYSIWYG,
                        'label_block' => true,
                        'default' => esc_html__( 'Content', 'oom-custom-elementor-widgets'),
                        'condition' => [
                            'oom_table_content_row_type' => 'col',
                            'oom_table_content_type' => 'editor'
                        ]
                    ]
                );
        
                $repeater->add_control(
                    'oom_table_content_row_title_link',
                    [
                        'label' => esc_html__( 'Link', 'oom-custom-elementor-widgets'),
                        'type' => Controls_Manager::URL,
                        'dynamic'   => ['active' => true],
                        'label_block' => true,
                        'default' => [
                                'url' => '',
                                'is_external' => '',
                             ],
                             'show_external' => true,
                             'separator' => 'before',
                         'condition' => [
                            'oom_table_content_row_type' => 'col',
                            'oom_table_content_type' => 'textarea'
                        ],
                    ]
                );
        
                $repeater->add_control(
                    'oom_table_content_row_css_class',
                    [
                        'label'			=> esc_html__( 'CSS Class', 'oom-custom-elementor-widgets'),
                        'type'			=> Controls_Manager::TEXT,
                        'dynamic'     => [ 'active' => true ],
                        'label_block'	=> false,
                        'condition' 	=> [
                            'oom_table_content_row_type' => 'col'
                        ],
                        'ai' => [
                            'active' => false,
                        ],
                    ]
                );
        
                $repeater->add_control(
                    'oom_table_content_row_css_id',
                    [
                        'label'			=> esc_html__( 'CSS ID', 'oom-custom-elementor-widgets'),
                        'type'			=> Controls_Manager::TEXT,
                        'dynamic'     => [ 'active' => true ],
                        'label_block'	=> false,
                        'condition' 	=> [
                            'oom_table_content_row_type' => 'col'
                        ],
                        'ai' => [
                            'active' => false,
                        ],
                    ]
                );
        
                  $this->add_control(
                    'oom_table_content_rows',
                    [
                        'type' => Controls_Manager::REPEATER,
                        'seperator' => 'before',
                        'default' => [
                            [ 'oom_table_content_row_type' => 'row' ],
                            [ 'oom_table_content_row_type' => 'col' ],
                            [ 'oom_table_content_row_type' => 'col' ],
                            [ 'oom_table_content_row_type' => 'col' ],
                            [ 'oom_table_content_row_type' => 'col' ],
                        ],
                        'fields' =>  $repeater->get_controls() ,
                        'title_field' => '{{oom_table_content_row_type}}::{{oom_table_content_row_title || oom_table_content_row_content}}',
                    ]
                );
        
                $this->end_controls_section();
        
        
                /**
                 * -------------------------------------------
                 * Tab Style (Data Table Header Style)
                 * -------------------------------------------
                 */
                $this->start_controls_section(
                    'oom_section_table_title_style_settings',
                    [
                        'label' => esc_html__( 'Header Style', 'oom-custom-elementor-widgets'),
                        'tab' => Controls_Manager::TAB_STYLE
                    ]
                );
        
        
                $this->add_control(
                    'oom_section_table_header_radius',
                    [
                        'label' => esc_html__( 'Header Border Radius', 'oom-custom-elementor-widgets'),
                        'type' => Controls_Manager::SLIDER,
                        'range' => [
                            'px' => [
                                'max' => 50,
                            ],
                        ],
                        'selectors' => [
                            '{{WRAPPER}} .oom-table thead tr th:first-child' => 'border-radius: {{SIZE}}px 0px 0px 0px;',
                            '{{WRAPPER}} .oom-table thead tr th:last-child' => 'border-radius: 0px {{SIZE}}px 0px 0px;',
                            '.rtl {{WRAPPER}} .oom-table thead tr th:first-child' => 'border-radius: 0px {{SIZE}}px 0px 0px;',
                            '.rtl {{WRAPPER}} .oom-table thead tr th:last-child' => 'border-radius: {{SIZE}}px 0px 0px 0px;',
                        ],
                    ]
                );
        
                $this->add_responsive_control(
                    'oom_table_each_header_padding',
                    [
                        'label' => esc_html__( 'Padding', 'oom-custom-elementor-widgets'),
                        'type' => Controls_Manager::DIMENSIONS,
                        'size_units' => [ 'px', 'em' ],
                        'selectors' => [
                            '{{WRAPPER}} .oom-table .table-header th' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                            '{{WRAPPER}} .oom-table tbody tr td .th-mobile-screen' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        ],
                    ]
                );
        
                $this->start_controls_tabs('oom_table_header_title_clrbg');
        
                    $this->start_controls_tab( 'oom_table_header_title_normal', [ 'label' => esc_html__( 'Normal', 'oom-custom-elementor-widgets') ] );
        
                        $this->add_control(
                            'oom_table_header_title_color',
                            [
                                'label' => esc_html__( 'Color', 'oom-custom-elementor-widgets'),
                                'type' => Controls_Manager::COLOR,
                                'default' => '#fff',
                                'selectors' => [
                                    '{{WRAPPER}} .oom-table thead tr th' => 'color: {{VALUE}};',
                                    '{{WRAPPER}} table.dataTable thead .sorting:after' => 'color: {{VALUE}};',
                                    '{{WRAPPER}} table.dataTable thead .sorting_asc:after' => 'color: {{VALUE}};',
                                    '{{WRAPPER}} table.dataTable thead .sorting_desc:after' => 'color: {{VALUE}};',
                                ],
                            ]
                        );
        
                        $this->add_control(
                            'oom_table_header_title_bg_color',
                            [
                                'label' => esc_html__( 'Background Color', 'oom-custom-elementor-widgets'),
                                'type' => Controls_Manager::COLOR,
                                'default' => '#4a4893',
                                'selectors' => [
                                    '{{WRAPPER}} .oom-table thead tr th' => 'background-color: {{VALUE}};'
                                ],
                            ]
                        );
        
                        $this->add_group_control(
                            Group_Control_Border::get_type(),
                                [
                                    'name' => 'oom_table_header_border',
                                    'label' => esc_html__( 'Border', 'oom-custom-elementor-widgets'),
                                    'selector' => '{{WRAPPER}} .oom-table thead tr th'
                                ]
                        );
        
                    $this->end_controls_tab();
        
                    $this->start_controls_tab( 'oom_table_header_title_hover', [ 'label' => esc_html__( 'Hover', 'oom-custom-elementor-widgets') ] );
        
                        $this->add_control(
                            'oom_table_header_title_hover_color',
                            [
                                'label' => esc_html__( 'Color', 'oom-custom-elementor-widgets'),
                                'type' => Controls_Manager::COLOR,
                                'default' => '#fff',
                                'selectors' => [
                                    '{{WRAPPER}} .oom-table thead tr th:hover' => 'color: {{VALUE}};',
                                    '{{WRAPPER}} table.dataTable thead .sorting:after:hover' => 'color: {{VALUE}};',
                                    '{{WRAPPER}} table.dataTable thead .sorting_asc:after:hover' => 'color: {{VALUE}};',
                                    '{{WRAPPER}} table.dataTable thead .sorting_desc:after:hover' => 'color: {{VALUE}};',
                                ],
                            ]
                        );
        
                        $this->add_control(
                            'oom_table_header_title_hover_bg_color',
                            [
                                'label' => esc_html__( 'Background Color', 'oom-custom-elementor-widgets'),
                                'type' => Controls_Manager::COLOR,
                                'selectors' => [
                                    '{{WRAPPER}} .oom-table thead tr th:hover' => 'background-color: {{VALUE}};',
                                ],
                            ]
                        );
        
                        $this->add_group_control(
                            Group_Control_Border::get_type(),
                                [
                                    'name' => 'oom_table_header_hover_border',
                                    'label' => esc_html__( 'Border', 'oom-custom-elementor-widgets'),
                                    'selector' => '{{WRAPPER}} .oom-table thead tr th:hover',
                                ]
                        );
        
                    $this->end_controls_tab();
        
                $this->end_controls_tabs();
        
                $this->add_group_control(
                    Group_Control_Typography::get_type(),
                    [
                         'name' => 'oom_table_header_title_typography',
                        'selector' => '{{WRAPPER}} .oom-table thead > tr th .table-header-text',
                    ]
                );
        
                $this->add_responsive_control(
                    'header_icon_size',
                    [
                        'label'      => __('Icon Size', 'oom-custom-elementor-widgets'),
                        'type'       => Controls_Manager::SLIDER,
                        'size_units' => ['px'],
                        'range'      => [
                            'px' => [
                                'min' => 1,
                                'max' => 70,
                            ],
                        ],
                        'default'    => [
                            'size' => 20,
                        ],
                        'selectors'  => [
                            '{{WRAPPER}} .oom-table thead tr th i'                           => 'font-size: {{SIZE}}{{UNIT}};',
                            '{{WRAPPER}} .oom-table thead tr th .table-header-svg-icon' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};',
                        ],
                    ]
                );
        
                $this->add_responsive_control(
                    'header_icon_position_from_top',
                    [
                        'label'      => __('Icon Position', 'oom-custom-elementor-widgets'),
                        'type'       => Controls_Manager::SLIDER,
                        'size_units' => ['px', '%'],
                        'range'      => [
                            'px' => [
                                'min' => 1,
                                'max' => 70,
                            ],
                            '%'  => [
                                'min' => 0,
                                'max' => 100,
                            ],
                        ],
                        'selectors'  => [
                            '{{WRAPPER}} .oom-table thead tr th .header-icon' => 'top: {{SIZE}}{{UNIT}};',
                        ],
                    ]
                );
        
                $this->add_responsive_control(
                    'header_icon_space',
                    [
                        'label'      => __('Icon Space', 'oom-custom-elementor-widgets'),
                        'type'       => Controls_Manager::SLIDER,
                        'size_units' => ['px'],
                        'range'      => [
                            'px' => [
                                'min' => 1,
                                'max' => 70,
                            ],
                        ],
                        'selectors'             => [
                            '{{WRAPPER}} .oom-table thead tr th i, {{WRAPPER}} .oom-table thead tr th img' => 'margin-right: {{SIZE}}{{UNIT}};'
                        ]
                    ]
                );
        
                $this->add_responsive_control(
                    'oom_table_header_title_alignment',
                    [
                        'label' => esc_html__( 'Title Alignment', 'oom-custom-elementor-widgets'),
                        'type' => Controls_Manager::CHOOSE,
                        'label_block' => true,
                        'options' => [
                            'left' => [
                                'title' => esc_html__( 'Left', 'oom-custom-elementor-widgets'),
                                'icon' => 'eicon-text-align-left',
                            ],
                            'center' => [
                                'title' => esc_html__( 'Center', 'oom-custom-elementor-widgets'),
                                'icon' => 'eicon-text-align-center',
                            ],
                            'right' => [
                                'title' => esc_html__( 'Right', 'oom-custom-elementor-widgets'),
                                'icon' => 'eicon-text-align-right',
                            ],
                        ],
                        'default' => 'left',
                        'selectors' => [
                                '{{WRAPPER}} .oom-table thead .table-header' => 'text-align: {{VALUE}};'
                        ],
                    ]
                );
        
                $this->end_controls_section();
        
                /**
                 * -------------------------------------------
                 * Tab Style (Data Table Content Style)
                 * -------------------------------------------
                 */
                $this->start_controls_section(
                    'oom_section_table_content_style_settings',
                    [
                        'label' => esc_html__( 'Content Style', 'oom-custom-elementor-widgets'),
                        'tab' => Controls_Manager::TAB_STYLE
                    ]
                );
        
                $this->start_controls_tabs('oom_table_content_row_cell_styles');
        
                    $this->start_controls_tab('oom_table_odd_cell_style', ['label' => esc_html__( 'Normal', 'oom-custom-elementor-widgets')]);
        
                        $this->add_control(
                            'oom_table_content_odd_style_heading',
                            [
                                'label' => esc_html__( 'ODD Cell', 'oom-custom-elementor-widgets'),
                                'type' => Controls_Manager::HEADING,
                            ]
                        );
        
                        $this->add_control(
                            'oom_table_content_color_odd',
                            [
                                'label' => esc_html__( 'Color ( Odd Row )', 'oom-custom-elementor-widgets'),
                                'type' => Controls_Manager::COLOR,
                                'default' => '#000000',
                                'selectors' => [
                                    '{{WRAPPER}} .oom-table tbody > tr:nth-child(2n) td' => 'color: {{VALUE}};',
                                ],
                            ]
                        );
        
                        $this->add_control(
                            'oom_table_content_bg_odd',
                            [
                                'label' => esc_html__( 'Background ( Odd Row )', 'oom-custom-elementor-widgets'),
                                'type' => Controls_Manager::COLOR,
                                'default' => '#f2f2f2',
                                'selectors' => [
                                    '{{WRAPPER}} .oom-table tbody > tr:nth-child(2n) td' => 'background: {{VALUE}};',
                                ],
                            ]
                        );
        
                        $this->add_control(
                            'oom_table_content_even_style_heading',
                            [
                                'label' => esc_html__( 'Even Cell', 'oom-custom-elementor-widgets'),
                                'type' => Controls_Manager::HEADING,
                                'separator'	=> 'before'
                            ]
                        );
        
                        $this->add_control(
                            'oom_table_content_even_color',
                            [
                                'label' => esc_html__( 'Color ( Even Row )', 'oom-custom-elementor-widgets'),
                                'type' => Controls_Manager::COLOR,
                                'default' => '#000000',
                                'selectors' => [
                                    '{{WRAPPER}} .oom-table tbody > tr:nth-child(2n+1) td' => 'color: {{VALUE}};',
                                ],
                            ]
                        );
        
                        $this->add_control(
                            'oom_table_content_bg_even_color',
                            [
                                'label' => esc_html__( 'Background Color (Even Row)', 'oom-custom-elementor-widgets'),
                                'type' => Controls_Manager::COLOR,
                                'default' => '',
                                'selectors' => [
                                    '{{WRAPPER}} .oom-table tbody > tr:nth-child(2n+1) td' => 'background-color: {{VALUE}};',
                                ],
                            ]
                        );
        
                        $this->add_group_control(
                            Group_Control_Border::get_type(),
                                [
                                    'name' => 'oom_table_cell_border',
                                    'label' => esc_html__( 'Border', 'oom-custom-elementor-widgets'),
                                    'selector' => '{{WRAPPER}} .oom-table tbody tr td',
                                    'separator'	=> 'before'
                                ]
                        );
        
                        $this->add_responsive_control(
                            'oom_table_each_cell_padding',
                            [
                                'label' => esc_html__( 'Padding', 'oom-custom-elementor-widgets'),
                                'type' => Controls_Manager::DIMENSIONS,
                                'size_units' => [ 'px', 'em' ],
                                'selectors' => [
                                         '{{WRAPPER}} .oom-table tbody tr td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                                 ],
                            ]
                        );
        
                    $this->end_controls_tab();
        
                    $this->start_controls_tab('oom_table_odd_cell_hover_style', ['label' => esc_html__( 'Hover', 'oom-custom-elementor-widgets')]);
        
                        $this->add_control(
                            'oom_table_content_hover_color_odd',
                            [
                                'label' => esc_html__( 'Color ( Odd Row )', 'oom-custom-elementor-widgets'),
                                'type' => Controls_Manager::COLOR,
                                'selectors' => [
                                    '{{WRAPPER}} .oom-table tbody > tr:nth-child(2n) td:hover' => 'color: {{VALUE}};',
                                ],
                            ]
                        );
        
                        $this->add_control(
                            'oom_table_content_hover_bg_odd',
                            [
                                'label' => esc_html__( 'Background ( Odd Row )', 'oom-custom-elementor-widgets'),
                                'type' => Controls_Manager::COLOR,
                                'selectors' => [
                                    '{{WRAPPER}} .oom-table tbody > tr:nth-child(2n) td:hover' => 'background: {{VALUE}};',
                                ],
                            ]
                        );
        
                        $this->add_control(
                            'oom_table_content_even_hover_style_heading',
                            [
                                'label' => esc_html__( 'Even Cell', 'oom-custom-elementor-widgets'),
                                'type' => Controls_Manager::HEADING,
                            ]
                        );
        
                        $this->add_control(
                            'oom_table_content_hover_color_even',
                            [
                                'label' => esc_html__( 'Color ( Even Row )', 'oom-custom-elementor-widgets'),
                                'type' => Controls_Manager::COLOR,
                                'default' => '#6d7882',
                                'selectors' => [
                                    '{{WRAPPER}} .oom-table tbody > tr:nth-child(2n+1) td:hover' => 'color: {{VALUE}};',
                                ],
                            ]
                        );
        
                        $this->add_control(
                            'oom_table_content_bg_even_hover_color',
                            [
                                'label' => esc_html__( 'Background Color (Even Row)', 'oom-custom-elementor-widgets'),
                                'type' => Controls_Manager::COLOR,
                                'default' => '',
                                'selectors' => [
                                    '{{WRAPPER}} .oom-table tbody > tr:nth-child(2n+1) td:hover' => 'background-color: {{VALUE}};',
                                ],
                            ]
                        );
        
                    $this->end_controls_tab();
        
                $this->end_controls_tabs();
        
                $this->add_group_control(
                    Group_Control_Typography::get_type(),
                    [
                         'name' => 'oom_table_content_typography',
                        'selector' => '{{WRAPPER}} .oom-table tbody tr td'
                    ]
                );
        
                $this->add_control(
                    'oom_table_content_link_typo',
                    [
                        'label' => esc_html__( 'Link Color', 'oom-custom-elementor-widgets'),
                        'type' => Controls_Manager::HEADING,
                        'separator'	=> 'before'
                    ]
                );
        
                /* Table Content Link */
                $this->start_controls_tabs( 'oom_table_link_tabs' );
        
                    // Normal State Tab
                    $this->start_controls_tab( 'oom_table_link_normal', [ 'label' => esc_html__( 'Normal', 'oom-custom-elementor-widgets') ] );
        
                    $this->add_control(
                        'oom_table_link_normal_text_color',
                        [
                            'label' => esc_html__( 'Text Color', 'oom-custom-elementor-widgets'),
                            'type' => Controls_Manager::COLOR,
                            'default' => '#c15959',
                            'selectors' => [
                                '{{WRAPPER}} .oom-table-wrap table td a' => 'color: {{VALUE}};',
                            ],
                        ]
                    );
        
                    $this->end_controls_tab();
        
                    // Hover State Tab
                    $this->start_controls_tab( 'oom_table_link_hover', [ 'label' => esc_html__( 'Hover', 'oom-custom-elementor-widgets') ] );
        
                    $this->add_control(
                        'oom_table_link_hover_text_color',
                        [
                            'label' => esc_html__( 'Text Color', 'oom-custom-elementor-widgets'),
                            'type' => Controls_Manager::COLOR,
                            'default' => '#6d7882',
                            'selectors' => [
                                '{{WRAPPER}} .oom-table-wrap table td a:hover' => 'color: {{VALUE}};',
                            ],
                        ]
                    );
        
                    $this->end_controls_tab();
        
                $this->end_controls_tabs();
        
                $this->add_responsive_control(
                    'oom_table_content_alignment',
                    [
                        'label' => esc_html__( 'Content Alignment', 'oom-custom-elementor-widgets'),
                        'type' => Controls_Manager::CHOOSE,
                        'label_block' => true,
                        'options' => [
                            'left' => [
                                'title' => esc_html__( 'Left', 'oom-custom-elementor-widgets'),
                                'icon' => 'eicon-text-align-left',
                            ],
                            'center' => [
                                'title' => esc_html__( 'Center', 'oom-custom-elementor-widgets'),
                                'icon' => 'eicon-text-align-center',
                            ],
                            'right' => [
                                'title' => esc_html__( 'Right', 'oom-custom-elementor-widgets'),
                                'icon' => 'eicon-text-align-right',
                            ],
                        ],
                        'default' => 'left',
                        'selectors' => [
                                '{{WRAPPER}} .oom-table tbody .td-content-wrapper' => 'text-align: {{VALUE}};'
                        ],
                    ]
                );
				
				$this->add_responsive_control(
					'oom_table_vertical_alignment', [
						'label' => esc_html__( 'Vertical Alignment', 'oom-custom-elementor-widgets'),
						'type' => Controls_Manager::CHOOSE,
						'label_block' => true,
						'options' => [
							'top' => [
								'title' => esc_html__( 'Top', 'oom-custom-elementor-widgets'),
								'icon' => 'eicon-v-align-top',
							],
							'middle' => [
								'title' => esc_html__( 'Middle', 'oom-custom-elementor-widgets'),
								'icon' => 'eicon-v-align-middle',
							],
							'bottom' => [
								'title' => esc_html__( 'Bottom', 'oom-custom-elementor-widgets'),
								'icon' => 'eicon-v-align-bottom',
							],
						],
						'default' => 'left',
						'selectors' => [
							'{{WRAPPER}} .oom-table tbody td' => 'vertical-align: {{VALUE}};'
						],
					] 
				);
        
                /* Table Content Icon  Style*/
        
                $this->add_control(
                    'oom_table_content_icon_style',
                    [
                        'label' => esc_html__( 'Icon Style', 'oom-custom-elementor-widgets'),
                        'type' => Controls_Manager::HEADING,
                        'separator'	=> 'before'
                    ]
                );
        
                $this->add_responsive_control(
                    'oom_table_content_icon_size',
                    [
                        'label'      => __('Icon Size', 'oom-custom-elementor-widgets'),
                        'type'       => Controls_Manager::SLIDER,
                        'size_units' => ['px'],
                        'range'      => [
                            'px' => [
                                'min' => 1,
                                'max' => 70,
                            ],
                        ],
                        'default'    => [
                            'size' => 20,
                        ],
                        'selectors'  => [
                            '{{WRAPPER}} .oom-table tbody .td-content-wrapper .oom-datatable-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
                            '{{WRAPPER}} .oom-table tbody .td-content-wrapper .oom-datatable-icon svg' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};',
                        ],
                        'separator'	=> 'before'
                    ]
                );
        
                $this->start_controls_tabs( 'oom_table_icon_tabs' );
        
                    // Normal State Tab
                    $this->start_controls_tab( 'oom_table_icon_normal', [ 'label' => esc_html__( 'Normal', 'oom-custom-elementor-widgets') ] );
        
                    $this->add_control(
                        'oom_table_icon_normal_color',
                        [
                            'label' => esc_html__( 'Icon Color', 'oom-custom-elementor-widgets'),
                            'type' => Controls_Manager::COLOR,
                            'default' => '#c15959',
                            'selectors' => [
                                '{{WRAPPER}} .oom-table tbody .td-content-wrapper .oom-datatable-icon i' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .oom-table tbody .td-content-wrapper .oom-datatable-icon svg' => 'fill: {{VALUE}};',
                            ],
                        ]
                    );
        
                    $this->end_controls_tab();
        
                    // Hover State Tab
                    $this->start_controls_tab( 'oom_table_icon_hover', [ 'label' => esc_html__( 'Hover', 'oom-custom-elementor-widgets') ] );
        
                    $this->add_control(
                        'oom_table_link_hover_color',
                        [
                            'label' => esc_html__( 'Icon Color', 'oom-custom-elementor-widgets'),
                            'type' => Controls_Manager::COLOR,
                            'default' => '#6d7882',
                            'selectors' => [
                                '{{WRAPPER}} .oom-table tbody .td-content-wrapper:hover .oom-datatable-icon i' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .oom-table tbody .td-content-wrapper:hover .oom-datatable-icon svg' => 'fill: {{VALUE}};',
                            ],
                        ]
                    );
        
                    $this->end_controls_tab();
        
                $this->end_controls_tabs();
        
                $this->end_controls_section();
        
            }
        
            public function get_style_depends() {
                return [
                    'font-awesome-5-all',
                    'font-awesome-4-shim',
                ];
            }
        
            protected function render( ) {
        
                $settings = $this->get_settings_for_display();
        
                $table_tr = [];
                $table_td = [];
        
                  // Storing Data table content values
                  foreach( $settings['oom_table_content_rows'] as $content_row ) {
                      $row_id = uniqid();
                      if( $content_row['oom_table_content_row_type'] == 'row' ) {
                          $table_tr[] = [
                              'id' => $row_id,
                              'type' => $content_row['oom_table_content_row_type'],
                          ];
        
                      }
                      if( $content_row['oom_table_content_row_type'] == 'col' ) {
        
                        $icon_migrated = isset($settings['__fa4_migrated']['oom_table_icon_content_new']);
                        $icon_is_new = empty($settings['oom_table_icon_content']);
        
                          $target = !empty($content_row['oom_table_content_row_title_link']['is_external']) ? 'target="_blank"' : '';
                          $nofollow = !empty($content_row['oom_table_content_row_title_link']['nofollow']) ? 'rel="nofollow"' : '';
        
                          $table_tr_keys = array_keys( $table_tr );
                          $last_key = end( $table_tr_keys );
                        $tbody_content = ($content_row['oom_table_content_type'] == 'editor') ? $content_row['oom_table_content_row_content'] : $content_row['oom_table_content_row_title'];
        
                          $table_td[] = [
                              'row_id'		=> !empty( $table_tr[$last_key]['id'] ) ? $table_tr[$last_key]['id'] : $row_id,
                              'type'			=> $content_row['oom_table_content_row_type'],
                            'content_type'	=> $content_row['oom_table_content_type'],
                              'title'			=> $tbody_content,
                              'link_url'		=> !empty($content_row['oom_table_content_row_title_link']['url'])?$content_row['oom_table_content_row_title_link']['url']:'',
                              'icon_content_new'	=> !empty($content_row['oom_table_icon_content_new']) ? $content_row['oom_table_icon_content_new']:'',
                              'icon_content'	=> !empty($content_row['oom_table_icon_content']) ? $content_row['oom_table_icon_content']:'',
                              'icon_migrated'	=> $icon_migrated,
                              'icon_is_new'	=> $icon_is_new,
                              'link_target'	=> $target,
                              'nofollow'		=> $nofollow,
                            'colspan'		=> $content_row['oom_table_content_row_colspan'],
                            'rowspan'		=> $content_row['oom_table_content_row_rowspan'],
                            'tr_class'		=> $content_row['oom_table_content_row_css_class'],
                            'tr_id'			=> $content_row['oom_table_content_row_css_id']
                          ];
                      }
                }
                $table_th_count = count($settings['oom_table_header_cols_data']);
                $this->add_render_attribute('oom_table_wrap', [
                    'class'                  => 'oom-table-wrap',
                    'table_id'          => esc_attr($this->get_id()),
                    'id'                     => 'oom-table-wrapper-'.esc_attr($this->get_id()),
                ]);
                
                $this->add_render_attribute('oom_table', [
                    'class'	=> [ 'tablesorter oom-table', esc_attr($settings['table_alignment']) ],
                    'id'	=> 'oom-table-'.esc_attr($this->get_id())
                ]);
        
                $this->add_render_attribute( 'td_content', [
                    'class'	=> 'td-content'
                ]);
        
               	?>
				<style>
					
				</style>
                <div <?php echo $this->get_render_attribute_string('oom_table_wrap'); ?>>
                    <table <?php echo $this->get_render_attribute_string('oom_table'); ?>>
                        <thead>
                            <tr class="table-header">
                                <?php $i = 0; foreach( $settings['oom_table_header_cols_data'] as $header_title ) :
                                    $this->add_render_attribute('th_class'.$i, [
                                        'class'		=> [ $header_title['oom_table_header_css_class'] ],
                                        'id'		=> $header_title['oom_table_header_css_id'],
                                        'colspan'	=> $header_title['oom_table_header_col_span']
                                    ]);
        
                                    $this->add_render_attribute('th_class'.$i, 'class', 'sorting' );
                                ?>
                                <th <?php echo $this->get_render_attribute_string('th_class'.$i); ?>>
                                    <?php if( $header_title['oom_table_header_col_icon_enabled'] == 'true' && $header_title['oom_table_header_icon_type'] == 'icon' ) : ?>
                                        <?php if (empty($header_title['oom_table_header_col_icon']) || isset($header_title['__fa4_migrated']['oom_table_header_col_icon_new'])) { ?>
                                            <?php if( isset($header_title['oom_table_header_col_icon_new']['value']['url']) ) : ?>
                                                <img class="header-icon table-header-svg-icon" src="<?php echo esc_url( $header_title['oom_table_header_col_icon_new']['value']['url'] ); ?>" alt="<?php echo esc_attr(get_post_meta($header_title['oom_table_header_col_icon_new']['value']['id'], '_wp_attachment_image_alt', true)); ?>" />
                                            <?php else : ?>
                                                <i class="<?php echo esc_attr( $header_title['oom_table_header_col_icon_new']['value'] ); ?> header-icon"></i>
                                            <?php endif; ?>
                                        <?php } else { ?>
                                            <i class="<?php echo esc_attr( $header_title['oom_table_header_col_icon'] ); ?> header-icon"></i>
                                        <?php } ?>
                                    <?php endif; ?>
                                    <?php
                                        if( $header_title['oom_table_header_col_icon_enabled'] == 'true' && $header_title['oom_table_header_icon_type'] == 'image' ) :
                                            $this->add_render_attribute('table_th_img'.$i, [
                                                'src'	=> esc_url( $header_title['oom_table_header_col_img']['url'] ),
                                                'class'	=> 'oom-table-th-img',
                                                'style'	=> "width:{$header_title['oom_table_header_col_img_size']}px;",
                                                'alt'	=> esc_attr(get_post_meta($header_title['oom_table_header_col_img']['id'], '_wp_attachment_image_alt', true))
                                            ]);
                                    ?><img <?php echo $this->get_render_attribute_string('table_th_img'.$i); ?>><?php endif; ?><span class="table-header-text"><?php echo __( $header_title['oom_table_header_col'], 'oom-custom-elementor-widgets'); ?></span></th>
                                <?php $i++; endforeach; ?>
                            </tr>
                        </thead>
                          <tbody>
                            <?php for( $i = 0; $i < count( $table_tr ); $i++ ) : ?>
                                <tr>
                                    <?php
                                        for( $j = 0; $j < count( $table_td ); $j++ ) {
                                            if( $table_tr[$i]['id'] == $table_td[$j]['row_id'] ) {
        
                                                $this->add_render_attribute('table_inside_td'.$i.$j,
                                                    [
                                                        'colspan' => $table_td[$j]['colspan'] > 1 ? $table_td[$j]['colspan'] : '',
                                                        'rowspan' => $table_td[$j]['rowspan'] > 1 ? $table_td[$j]['rowspan'] : '',
                                                        'class'		=> $table_td[$j]['tr_class'],
                                                        'id'		=> $table_td[$j]['tr_id']
                                                    ]
                                                );
                                                ?>
                                               <?php if(  $table_td[$j]['content_type'] == 'icon' ) : ?>
                                                    <td <?php echo $this->get_render_attribute_string('table_inside_td'.$i.$j); ?>>
                                                        <div class="td-content-wrapper">
                                                            <?php if ( $table_td[$j]['icon_is_new'] || $table_td[$j]['icon_migrated']) { ?>
                                                                <div class="oom-datatable-icon td-content">
                                                                <?php Icons_Manager::render_icon( $table_td[$j]['icon_content_new'] );?>
                                                                </div>
                                                           <?php } else { ?>
                                                                <div class="td-content">
                                                                    <span class="<?php echo esc_attr( $table_td[ $j ]['icon_content'] ); ?>" aria-hidden="true"></span>
                                                                </div>
                                                            <?php } ?>
                                                        </div>
                                                    </td>
                                                <?php elseif(  $table_td[$j]['content_type'] == 'textarea' && !empty($table_td[$j]['link_url']) ) : ?>
                                                    <td <?php echo $this->get_render_attribute_string('table_inside_td'.$i.$j); ?>>
                                                        <div class="td-content-wrapper">
                                                            <a href="<?php echo esc_url( $table_td[$j]['link_url'] ); ?>" <?php echo $table_td[$j]['link_target'] ?> <?php echo $table_td[$j]['nofollow'] ?>><?php echo wp_kses_post($table_td[$j]['title']); ?></a>
                                                        </div>
                                                    </td>
        
                                                <?php else: ?>
                                                    <td <?php echo $this->get_render_attribute_string('table_inside_td'.$i.$j); ?>>
                                                        <div class="td-content-wrapper"><div <?php echo $this->get_render_attribute_string('td_content'); ?>><?php echo $table_td[$j]['title']; ?></div></div>
                                                    </td>
                                                <?php endif; ?>
                                                <?php
                                            }
                                        }
                                    ?>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
                  <?php
            }
    
        }
		$oom_table_status = get_option('oom_table_status');
        // Register the custom widget
        if ($oom_table_status == 'active'){
			Plugin::instance()->widgets_manager->register_widget_type(new OOm_Table());
		}

    }
}