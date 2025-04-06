<?php
/**
 * Plugin Name: Boulevard Booking Widget
 * Description: Easily install the Boulevard booking overlay on your wordpress website as well as easily install and utilize the link to specific items or categories functionality.
 * Version: 1.0
 * Author: Boulevard
 * Author URI: https://www.joinblvd.com/?utm_source=referral&utm_medium=plugin-directory&utm_campaign=boulevard-booking-plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Boulevard_Booking_Widget {
    private $options;

    public function __construct() {
        // Add menu item
        add_action('admin_menu', array($this, 'add_plugin_page'));
        // Register settings
        add_action('admin_init', array($this, 'page_init'));
        // Add scripts to frontend
        add_action('wp_head', array($this, 'output_header_script'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Add settings link on plugin page
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));
    }

    /**
     * Add options page
     */
    public function add_plugin_page() {
        add_menu_page(
            'Boulevard Booking Settings',
            'Boulevard Booking',
            'manage_options',
            'boulevard-booking',
            array($this, 'create_admin_page'),
            'dashicons-admin-tools',
            30
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page() {
        // Set class property
        $this->options = get_option('boulevard_booking_options');
        ?>
        <div class="wrap">
            <h1>Boulevard Booking Widget Settings</h1>
            <form method="post" action="options.php">
            <?php
                settings_fields('boulevard_booking_option_group');
                do_settings_sections('boulevard-booking-admin');
                submit_button();
            ?>
            </form>
            <div class="how-to-use" style="margin-top: 30px; background: #fff; padding: 20px; border-left: 4px solid #2271b1;">
                <h2>How to Use This Plugin</h2>
                <p>Configure your Boulevard initialization script and booking triggers above, then assign the defined CSS IDs to any element on your site.</p>
                <p><strong>Example:</strong></p>
                <p>If you defined a trigger with ID <code>wrinkles</code>, you can use it like this:</p>
                <pre>&lt;button id="wrinkles"&gt;Book Consultation&lt;/button&gt;</pre>
                <p>Or with a link:</p>
                <pre>&lt;a href="#" id="wrinkles"&gt;Book Now&lt;/a&gt;</pre>
                <p>You can also link directly to the trigger ID:</p>
                <pre>&lt;a href="#wrinkles"&gt;Book Consultation&lt;/a&gt;</pre>
                <p><strong>Note:</strong> The initialization script is optional. If you've already added the Boulevard script to your site, you can skip that section and just add your triggers.</p>
                <p><strong>JavaScript Implementation Format:</strong></p>
                <p>Use the complete JavaScript function call in the implementation field:</p>
                <pre>blvd.openBookingWidget({ urlParams: { locationId: 'your-location-id', path: '/cart/menu/Consultations/your-service-id', visitType: 'SELF_VISIT' }});</pre>
                <p><strong>Working with Page Builders:</strong></p>
                <p>In most page builders, you can add a CSS ID to buttons or other elements in the advanced settings.</p>
                <ul>
                    <li>Elementor: Advanced tab > CSS ID field</li>
                    <li>Beaver Builder: Advanced tab > ID field</li>
                    <li>Divi: Advanced tab > CSS ID field</li>
                    <li>Gutenberg: Block settings > Advanced > HTML anchor</li>
                </ul>
                <p><strong>Troubleshooting:</strong></p>
                <p>If triggers aren't working:</p>
                <ul>
                    <li>Make sure the Boulevard script is properly loaded on your site</li>
                    <li>Check browser console for errors or messages</li>
                    <li>Verify the JavaScript implementation is correct and includes the full function call</li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init() {
        register_setting(
            'boulevard_booking_option_group',
            'boulevard_booking_options',
            array($this, 'sanitize')
        );

        // Add section for initialization script
        add_settings_section(
            'boulevard_init_section',
            'Boulevard Initialization Script',
            array($this, 'print_init_section_info'),
            'boulevard-booking-admin'
        );

        add_settings_field(
            'init_script',
            'Self-Booking Overlay Script',
            array($this, 'init_script_callback'),
            'boulevard-booking-admin',
            'boulevard_init_section'
        );

        // Add section for triggers
        add_settings_section(
            'boulevard_triggers_section',
            'Booking Triggers',
            array($this, 'print_triggers_section_info'),
            'boulevard-booking-admin'
        );

        add_settings_field(
            'booking_triggers',
            'Booking Triggers',
            array($this, 'booking_triggers_callback'),
            'boulevard-booking-admin',
            'boulevard_triggers_section'
        );
    }

    /**
     * Sanitize each setting field as needed
     */
    public function sanitize($input) {
        $new_input = array();
        
        if (isset($input['init_script'])) {
            // Allow script tags by not using wp_kses_post
            $new_input['init_script'] = $input['init_script'];
        }
        
        if (isset($input['booking_triggers'])) {
            $new_input['booking_triggers'] = $this->sanitize_triggers($input['booking_triggers']);
        }
        
        return $new_input;
    }

    /**
     * Sanitize triggers
     */
    private function sanitize_triggers($triggers) {
        $sanitized = array();
        $triggers = is_array($triggers) ? $triggers : array();
        
        foreach ($triggers as $index => $trigger) {
            if (!empty($trigger['id']) && !empty($trigger['code'])) {
                $sanitized[] = array(
                    'id' => sanitize_text_field($trigger['id']),
                    // Allow JavaScript code to pass through without sanitization
                    'code' => $trigger['code']
                );
            }
        }
        
        return $sanitized;
    }

    /**
     * Print the initialization section info
     */
    public function print_init_section_info() {
        print 'Enter your Boulevard Self-Booking overlay initialization script below (optional):';
    }

    /**
     * Print the triggers section info
     */
    public function print_triggers_section_info() {
        print 'Configure your booking triggers below. Each trigger needs an ID and the JavaScript code to execute when clicked.';
    }

    /**
     * Initialization script callback
     */
    public function init_script_callback() {
        $value = isset($this->options['init_script']) ? $this->options['init_script'] : '';
        
        // Add admin script for code editor styling
        wp_enqueue_code_editor(array('type' => 'text/html'));
        wp_enqueue_script('jquery');
        
        ?>
        <div style="position: relative;">
            <div style="position: absolute; top: 5px; right: 5px; z-index: 100;">
                <span class="description" style="background: #f0f0f0; padding: 3px 8px; border-radius: 3px; display: inline-block; font-size: 12px;">HTML/JavaScript</span>
            </div>
            <textarea id="init_script" name="boulevard_booking_options[init_script]" rows="15" style="width: 100%; font-family: monospace; tab-size: 2; background: #f8f8f8; border: 1px solid #ddd; padding: 15px; border-radius: 3px;"><?php echo esc_textarea($value); ?></textarea>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Try to use WP code editor if available
            if (typeof wp !== 'undefined' && wp.codeEditor) {
                wp.codeEditor.initialize($('#init_script'), {
                    codemirror: {
                        mode: 'htmlmixed',
                        lineNumbers: true,
                        indentUnit: 2,
                        tabSize: 2,
                        autoCloseBrackets: true,
                        matchBrackets: true
                    }
                });
            }
        });
        </script>
        
        <p class="description" style="margin-top: 10px;">Paste your <strong>complete Boulevard initialization script</strong> here, including the <code>&lt;script&gt;</code> tags. (Optional - only needed if you haven't already added it to your site)</p>
        <?php
    }

    /**
     * Booking triggers callback
     */
    public function booking_triggers_callback() {
        $triggers = isset($this->options['booking_triggers']) ? $this->options['booking_triggers'] : array(array('id' => '', 'code' => ''));
        
        // Ensure at least one trigger exists
        if (empty($triggers)) {
            $triggers = array(array('id' => '', 'code' => ''));
        }
        ?>
        <style>
            /* Fix for CSS overflow issues */
            #booking-triggers-table {
                table-layout: fixed;
                width: 100%;
                border-collapse: collapse;
            }
            #booking-triggers-table th,
            #booking-triggers-table td {
                padding: 10px;
                vertical-align: top;
                overflow: visible;
            }
            #booking-triggers-table th:nth-child(1),
            #booking-triggers-table td:nth-child(1) {
                width: 20%;
            }
            #booking-triggers-table th:nth-child(2),
            #booking-triggers-table td:nth-child(2) {
                width: 60%;
            }
            #booking-triggers-table th:nth-child(3),
            #booking-triggers-table td:nth-child(3) {
                width: 20%;
                text-align: center;
                vertical-align: middle;
            }
            #booking-triggers-table textarea, 
            .trigger-instructions pre {
                max-width: 100%;
                box-sizing: border-box;
                overflow: auto;
                white-space: pre-wrap !important;
                word-wrap: break-word !important;
                word-break: break-word !important;
            }
            /* Fix for CodeMirror instances */
            .CodeMirror {
                height: auto !important;
                max-width: 100% !important;
                border: 1px solid #ddd;
                border-radius: 3px;
            }
            .CodeMirror-scroll {
                max-width: 100% !important;
                overflow-x: auto !important;
            }
            /* Force CodeMirror to wrap long lines */
            .CodeMirror pre.CodeMirror-line,
            .CodeMirror pre.CodeMirror-line-like {
                white-space: pre-wrap !important;
                word-break: break-all !important;
            }
            /* Fix for the How to Format Trigger Codes section */
            .how-to-use pre {
                white-space: pre-wrap !important;
                word-wrap: break-word !important;
                word-break: break-word !important;
                max-width: 100% !important;
                overflow-x: auto !important;
            }
            .blvd-buttons-container {
                display: flex;
                gap: 10px;
                margin-bottom: 15px;
            }
            .blvd-danger-button {
                background-color: #dc3545;
                color: white;
                border-color: #dc3545;
            }
            .blvd-danger-button:hover {
                background-color: #c82333;
                border-color: #bd2130;
            }
        </style>
        
        <table class="form-table" id="booking-triggers-table">
            <thead>
                <tr>
                    <th>Trigger ID</th>
                    <th>JavaScript Implementation</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($triggers as $index => $trigger) : ?>
                <tr class="trigger-row">
                    <td>
                        <input type="text" name="boulevard_booking_options[booking_triggers][<?php echo $index; ?>][id]" value="<?php echo esc_attr($trigger['id']); ?>" style="width: 100%; padding: 8px;" placeholder="e.g. wrinkles" />
                    </td>
                    <td>
                        <div style="position: relative;">
                            <textarea id="trigger_code_<?php echo $index; ?>" name="boulevard_booking_options[booking_triggers][<?php echo $index; ?>][code]" rows="4" style="width: 100%; font-family: monospace; tab-size: 2; background: #f8f8f8; border: 1px solid #ddd; padding: 8px; border-radius: 3px;"><?php echo esc_textarea($trigger['code']); ?></textarea>
                            <div style="position: absolute; top: 5px; right: 5px; z-index: 100;">
                                <span class="description" style="background: #f0f0f0; padding: 2px 5px; border-radius: 3px; font-size: 10px;">JS</span>
                            </div>
                        </div>
                    </td>
                    <td style="vertical-align: middle; text-align: center;">
                        <button type="button" class="button remove-trigger" <?php echo ($index === 0 && count($triggers) === 1) ? 'disabled' : ''; ?>>Remove</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="blvd-buttons-container">
            <button type="button" class="button" id="add-new-trigger">Add New Trigger</button>
            <button type="button" class="button blvd-danger-button" id="reset-all-triggers">Reset All Triggers</button>
        </div>
        
        <div style="margin-top: 20px; background: #f9f9f9; padding: 15px; border: 1px solid #ddd;">
            <h4 style="margin-top: 0;">How to Format Trigger Codes</h4>
            <p>When adding Boulevard booking triggers, enter the <strong>complete</strong> function call including the <code>blvd.openBookingWidget()</code> part. For example:</p>
            <pre style="background: #f0f0f0; padding: 10px; overflow: auto; margin-bottom: 10px; white-space: pre-wrap; word-wrap: break-word; word-break: break-word; max-width: 100%;">blvd.openBookingWidget({ urlParams: { locationId: 'b7d45e12-3f89-4a67-9c21-f8e76d5b2a03', path: '/cart/menu/Services/s_12345678-abcd-4321-efgh-987654321abc', visitType: 'SELF_VISIT' }});</pre>
            
            <p><strong>Common Parameters:</strong></p>
            <ul style="margin-left: 20px; list-style-type: disc;">
                <li><code>locationId</code>: Your Boulevard location ID (required)</li>
                <li><code>path</code>: Path to specific category or service</li>
                <li><code>visitType</code>: Usually set to 'SELF_VISIT'</li>
            </ul>
            
            <p><strong>Important:</strong> You can get these values from the Boulevard dashboard under Services > Direct links for online booking.</p>
            <p><strong>Note:</strong> After adding or modifying triggers, click the "Save Changes" button for them to take effect.</p>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Initialize CodeMirror for all existing trigger code textareas
            if (typeof wp !== 'undefined' && wp.codeEditor) {
                $('textarea[id^="trigger_code_"]').each(function() {
                    wp.codeEditor.initialize($(this), {
                        codemirror: {
                            mode: 'javascript',
                            lineNumbers: true,
                            indentUnit: 2,
                            tabSize: 2,
                            autoCloseBrackets: true,
                            matchBrackets: true,
                            lineWrapping: true,
                            wordWrap: true
                        }
                    });
                });
            }
            // Fix for the Add New Trigger button
            var triggerCount = <?php echo count($triggers); ?>;
            
            $('#add-new-trigger').on('click', function() {
                // Create a new blank row - using HTML directly instead of cloning to avoid copying values
                var newRow = $('<tr class="trigger-row"></tr>');
                
                // Add ID cell
                newRow.append(
                    '<td>' +
                    '<input type="text" name="boulevard_booking_options[booking_triggers][' + triggerCount + '][id]" ' +
                    'value="" style="width: 100%; padding: 8px;" placeholder="e.g. wrinkles" />' +
                    '</td>'
                );
                
                // Add code cell with textarea
                newRow.append(
                    '<td>' +
                    '<div style="position: relative;">' +
                    '<textarea id="trigger_code_' + triggerCount + '" ' +
                    'name="boulevard_booking_options[booking_triggers][' + triggerCount + '][code]" ' +
                    'rows="4" style="width: 100%; font-family: monospace; tab-size: 2; ' +
                    'background: #f8f8f8; border: 1px solid #ddd; padding: 8px; border-radius: 3px;"></textarea>' +
                    '<div style="position: absolute; top: 5px; right: 5px; z-index: 100;">' +
                    '<span class="description" style="background: #f0f0f0; padding: 2px 5px; ' +
                    'border-radius: 3px; font-size: 10px;">JS</span>' +
                    '</div>' +
                    '</div>' +
                    '</td>'
                );
                
                // Add remove button cell
                newRow.append(
                    '<td style="vertical-align: middle; text-align: center;">' +
                    '<button type="button" class="button remove-trigger">Remove</button>' +
                    '</td>'
                );
                
                // Append the new row to the table
                $('#booking-triggers-table tbody').append(newRow);
                
                // Try to initialize code editor for the new field
                if (typeof wp !== 'undefined' && wp.codeEditor) {
                    var newTextarea = $('#trigger_code_' + triggerCount);
                    if (newTextarea.length) {
                        wp.codeEditor.initialize(newTextarea, {
                            codemirror: {
                                mode: 'javascript',
                                lineNumbers: true,
                                indentUnit: 2,
                                tabSize: 2,
                                autoCloseBrackets: true,
                                matchBrackets: true,
                                lineWrapping: true,
                                wordWrap: true
                            }
                        });
                    }
                }
                
                // Increment the counter
                triggerCount++;
                
                return false;
            });
            
            // Event delegation for remove buttons
            $('#booking-triggers-table').on('click', '.remove-trigger', function() {
                // If this is not the last row, remove it
                if ($('#booking-triggers-table tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                }
                return false;
            });
            
            // Reset All Triggers button
            $('#reset-all-triggers').on('click', function() {
                if (confirm('Are you sure you want to remove all triggers? This action cannot be undone until you save changes.')) {
                    // Clear all rows
                    $('#booking-triggers-table tbody').empty();
                    
                    // Add a single empty row
                    var newRow = $('<tr class="trigger-row"></tr>');
                    
                    // Add ID cell
                    newRow.append(
                        '<td>' +
                        '<input type="text" name="boulevard_booking_options[booking_triggers][0][id]" ' +
                        'value="" style="width: 100%; padding: 8px;" placeholder="e.g. wrinkles" />' +
                        '</td>'
                    );
                    
                    // Add code cell with textarea
                    newRow.append(
                        '<td>' +
                        '<div style="position: relative;">' +
                        '<textarea id="trigger_code_0" ' +
                        'name="boulevard_booking_options[booking_triggers][0][code]" ' +
                        'rows="4" style="width: 100%; font-family: monospace; tab-size: 2; ' +
                        'background: #f8f8f8; border: 1px solid #ddd; padding: 8px; border-radius: 3px;"></textarea>' +
                        '<div style="position: absolute; top: 5px; right: 5px; z-index: 100;">' +
                        '<span class="description" style="background: #f0f0f0; padding: 2px 5px; ' +
                        'border-radius: 3px; font-size: 10px;">JS</span>' +
                        '</div>' +
                        '</div>' +
                        '</td>'
                    );
                    
                    // Add remove button cell (disabled since it's the only row)
                    newRow.append(
                        '<td style="vertical-align: middle; text-align: center;">' +
                        '<button type="button" class="button remove-trigger" disabled>Remove</button>' +
                        '</td>'
                    );
                    
                    // Append the new row to the table
                    $('#booking-triggers-table tbody').append(newRow);
                    
                    // Reset the counter
                    triggerCount = 1;
                    
                    // Try to initialize code editor for the new field
                    if (typeof wp !== 'undefined' && wp.codeEditor) {
                        var newTextarea = $('#trigger_code_0');
                        if (newTextarea.length) {
                            wp.codeEditor.initialize(newTextarea, {
                                codemirror: {
                                    mode: 'javascript',
                                    lineNumbers: true,
                                    indentUnit: 2,
                                    tabSize: 2,
                                    autoCloseBrackets: true,
                                    matchBrackets: true
                                }
                            });
                        }
                    }
                    
                    // Show a notice that changes need to be saved
                    alert('All triggers have been reset. Click "Save Changes" to apply this change.');
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Output the Boulevard initialization script in the header
     */
    public function output_header_script() {
        $this->options = get_option('boulevard_booking_options');
        
        if (!empty($this->options['init_script'])) {
            // Output the script directly without wp_kses_post to preserve script tags
            echo $this->options['init_script'];
        }
    }

    /**
     * Enqueue scripts for the frontend
     */
    public function enqueue_scripts() {
        // Register and enqueue the Boulevard script
        wp_register_script('boulevard-booking-script', plugins_url('js/boulevard-booking.js', __FILE__), array('jquery'), '1.0.1', true);
        wp_enqueue_script('boulevard-booking-script');
        
        // Localize the script with our data
        $this->options = get_option('boulevard_booking_options');
        $triggers_data = array();
        
        if (!empty($this->options['booking_triggers'])) {
            foreach ($this->options['booking_triggers'] as $trigger) {
                if (!empty($trigger['id']) && !empty($trigger['code'])) {
                    $triggers_data[$trigger['id']] = $trigger['code'];
                }
            }
        }
        
        wp_localize_script('boulevard-booking-script', 'boulevardBooking', array(
            'triggers' => $triggers_data
        ));
    }
    
    /**
     * Add settings link to plugin page
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="admin.php?page=boulevard-booking">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}

// Create plugin directory structure
function boulevard_booking_activate() {
    $plugin_dir = plugin_dir_path(__FILE__);
    if (!file_exists($plugin_dir . 'js')) {
        mkdir($plugin_dir . 'js', 0755, true);
    }
    
    // Create JavaScript file
    $js_file = $plugin_dir . 'js/boulevard-booking.js';
    if (!file_exists($js_file) || true) { // Always update the JS file on activation
        $js_content = <<<'EOD'
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Check if Boulevard triggers exist
        if (typeof boulevardBooking === 'undefined' || !boulevardBooking.triggers) {
            console.error('Boulevard Booking: No triggers defined or plugin not properly initialized');
            return;
        }
        
        console.log('Boulevard Booking: Initializing with triggers:', Object.keys(boulevardBooking.triggers));
        
        // Process each trigger - set up click handlers for elements with matching IDs
        $.each(boulevardBooking.triggers, function(triggerId, triggerCode) {
            console.log('Boulevard Booking: Setting up handler for #' + triggerId);
            
            // Direct ID click handler
            $(document).on('click', '#' + triggerId, function(e) {
                e.preventDefault();
                console.log('Boulevard Booking: Trigger clicked: ' + triggerId);
                
                // Execute the Boulevard booking code directly
                try {
                    // Check if window.blvd exists (Boulevard script is loaded)
                    if (typeof window.blvd === 'undefined') {
                        console.error('Boulevard Booking: Boulevard script not loaded. Make sure the Boulevard initialization script is properly added to your site.');
                        return;
                    }
                    
                    // Execute the trigger code
                    eval(triggerCode);
                } catch (err) {
                    console.error('Boulevard Booking: Error executing trigger code for ' + triggerId + ':', err);
                }
                
                return false;
            });
        });
        
        // Handle clicks on links with href="#triggerID"
        $(document).on('click', 'a[href^="#"]', function(e) {
            var href = $(this).attr('href');
            var triggerId = href.substring(1); // Remove the # character
            
            if (boulevardBooking.triggers[triggerId]) {
                e.preventDefault();
                console.log('Boulevard Booking: Link with href clicked: ' + href);
                
                try {
                    // Check if window.blvd exists (Boulevard script is loaded)
                    if (typeof window.blvd === 'undefined') {
                        console.error('Boulevard Booking: Boulevard script not loaded. Make sure the Boulevard initialization script is properly added to your site.');
                        return;
                    }
                    
                    // Execute the trigger code
                    eval(boulevardBooking.triggers[triggerId]);
                } catch (err) {
                    console.error('Boulevard Booking: Error executing trigger code for ' + triggerId + ':', err);
                }
                
                return false;
            }
        });
        
        // Also check if the URL has a hash that matches a trigger ID
        $(window).on('load hashchange', function() {
            var hash = window.location.hash;
            if (hash && hash.length > 1) {
                var triggerId = hash.substring(1); // Remove the # character
                
                if (boulevardBooking.triggers[triggerId]) {
                    console.log('Boulevard Booking: URL hash matches trigger: ' + triggerId);
                    
                    try {
                        // Check if window.blvd exists (Boulevard script is loaded)
                        if (typeof window.blvd === 'undefined') {
                            console.error('Boulevard Booking: Boulevard script not loaded. Make sure the Boulevard initialization script is properly added to your site.');
                            return;
                        }
                        
                        // Execute the trigger code
                        eval(boulevardBooking.triggers[triggerId]);
                    } catch (err) {
                        console.error('Boulevard Booking: Error executing trigger code for ' + triggerId + ':', err);
                    }
                }
            }
        });
    });
})(jQuery);
EOD;
        file_put_contents($js_file, $js_content);
    }
}
register_activation_hook(__FILE__, 'boulevard_booking_activate');

$boulevard_booking_widget = new Boulevard_Booking_Widget();
