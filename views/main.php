
<div class="container">
    <div class="row">
        <div class="col text-center">
            <h2>Active calls</h2>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table class='table table-hover table-striped'>
                <thead>
                    <th>Extension</th>
<!--                    <th>Name</th>
                    <th>DDI</th>-->
                    <th>Target</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Application</th>
                </thead>
                <tbody id="prech_table">

                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        var loop = setInterval(function() {
            $.get("ajax.php?module=prechtest&command=getJSON", function(data, status) {
                console.log(data.message.message); 
                $('#prech_table').html(data.message.message);
             });
        }, 500);

//                        setTimeout(function() {
//                            window.location.reload();
//                        }, 1000);
    });
</script>