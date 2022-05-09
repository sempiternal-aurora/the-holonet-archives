$(document).ready(function() {
    $('#show-advanced').click(function() {
        $(".advanced").toggle('slow');
        val = $("#show-advanced").html();
        if (val == 'Show Advanced') {
            $("#show-advanced").html('Hide Advanced');
        } else {
            $("#show-advanced").html('Show Advanced');
        }
    });
});