<div class="container">
    <div class="row">
        <div class="col-3"> 
            @if (Auth::user()->role != \App\User::ROLE_MAIN_DEALER)
            <h4>
            @if ($project->type == \App\Project::CQI_TYPE)
            District
            @else
            Main Dealer
            @endif
            </h4>
            <select name="main-dealer-interviewer" id="main-dealer-interviewer" class="select2" style="width: 200px;">
                <option value="0">
                @if ($project->type == \App\Project::CQI_TYPE)
                Select District
                @else
                Select Main Dealer
                @endif
                </option>
            </select>
            <br/><br/>
            <div class="mb-4">
                <div class="input-group input-daterange">
                    <input type="text" id="filter-date-from" class="form-control" placeholder="Start Date">
                    <div class="input-group-addon">to</div>
                    <input type="text" id="filter-date-to" class="form-control" placeholder="End Date">
                </div>
            </div>
            <button class="btn btn-primary" id="generate-interviewer-chart" style="width: 200px;">Generate Chart</button>
            @endif

            @if (Auth::user()->role == \App\User::ROLE_ADMIN)
            <br/><hr/>
            <form id="form-interviewer" class="form-horizontal form-interviewer" action="{{ route('project.interviewer.import') }}" method="POST" enctype="multipart/form-data">
                <h4>Upload Interviewer Data</h4>
                {{ csrf_field() }}
                <div class="mb-4">
                    <label>Exclude Date</label>
                    <div class="input-group input-daterange">
                        <input type="text" id="exclude-date-from" class="form-control" placeholder="Start Date">
                        <div class="input-group-addon">to</div>
                        <input type="text" id="exclude-date-to" class="form-control" placeholder="End Date">
                    </div>
                </div>
                <div>
                    <input type="file" id="file-import-interviewer"
                           name="import_interviewer" class="file-hidden project-file" required="required"
                           accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                    />
                    <div class="thumbnail-dashed interviewer-thumbnail-progress" style="width: 200px;">
                        <i class="fa fa-file"></i>
                    </div>
                    <div class="alert-message alert mt-2" role="alert" style="display: none;">
                    </div>
                    <div class="progress-save-interviewer"></div>
                </div>
                <div class="mt-4">
                    <button type="button" class="btn btn-success" id="btn-import-interviewer">Choose File</button>
                    <button type="button" class="btn btn-primary" id="upload-interviewer">Upload</button>
                </div>
            </form>

            @endif
        </div>
        <div class="col-1"></div>
        <div class="col-8" id="interview-chart-section">
            <h4 style="text-align: center;">Progress Per Interviewer</h4>
            <h6 style="text-align: center;">
                Jumlah Interviewer: <span class="interviewer-count">0</span>
                <br>
                Target: <span class="threshold-count">0</span>
            </h6>
            <div id="interview-chart-body">
                <canvas id="interviewer-chart"></canvas>
            </div>
        </div>
    </div>

    @if ($project->type == \App\Project::CSI_TYPE || $project->type == \App\Project::CSL_TYPE)
    @include('project.tab.interviewer.trend')
    @endif
    <br/><br/>
</div>

<style>
#interview-chart-section {
    overflow-x: auto;
}
.progress-save-interviewer {
    display: none;
    border: 8px solid #f3f3f3; /* Light grey */
    border-top: 8px solid #3498db; /* Blue */
    border-radius: 50%;
    width: 60px;
    height: 100px;
    animation: spin 2s linear infinite;
    text-align: center;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

@section('interviewer-js')
<script type="text/javascript">
    let defaultInterviewChartWidth = 500;
    $(document).ready(function() {
        generateHorizontalLineChartJs();
        loadMainDealersInterviewer();

        $('.input-daterange input').each(function() {
            $(this).datepicker({
                autoclose: true,
                format: 'yyyy-mm-dd',
                orientation: 'bottom',
            });
        });

        var interviewerData = {
                labels: [],
                datasets: [{
                    label: "Achievement",
                    backgroundColor: '#4990E2',
                    data: [],
                    datalabels: {
                        formatter: function(value, context) {
                            return value;
                        }
                    }
                }]
            };

        var interviewerOpt = {
            maintainAspectRatio: false,
            responsive: true,
            legend: {
                display: false
            },
            layout: {
                padding: {
                    top: 30,
                    bottom: 30,
                    right: 30,
                    left: 30
                }
            },
            title: {
                display: false
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    },
                    gridLines: {
                        display:false
                    }
                }],
                xAxes: [{
                    ticks: {
                        beginAtZero: true,
                        autoSkip: false,
                        stepSize: 5
                    },
                    gridLines: {
                        display:false
                    }
                }]
            },
            plugins: {
                datalabels: {
                    color: '#000000',
                    align: 'end',
                    anchor: 'end',
                    font: {
                        size: 12
                    }
                }
            }
        };

        var interviewerCtx = document.getElementById('interviewer-chart').getContext('2d');
        window.interviewerChart = new Chart(interviewerCtx, {
            type: 'bar',
            data: interviewerData,
            options: interviewerOpt
        });
        window.interviewerChart.canvas.parentNode.style.height = '500px';
        window.interviewerChart.canvas.parentNode.style.width = defaultInterviewChartWidth + 'px';

        $('#generate-interviewer-chart').click(function () {
            mainDealerCode = $('#main-dealer-interviewer').val();

            if (mainDealerCode == 0) {
                @if ($project->type == \App\Project::CQI_TYPE)
                alert('You have to select District');
                @else
                alert('You have to select Main Dealer');
                @endif
                return;
            }

            const filterDateFrom = $('#filter-date-from').val();
            const filterDateTo = $('#filter-date-to').val();

            if(filterDateFrom !== '' && filterDateTo !== '') {
                const filterDateFromDate = new Date(filterDateFrom);
                const filterDateToDate = new Date(filterDateTo);
                if (filterDateFromDate > filterDateToDate) {
                    alert('Filter date from must be less than filter date to');
                    return false;
                }
            }
            let url = '{{ route('project.interviewer.chart', ['uuid' => $project->uuid]) }}?mainDealerCode=' + mainDealerCode

            if (filterDateFrom !== '') {
                url += '&filterDateFrom=' + filterDateFrom;
            }
            if (filterDateTo !== '') {
                url += '&filterDateTo=' + filterDateTo;
            }
            $.ajax({
                method: 'get',
                url: url,
                error: function (response) {

                },
                success: function (response) {
                    generateChartInterviewer(response.achievement, response.labels, response.threshold);
                    $('.interviewer-count').text(response.labels.length);
                    $('.threshold-count').text(response.threshold);
                }
            });
        });

        @if (Auth::user()->role == \App\User::ROLE_ADMIN)

        $('#btn-import-interviewer').click(function() {
            $('#file-import-interviewer').trigger('click');
        });

        $('.interviewer-thumbnail-progress').click(function() {
            $('#file-import-interviewer').trigger('click');
        });

        $('#file-import-interviewer').change(function() {
            $('.interviewer-thumbnail-progress').html(`<i class="fa fa-file"></i>`);
            readURLInterviewer(this, '.interviewer-thumbnail-progress');
        });

        $('#upload-interviewer').click(function(e) {
            const progressFile = $('#file-import-interviewer')[0].files;
            if (progressFile.length === 0) {
                alert('Please select file');
                return false;
            }
            const excludeDateFrom = $('#exclude-date-from').val();
            const excludeDateTo = $('#exclude-date-to').val();

            if(excludeDateFrom !== '' && excludeDateTo !== '') {
                const excludeDateFromDate = new Date(excludeDateFrom);
                const excludeDateToDate = new Date(excludeDateTo);
                if (excludeDateFromDate > excludeDateToDate) {
                    alert('Exclude date from must be less than exclude date to');
                    return false;
                }
            }

            const allowedExtensions = /(\.xlsx|\.xls|\.csv)$/i;

            if (!allowedExtensions.exec(progressFile[0].name)) {
                alert('Please select file with extension .xlsx, .xls or .csv');
                return false;
            }

            if (progressFile[0].size > 5242880) {
                alert('File size must be less than 5MB');
                return false;
            }

            const formData = new FormData();
            const file = progressFile[0];
            formData.append('import_interviewer', file, file.name);
            formData.append('project_id', {!! $project->id !!});
            formData.append('exclude_date_from', $('#exclude-date-from').val());
            formData.append('exclude_date_to', $('#exclude-date-to').val());
            formData.append('_token', $('[name="_token"]').val());

            let progress = 0;

            $.ajax({
                method: 'post',
                url: '{{ route('project.interviewer.import') }}',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function (response) {
                    $('#btn-import-interviewer').hide();
                    $('#upload-interviewer').hide();
                    for (let index = 0; index < 100; index++) {
                        progress++;
                    }
                    $('.alert-message').hide().removeClass('alert-success alert-warning');
                    $('.progress-save-interviewer').show();
                },
                error: function (response) {
                    response = JSON.parse(response.responseText);
                    progress = 100;
                    $('.alert-message').show().removeClass('alert-success').addClass('alert-warning').text(response.message);
                    $('.interviewer-thumbnail-progress').html(`<i class="fa fa-file"></i>`);
                    $("#file-import-interviewer").val('');
                    $('.progress-save-interviewer').hide();
                    $('#btn-import-interviewer').show();
                    $('#upload-interviewer').show();
                },
                success: function (response) {
                    progress = 100;
                    $('.alert-message').show().removeClass('alert-warning').addClass('alert-success').text(response.message);
                    $('.interviewer-thumbnail-progress').html(`<i class="fa fa-file"></i>`);
                    $('.progress-save-interviewer').hide();
                    $('#btn-import-interviewer').show();
                    $('#upload-interviewer').show();
                    clearAllFormAndChartData()
                    loadMainDealersInterviewer();
                    loadIDInterviewer();
                }
            });

            return false;
        });
        @endif
    });
    
    @if (Auth::user()->role == \App\User::ROLE_ADMIN)
    function readURLInterviewer(input, fileClass) {
        if (input.files && input.files[0]) {
            for(i=0; i < input.files.length; i++) {
                var fileReader = new FileReader();
                fileReader.onload = function(file) {
                }
                fileReader.fileName = input.files[i].name;
                fileReader.readAsBinaryString(input.files[i]);
            }
            $(fileClass).html(fileReader.fileName);
        }
    }
    @endif

    function generateHorizontalLineChartJs(){
        Chart.pluginService.register({
            afterDraw: function(chart) {
                if (typeof chart.config.options.lineAt === 'undefined') {
                    return;
                }

                let lineAt = chart.config.options.lineAt;
                let ctxPlugin = chart.chart.ctx;
                let xAxe = chart.scales[chart.config.options.scales.xAxes[0].id];
                let yAxe = chart.scales[chart.config.options.scales.yAxes[0].id];

                if(yAxe.min !== 0) {
                    return;
                }

                ctxPlugin.strokeStyle = "#FF5B57";
                ctxPlugin.lineWidth = 3;
                ctxPlugin.beginPath();

                lineAt = (lineAt - yAxe.min) * (100 / yAxe.max);
                lineAt = (100 - lineAt) / 100 * (yAxe.height) + yAxe.top;
                ctxPlugin.moveTo(xAxe.left, lineAt);
                ctxPlugin.lineTo(xAxe.right, lineAt);
                ctxPlugin.stroke();
            }
        });
    }

    function loadMainDealersInterviewer() {
        $.ajax({
            method: 'get',
            url: '{{ route('project.interviewer.maindealers', ['uuid' => $project->uuid]) }}',
            error: function (response) {
                
            },
            success: function (response) {
                @if (Auth::user()->role != \App\User::ROLE_MAIN_DEALER)
                @if ($project->type == \App\Project::CQI_TYPE)
                htmlContent = '<option value="0">Select District</option>';
                @else
                htmlContent = '<option value="0">Select Main Dealer</option>';
                @endif
                @endif
                for(i = 0; i < response.length; ++i) {
                    htmlContent += '<option value="' + response[i].main_dealer_code + '">' 
                            + response[i].main_dealer_code + ' - ' + response[i].main_dealer_name + '</option>';
                }
                
                @if (Auth::user()->role != \App\User::ROLE_MAIN_DEALER)
                $('#main-dealer-interviewer').html(htmlContent);
                $('#main-dealer-trend-interviewer').html(htmlContent);
                @endif
            }
        });
    }

    function generateChartInterviewer(achievementData, labels, threshold) {
        window.interviewerChart.data.datasets.forEach(function (dataset, key) {
            dataset.data = achievementData;
        });
        let newWidth = (labels.length * 30);

        window.interviewerChart.config.data.labels = labels;
        window.interviewerChart.canvas.parentNode.style.width =  newWidth + 'px';
        window.interviewerChart.config.options.lineAt= threshold;

        if(newWidth < defaultInterviewChartWidth){
            window.interviewerChart.canvas.parentNode.style.width = defaultInterviewChartWidth + 'px';
        }

        window.interviewerChart.update();
        window.interviewerChart.resize();
    }

    function clearAllFormAndChartData(){
        $("#file-import-interviewer").val('');
        $('#exclude-date-from').val();
        $('#exclude-date-to').val();
        $("#filter-date-from").val('');
        $("#filter-date-to").val('');
        $('#filter-trend-date-from').val('');
        $('#filter-trend-date-to').val('');
        $('.interviewer-count').text("0");
        $('.threshold-count').text("0");
        window.interviewerChart.chart.ctx.lineWidth = 0;
        removeChartData(window.interviewerChart);
        removeChartData(window.trendInterviewerChart);
    }

    function removeChartData(chart) {
        chart.config.data.labels = [];
        chart.data.datasets.forEach(function (dataset, key) {
            dataset.data = [];
        });
        chart.update();
    }
</script>
@yield('trend-interviewer-js')
@endsection