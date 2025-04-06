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
