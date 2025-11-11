jQuery(document).ready(function($) {
    
    // Media uploader
    var mediaUploader;
    
    $('#adnl_upload_image').on('click', function(e) {
        e.preventDefault();
        
        // If the uploader object has already been created, reopen the dialog
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        // Create the media uploader
        mediaUploader = wp.media({
            title: 'Select Ad Image',
            button: {
                text: 'Use This Image'
            },
            multiple: false,
            library: {
                type: ['image/png', 'image/gif', 'image/avif', 'image/jpeg', 'image/jpg', 'image/webp']
            }
        });
        
        // When an image is selected, run a callback
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#image_url').val(attachment.url);
            
            // Show preview
            $('#adnl_image_preview').html('<img src="' + attachment.url + '" alt="Preview">');
        });
        
        // Open the uploader dialog
        mediaUploader.open();
    });
    
    // Copy shortcode to clipboard
    $(document).on('click', '.adnl-copy-btn', function(e) {
        e.preventDefault();
        var shortcode = $(this).data('shortcode');
        
        // Create temporary input
        var tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(shortcode).select();
        document.execCommand('copy');
        tempInput.remove();
        
        // Change button text temporarily
        var button = $(this);
        var originalText = button.html();
        button.html('✓');
        button.css('background', '#00a32a');
        setTimeout(function() {
            button.html(originalText);
            button.css('background', '');
        }, 2000);
    });
    
    // Preview image on URL input
    $('#image_url').on('input', function() {
        var url = $(this).val();
        if (url) {
            $('#adnl_image_preview').html('<img src="' + url + '" alt="Preview">');
        } else {
            $('#adnl_image_preview').html('');
        }
    });
    
    // Search functionality
    $('#adnl-search').on('input', function() {
        filterLinks();
    });
    
    // Filter functionality
    $('#adnl-filter').on('change', function() {
        filterLinks();
    });
    
    function filterLinks() {
        var searchTerm = $('#adnl-search').val().toLowerCase();
        var filterType = $('#adnl-filter').val();
        var visibleCount = 0;
        
        $('.adnl-link-card').each(function() {
            var $card = $(this);
            var name = $card.data('name');
            var type = $card.data('type');
            
            var matchesSearch = searchTerm === '' || name.indexOf(searchTerm) !== -1;
            var matchesFilter = filterType === 'all' || type === filterType;
            
            if (matchesSearch && matchesFilter) {
                $card.show();
                visibleCount++;
            } else {
                $card.hide();
            }
        });
        
        // Show/hide no results message
        if (visibleCount === 0) {
            $('#adnl-no-results').show();
        } else {
            $('#adnl-no-results').hide();
        }
    }
    
    // Form reset
    $('#adnl-create-form').on('reset', function() {
        setTimeout(function() {
            $('#adnl_image_preview').html('');
        }, 10);
    });
    
    // Auto-dismiss success messages
    setTimeout(function() {
        $('.notice.is-dismissible').fadeOut();
    }, 5000);
    
});

    // Dynamic Shortcode Generation
    $(document).on('input change', '.adnl-attribute-grid input, .adnl-attribute-grid select', function() {
        var linkId = $(this).data('link');
        updateCustomShortcode(linkId);
    });
    
    function updateCustomShortcode(linkId) {
        var shortcode = '[ad_network_link id="' + linkId + '"';
        
        // Get all attributes for this link
        var redirect = $('.adnl-attr-redirect[data-link="' + linkId + '"]').val();
        var width = $('.adnl-attr-width[data-link="' + linkId + '"]').val();
        var height = $('.adnl-attr-height[data-link="' + linkId + '"]').val();
        var alt = $('.adnl-attr-alt[data-link="' + linkId + '"]').val();
        var title = $('.adnl-attr-title[data-link="' + linkId + '"]').val();
        var cssClass = $('.adnl-attr-class[data-link="' + linkId + '"]').val();
        var target = $('.adnl-attr-target[data-link="' + linkId + '"]').val();
        var style = $('.adnl-attr-style[data-link="' + linkId + '"]').val();
        
        // Build shortcode with attributes
        if (redirect) shortcode += ' redirect="' + redirect + '"';
        if (width) shortcode += ' width="' + width + '"';
        if (height) shortcode += ' height="' + height + '"';
        if (alt) shortcode += ' alt="' + alt + '"';
        if (title) shortcode += ' title="' + title + '"';
        if (cssClass) shortcode += ' class="' + cssClass + '"';
        if (target) shortcode += ' target="' + target + '"';
        if (style) shortcode += ' style="' + style + '"';
        
        shortcode += ']';
        
        // Update preview
        $('.adnl-custom-preview-' + linkId).text(shortcode);
    }
    
    // Copy custom shortcode
    $(document).on('click', '.adnl-copy-custom-btn', function(e) {
        e.preventDefault();
        var linkId = $(this).data('link');
        var shortcode = $('.adnl-custom-preview-' + linkId).text();
        
        // Create temporary input
        var tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(shortcode).select();
        document.execCommand('copy');
        tempInput.remove();
        
        // Change button text temporarily
        var button = $(this);
        var originalText = button.html();
        button.html('✓ Copied!');
        button.css('background', '#00a32a');
        setTimeout(function() {
            button.html(originalText);
            button.css('background', '');
        }, 2000);
    });
    
