<div class="container">
    <div class="row">
        <div class="col-12">
            <ul class='nav nav-tabs' id='custom-tabs-four-tab' role='tablist'>
                <li class='nav-item active'>
                  <a class='nav-link active' id='active-calls-tab' data-toggle='pill' href='#active-calls' data-bs-toggle="tab" data-bs-target="#active-calls" role='tab' aria-controls='active-calls' aria-selected='true'>Active Calls</a>
                </li>
                <li class='nav-item'>
                  <a class='nav-link' id='call-history-tab' data-toggle='pill' href='#call-history' data-bs-toggle="tab" data-bs-target="#call-history" role='tab' aria-controls='call-history' aria-selected='false'>Call History (Last 100)</a>
                </li>
            </ul>
            
            <div class='tab-content'>
                <div class="tab-pane fade in active" id="active-calls" role="tabpanel" aria-labelledby="active-calls-tab" tabindex="0">
                    <table class='table table-hover table-striped'>
                        <thead>
                            <th>Extension</th>
                            <th>Target</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>CID</th>
                            <th>Application</th>
                            <th>Actions</th>
                        </thead>
                        <tbody id="prech_table">

                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade in" id="call-history" role="tabpanel" aria-labelledby="call-history-tab" tabindex="0">
                    <table class='table table-hover table-striped' id="history">
                        <thead>
                            <th>Date</th>
                            <th>Extension</th>
                            <th>Target</th>
                            <th>Duration</th>
                            <th>CID</th>
                            <th>Application</th>
                        </thead>
                        <tbody id="prech_table_history">
                            <?php echo $prech_var; ?>
                        </tbody>
                    </table>
                </div>      
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var loop = setInterval(refreshCalls, 500);
    });
    
    function refreshCalls() {
        $.get("ajax.php?module=prechtest&command=getJSON", function(data, status) {
            console.log(data.message.message); 
            $('#prech_table').html(data.message.message);
        });
    }
    
    function hangupCall(callId) {
        $.get("ajax.php?module=prechtest&command=hangup&call=" + callId, function(data, status) {
                console.log(data.message); 
        });
    }
</script>