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
                    <div class="row" style="margin:0;">
                        <div class="col">
                            <table class='table table-hover table-striped'>
                                <thead>
                                <th class='text-center'>CID</th>
                                <th class='text-center'>Target</th>
                                <th class='text-center'>Duration</th>
                                <th class='text-center'>Status</th>
                                <th class='text-center'>Application</th>
                                <th class='text-center'>Actions</th>
                                </thead>
                                <tbody id="prech_table">

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 3em;">
                        <div class="col text-center">
                            <h2>Simulate a call</h2>
                        </div>
                    </div>
                    <div class="row text-center" style="margin-top: 1em;">
                        <div class="col">
                            <form action="#" method="POST" id="call_form" class="form-inline" onsubmit="return false;">
                                <div class="row text-center">
                                    <div class="col-sm-5">
                                        <label class="control-label" for="call_src">Calling from</label>
                                        <input type="text" class="form-control" id="call_src" name="call_src" />
                                    </div>
                                    <div class="col-sm-1" style="margin-top: 1em;">
                                        <h1><i class="fa fa-arrow-right text-success" aria-hidden="true"></i></h1>
                                    </div>
                                    <div class="col-sm-5">
                                        <label class="control-label" class="control-label" for="call_dsc">Target number</label>
                                        <input type="text" class="form-control" id="call_dsc" name="call_dsc" />
                                    </div>
                                    <div class="col-sm-1">
                                        <button class="btn btn-success" id="call_btn" onClick="Call()">Call</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
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
    function refreshCalls() {
        $.get("ajax.php?module=prechtest&command=getJSON", function (data, status) {
            //console.log(data.message.message);
            $('#prech_table').html(data.message.message);
        });

    }

    function hangupCall(callId) {
        $.get("ajax.php?module=prechtest&command=hangup&call=" + callId, function (data, status) {
            //console.log(data.message);
        });
    }


    function Call() {
        var src = $("#call_src").val();
        var dsc = $("#call_dsc").val();
        var url = "ajax.php?module=prechtest&command=call&src=" + src + "&dsc=" + dsc;
        console.log(url);
        $.get(url, function (data, status) {
            //console.log(data.message.message);
            //console.log(data);
        });
    }


    $(document).ready(function () {
        setInterval(refreshCalls, 1000);
        $('#history').DataTable({
            paging: true,
            lengthChange: true,
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            responsive: true,
            language: {
                lengthMenu: 'Wy??wietl _MENU_ wynik??w na stron??',
                zeroRecords: 'Brak wynik??w!',
                info: 'Strona _PAGE_ z _PAGES_',
                infoEmpty: 'Brak wynik??w!',
                infoFiltered: '(wyfiltrowane z _MAX_ ????cznych wynik??w)',
                search: 'Szukaj',
                paginate: {
                    first: 'Pierwsza',
                    last: 'Ostatnia',
                    next: 'Nast??pna',
                    previous: 'Poprzednia'
                },
            },
            order: [[0, 'desc']],
            iDisplayLength: 10
        });
    });


</script>