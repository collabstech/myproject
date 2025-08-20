<div class="container">
    <div class="row">
        <div class="col-12"> 
            <div class="alert-message alert" role="alert" style="display: none;">
            </div>
        </div>
        <div class="col-3"> 
            @if (Auth::user()->role != \App\User::ROLE_MAIN_DEALER)
            <h4>
                Main Dealer
            </h4>
            <select name="main-dealer-week" id="main-dealer-week" class="select2" style="width: 200px;">
                <option value="0">
                    Select Main Dealer
                </option>
            </select>
            <br/><br/>
            <h4>
                Week
            </h4>
            <select name="week-select" id="week-select" class="select2" style="width: 200px;">
                <option value="0">
                    Select Week
                </option>
            </select>
            <br/><br/>
            <button class="btn btn-primary" id="generate-week-chart" style="width: 200px;">Generate Chart</button>
            @endif

            @if (Auth::user()->role == \App\User::ROLE_ADMIN)
            <br/><br/><br/>

            <form id="form-week" class="form-horizontal form-week" action="{{ route('project.week.import') }}" method="POST" enctype="multipart/form-data">
                {{ csrf_field() }}
                <input type="file" id="file-import-week" name="import_week" class="file-hidden project-file" />
                <div class="thumbnail-dashed thumbnail-progress" style="width: 200px;"><i class="fa fa-file"></i></div>
                <br/>
                <div class="progress-save-week"></div>
                <button type="button" class="btn btn-success" id="btn-import-week" style="width: 120px;">Choose File</button>
                <button type="submit" class="btn btn-primary" id="upload-week" style="width: 80px;">Upload</button>
                <br/><br/>
                <button type="button" class="btn btn-danger" style="width: 200px;" id="delete-week">Delete</button>
            </form>

            @endif
        </div>
        <div class="col-1"></div>
        <div class="col-8">
            <h4 style="text-align: center;">Progress Per Week</h4>
            <h6 style="text-align: center;"><span class="week-count"></span></h6>
            <canvas id="week-chart"></canvas>
        </div>
    </div>

    <br/><br/>
</div>

<style>
.progress-save-week {
    display: none;
    border: 8px solid #f3f3f3; /* Light grey */
    border-top: 8px solid #3498db; /* Blue */
    border-radius: 50%;
    width: 60px;
    height: 60px;
    animation: spin 2s linear infinite;
    text-align: center;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

@section('week-js')
<script type="text/javascript">
    $(document).ready(function() {
        loadMainDealersweek();
        loadMainweek();

        var weekData = {
                labels: [],
                datasets: [{
                    label: "Percent",
                    backgroundColor: [
                        '#e81f27',
                        '#4990E2'
                    ],
                    data: [],
                    datalabels: {
                        formatter: function(value, context) {
                            return value;
                        }
                    }
                }]
            };

        var weekOpt = {
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
                            autoSkip: false
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
                },
                tooltips: {
                    mode: 'nearest',
                    intersect: true,
                    callbacks: {
                        // Use the footer callback to display the sum of the items showing in the tooltip
                        label: function(tooltipItem, data) {
                            var label = tooltipItem.yLabel+" : ";

                            if(tooltipItem.yLabel == 'Target'){
                                label += '100%';
                            }else{
                                label += Math.round(data.datasets[tooltipItem.datasetIndex].data[1]/data.datasets[tooltipItem.datasetIndex].data[0]*100)+'%';
                            }
                            
                            return label;
                        }
                    },
                }
            };

        var weekCtx = document.getElementById('week-chart').getContext('2d');
        window.weekChart = new Chart(weekCtx, {
            type: 'horizontalBar',
            data: weekData,
            options: weekOpt
        });
        // window.weekChart.canvas.parentNode.style.height = '500px';

        $('#generate-week-chart').click(function () {
            mainDealerCode = $('#main-dealer-week').val();
            week = $('#week-select').val();
            text = $('#week-select option:selected').text();

            $.ajax({
                method: 'get',
                url: '{{ route('project.week.chart', ['uuid' => $project->uuid]) }}' 
                        + '?mainDealerCode=' + mainDealerCode
                        + '&week=' + week,
                error: function (response) {
                    
                },
                success: function (response) {
                    generateChartweek(response.achievement, response.labels);
                    if(week != 0){
                        periode = text.split('-');
                        $('.week-count').text('Periode '+periode[1]+' - '+periode[2]);
                    }else{
                        $('.week-count').text('Up to Week '+response.week);
                    }
                }
            });
        });

        @if (Auth::user()->role == \App\User::ROLE_ADMIN)
        $('#btn-import-week').click(function() {
            $('#file-import-week').click();

            return false;
        });

        $('.thumbnail-progress').click(function() {
            $('#file-import-week').click();
        });

        $('#file-import-week').change(function() {
            readURLweek(this, '.thumbnail-progress');
        });

        $('#form-week').submit(function(e) {
            e.preventDefault();

            var progressFile = $('#file-import-week')[0].files;
            var formData = new FormData();
            var file = progressFile[0];
            formData.append('import_week', file, file.name);
            formData.append('project_id', {!! $project->id !!});
            formData.append('_token', $('[name="_token"]').val());

            var progress = 0;

            $.ajax({
                method: 'post',
                url: '{{ route('project.week.import') }}',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function (response) {
                    $('#btn-import-week').hide();
                    $('#upload-week').hide();
                    $('#delete-week').hide();
                    for (let index = 0; index < 100; index++) {
                        progress++;
                    }
                    $('.alert-message').hide().removeClass('alert-success alert-warning');
                    $('.progress-save-week').show();
                },
                error: function (response) {
                    response = JSON.parse(response.responseText);
                    progress = 100;
                    $('.alert-message').show().removeClass('alert-success').addClass('alert-warning').text(response.message);
                    $('.progress-save-week').hide();
                    $('#btn-import-week').show();
                    $('#upload-week').show();
                    $('#delete-week').show();
                },
                success: function (response) {
                    progress = 100;
                    $('.alert-message').show().removeClass('alert-warning').addClass('alert-success').text(response.message);
                    $('.progress-save-week').hide();
                    $('#btn-import-week').show();
                    $('#upload-week').show();
                    $('#delete-week').show();
                    loadMainDealersweek();
                    loadMainweek();
                }
            });

            return false;
        });
        @endif
    });
    
    @if (Auth::user()->role == \App\User::ROLE_ADMIN)
    function readURLweek(input, fileClass) {
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

    function loadMainDealersweek() {
        $.ajax({
            method: 'get',
            url: '{{ route('project.week.maindealers', ['uuid' => $project->uuid]) }}',
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
                $('#main-dealer-week').html(htmlContent);
                @endif
            }
        });
    }

    function loadMainweek() {
        $.ajax({
            method: 'get',
            url: '{{ route('project.week.mainweek', ['uuid' => $project->uuid]) }}',
            error: function (response) {
                
            },
            success: function (response) {
                @if (Auth::user()->role != \App\User::ROLE_MAIN_DEALER)
                    htmlContent = '<option value="0">Select Week</option>';
                @endif
                for(i = 0; i < response.length; ++i) {
                    htmlContent += '<option value="' + response[i].week + '">' 
                            + 'Week ' + response[i].week + ' - ' + response[i].date + '</option>';
                }
                
                @if (Auth::user()->role != \App\User::ROLE_MAIN_DEALER)
                    $('#week-select').html(htmlContent);
                @endif
            }
        });
    }

    $('#delete-week').click(function() {
        var progress = 0;

        $.ajax({
            method: 'get',
            url: '{{ route('project.week.delete', ['uuid' => $project->uuid]) }}',
            error: function (response) {
                
            },
            beforeSend: function (response) {
                $('#btn-import-week').hide();
                $('#upload-week').hide();
                $('#delete-week').hide();
                for (let index = 0; index < 100; index++) {
                    progress++;
                }
                $('.alert-message').hide().removeClass('alert-success alert-warning');
                $('.progress-save-week').show();
            },
            error: function (response) {
                response = JSON.parse(response.responseText);
                progress = 100;
                $('.alert-message').show().removeClass('alert-success').addClass('alert-warning').text(response.message);
                $('.progress-save-week').hide();
                $('#btn-import-week').show();
                $('#upload-week').show();
                $('#delete-week').show();
            },
            success: function (response) {
                progress = 100;
                $('.alert-message').show().removeClass('alert-warning').addClass('alert-success').text(response.message);
                $('.progress-save-week').hide();
                $('#btn-import-week').show();
                $('#upload-week').show();
                $('#delete-week').show();
                loadMainDealersweek();
                loadMainweek();
            }
        });
    });

    function generateChartweek(achievementData, labels) {
        window.weekChart.data.datasets.forEach(function (dataset, key) {
            dataset.data = achievementData;
        });
        window.weekChart.config.data.labels = labels;
        window.weekChart.update();
    }
</script>
@endsection