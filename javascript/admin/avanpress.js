// IIFE - Immediately Invoked Function Expression
(function(avanpress) {

    // The global jQuery object is passed as a parameter
    avanpress(window.jQuery, window, document);

}(function($, window, document) {

    // The $ is now locally scoped

    // Listen for the jQuery ready event on the document
    $(function() {
        $('.ap-import-products').on('click', function(){
           importProducts();
        });

        // check connection event listeners
        $('.ap-check-connection').on('click', function(){
            checkConnection();
        });
    });

    function importProducts(){
        var data = {
            'action': 'import_products',
        };

        $.post(ajaxurl, data, function(response) {
            alert('Server response from the AJAX URL ' + response);
        });
    }

    function checkConnection(){
        var data = {
            'action': 'check_connection',
        };

        $.post(ajaxurl, data, function(response) {
            var connectionStatus = false;
            if (response){
                var responseJSON     = jQuery.parseJSON(response);
                connectionStatus = responseJSON.connectionStatus
            }

            if (false == connectionStatus){
                alert("could not connect");
            } else {
                alert("connection successful");
            }
        });
    }

}));