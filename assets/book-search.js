jQuery(document).ready(function($) {
    $('#searchform').on('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        $.ajax({
            url: bookSearchAjax.ajax_url,
            type: 'GET',
            data: $(this).serialize() + '&action=book_search',
            success: function(response) {
                console.log(response); // Log the response to the console
                
                $('#book-results').html(''); // Clear previous results

                $('#book-results').html(response)

            },
            error: function() {
                $('#book-results').html('<p>Error retrieving books. Please try again.</p>');
            }
        });
    });


});
