<div class="container">
    @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
    <div class="row">
        <div class="col-12">
            <div class="alert-message alert" id="info-upload-progress-cqi" role="alert" style="display: none;"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <div class="row">
                @if (Auth::user()->role == \App\User::ROLE_ADMIN)
                <div class="col-4">
                    <form id="form-upload-progress" class="form-horizontal form-progress-upload" action="{{ route('project.total.upload', ['uuid' => $project->uuid]) }}" method="POST" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="file" id="file-upload-progress" name="upload_progress" class="file-hidden" />
                        <div class="progress-upload"></div>
                        <button class="btn btn-primary" id="btn-upload-progress" style="width: 100%;">Upload File</button>
                        <button type="submit" id="submit-upload-progress" style="display: none;">
                    </form>
                </div>
                <div class="col-4">
                    <form id="form-delete-progress" class="form-horizontal form-delete-upload" action="{{ route('project.total.delete', ['uuid' => $project->uuid]) }}" method="POST">
                        {{ csrf_field() }}
                        <button class="btn btn-danger" id="btn-delete-progress" style="width: 100%;">Delete File</button>
                    </form>
                </div>  
                @endif
                @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
                <div class="col-4">
                    <button class="btn btn-success" id="btn-export-progress" style="width: 100%;">Export Excel</button>
                </div>  
                @endif
            </div>
            <br/>
        </div>
        <div class="col-6">
            @if (Auth::user()->role == \App\User::ROLE_ADMIN)
            <div class="row">
                <div class="col-6"></div>
                <div class="col-6">
                    <form id="form-progress" class="form-horizontal form-progress" action="{{ route('project.progresscqi.import', ['uuid' => $project->uuid]) }}" method="POST" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="file" id="file-import-progress" name="import_progress" class="file-hidden project-file" />
                        <div class="thumbnail-dashed thumbnail-progress" style="width: 200px;"><i class="fa fa-file"></i></div>
                        <br/>
                        <div class="alert-message alert" role="alert" style="display: none;">
                        </div>
                        <div class="progress-save"></div>
                        <button class="btn btn-success" id="btn-import-progress">Choose File</button>
                        <button type="submit" class="btn btn-primary" id="upload">Upload</button>
                    </form>
                </div>
            </div>    
            @endif    
        </div>
    </div>
    <hr/>
    @endif
    <div class="row">
        <div class="col-4">
            <div class="row">
                <div class="col-12">
                    <br/><br/><br/>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <button class="btn btn-primary" id="generate-type-chart" style="width: 100%;">Generate Chart</button>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="row">
                <div class="col-12"> 
                    <select name="motorcycle-type" id="motorcycle-type" class="select2" style="width: 100%;">
                        <option value="0">Select Motorcycle Type</option>
                    </select>
                    <br/><br/>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <button class="btn btn-primary" id="generate-model-chart" style="width: 100%;">Generate Chart</button>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="row">
                <div class="col-6"> 
                    <select name="motorcycle-type-2" id="motorcycle-type-2" class="select2" style="width: 100%;">
                        <option value="0">Select Motorcycle Type</option>
                    </select>
                    <br/><br/>
                </div>
                <div class="col-6"> 
                    <select name="motorcycle-model" id="motorcycle-model" class="select2" style="width: 100%;">
                        <option value="0">Select Model</option>
                    </select>
                    <br/><br/>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <button class="btn btn-primary" id="generate-mcycle-district-chart" style="width: 100%;">Generate Chart</button>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-4">
            <br/><br/>
            <h4 style="width: 100%; text-align: center;">Total Per Motorcycle Type</h4>
            <div style="width: 100%; text-align: center;">
                <span style="font-weight: bold; color: #242a30;">Total Achievement: </span>&nbsp;
                <span id="achievement-motorcycle-type" style="color: #242a30;"></span>
            </div>
            <canvas id="motorcycle-type-chart"></canvas>
        </div>
        <div class="col-4">
            <br/><br/>
            <h4 style="width: 100%; text-align: center;">Total Per Model</h4>
            <div style="width: 100%; text-align: center;">
                <span style="font-weight: bold; color: #242a30;">Total Achievement: </span>&nbsp;
                <span id="achievement-motorcycle-model" style="color: #242a30;"></span>
            </div>
            <canvas id="motorcycle-model-chart"></canvas>
        </div>
        <div class="col-4">
            <br/><br/>
            <h4 style="width: 100%; text-align: center;">Total Per District</h4>
            <div style="width: 100%; text-align: center;">
                <span style="font-weight: bold; color: #242a30;">Total Achievement: </span>&nbsp;
                <span id="achievement-district" style="color: #242a30;"></span>
            </div>
            <canvas id="district-chart"></canvas>
        </div>
    </div>
</div>

<style>
.progress-upload {
    display: none;
    border: 8px solid #f3f3f3; /* Light grey */
    border-top: 8px solid #3498db; /* Blue */
    border-radius: 50%;
    width: 60px;
    height: 60px;
    animation: spin 2s linear infinite;
    text-align: center;
}

.progress-save {
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

@section('total-js')
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.5.0"></script>
<script type="text/javascript">
    $(document).ready(function() {
        window.sortedTypeLabels = null;
        window.sortedDistrictLabels = null;
        window.sortedModelLabels = null;

        loadMotorcycleType('motorcycle-type');
        loadMotorcycleType('motorcycle-type-2');

        @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
        var mTypeData = {
                labels: [],
                datasets: [{
                    label: "Target",
                    backgroundColor: '#EB5757',
                    data: [],
                    datalabels: {
                        formatter: function(value, context) {
                            return value == 0 ? '0%' : '100%';
                        }
                    }
                },
                {
                    label: "Achievement",
                    backgroundColor: '#4990E2',
                    data: [],
                    datalabels: {
                        formatter: function(value, context) {
                            targetVal = mTypeData.datasets[0].data[context.dataIndex];
                            returnVal = 0;
                            if (targetVal != 0) {
                                returnVal = value / targetVal * 100
                            }
                            return Math.round(returnVal) + '%';
                        }
                    }
                }]
            };

        var mTypeOpt = {
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
                aspectRatio: 0.5
            };

        var mainDealerData = {
                labels: [],
                datasets: [{
                    label: "Target",
                    backgroundColor: '#EB5757',
                    data: [],
                    datalabels: {
                        formatter: function(value, context) {
                            return value == 0 ? '0%' : '100%';
                        }
                    }
                },
                {
                    label: "Achievement",
                    backgroundColor: '#4990E2',
                    data: [],
                    datalabels: {
                        formatter: function(value, context) {
                            targetVal = mainDealerData.datasets[0].data[context.dataIndex];
                            returnVal = 0;
                            if (targetVal != 0) {
                                returnVal = value / targetVal * 100
                            }
                            return Math.round(returnVal) + '%';
                        }
                    }
                }]
            };

        var mainDealerOpt = {
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
                            size: 8
                        }
                    }
                },
                aspectRatio: 0.5
            };

        var districtData = {
                labels: [],
                datasets: [{
                    label: "Target",
                    backgroundColor: '#EB5757',
                    data: [],
                    datalabels: {
                        formatter: function(value, context) {
                            return value == 0 ? '0%' : '100%';
                        }
                    }
                },
                {
                    label: "Achievement",
                    backgroundColor: '#4990E2',
                    data: [],
                    datalabels: {
                        formatter: function(value, context) {
                            targetVal = districtData.datasets[0].data[context.dataIndex];
                            returnVal = 0;
                            if (targetVal != 0) {
                                returnVal = value / targetVal * 100
                            }
                            return Math.round(returnVal) + '%';
                        }
                    }
                }]
            };

        var districtOpt = {
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
                            size: 8
                        }
                    }
                },
                aspectRatio: 0.5
            };
        @endif

        @if (Auth::user()->role == \App\User::ROLE_ADMIN)
        $('#btn-upload-progress').click(function() {
            $('#file-upload-progress').click();

            return false;
        });

        $('#file-upload-progress').change(function() {
            $('#submit-upload-progress').click();
        });

        $('#form-upload-progress').submit(function(e) {
            e.preventDefault();

            var progressFile = $('#file-upload-progress')[0].files;
            var formData = new FormData();
            var file = progressFile[0];
            formData.append('upload_progress', file, file.name);
            formData.append('_token', $('[name="_token"]').val());

            var progress = 0;

            $.ajax({
                method: 'post',
                url: '{{ route('project.total.upload', ['uuid' => $project->uuid]) }}',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function (response) {
                    $('#btn-upload-progress').hide();
                    for (let index = 0; index < 100; index++) {
                        progress++;
                    }
                    $('#info-upload-progress-cqi').hide().removeClass('alert-success alert-warning');
                    $('.progress-upload').show();
                },
                error: function (response) {
                    progress = 100;
                    $('#info-upload-progress-cqi').show().removeClass('alert-success').addClass('alert-warning').text(response.responseText);
                    $('.progress-upload').hide();
                    $('#btn-upload-progress').show();
                },
                success: function (response) {
                    progress = 100;
                    $('#info-upload-progress-cqi').show().removeClass('alert-warning').addClass('alert-success').text(response);
                    $('.progress-upload').hide();
                    $('#btn-upload-progress').show();
                }
            });

            return false;
        });

        $('#btn-delete-progress').click(function() {
            var formData = new FormData();
            formData.append('_token', $('[name="_token"]').val());

            $.ajax({
                method: 'post',
                url: '{{ route('project.total.delete', ['uuid' => $project->uuid]) }}',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function (response) {
                    $('#info-upload-progress-cqi').hide().removeClass('alert-success alert-warning');
                },
                error: function (response) {
                    $('#info-upload-progress-cqi').show().removeClass('alert-success').addClass('alert-warning').text(response.responseText);
                },
                success: function (response) {
                    $('#info-upload-progress-cqi').show().removeClass('alert-warning').addClass('alert-success').text(response);
                }
            });

            return false;
        });
        @endif

        @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
        $('#btn-export-progress').click(function() {
            localStorage.clear();
            window.open('{{ route('project.total.download', ['uuid' => $project->uuid]) }}', '_blank');
            return false;
        });
        @endif

        @if (Auth::user()->role == \App\User::ROLE_ADMIN)
        $('#btn-import-progress').click(function() {
            $('#file-import-progress').click();

            return false;
        });

        $('.thumbnail-progress').click(function() {
            $('#file-import-progress').click();
        });

        $('#file-import-progress').change(function() {
            readURL(this, '.thumbnail-progress');
        });

        $('#form-progress').submit(function(e) {
            e.preventDefault();

            var progressFile = $('#file-import-progress')[0].files;
            var formData = new FormData();
            var file = progressFile[0];
            formData.append('import_progress', file, file.name);
            formData.append('_token', $('[name="_token"]').val());

            var progress = 0;

            $.ajax({
                method: 'post',
                url: '{{ route('project.progresscqi.import', ['uuid' => $project->uuid]) }}',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function (response) {
                    $('#btn-import-progress').hide();
                    $('#upload').hide();
                    for (let index = 0; index < 100; index++) {
                        progress++;
                    }
                    $('.alert-message').hide().removeClass('alert-success alert-warning');
                    $('.progress-save').show();
                },
                error: function (response) {
                    response = JSON.parse(response.responseText);
                    progress = 100;
                    $('.alert-message').show().removeClass('alert-success').addClass('alert-warning').text(response.message);
                    $('.progress-save').hide();
                    $('#btn-import-progress').show();
                    $('#upload').show();
                },
                success: function (response) {
                    progress = 100;
                    $('.alert-message').show().removeClass('alert-warning').addClass('alert-success').text(response.message);
                    $('.progress-save').hide();
                    $('#btn-import-progress').show();
                    $('#upload').show();
                    loadMotorcycleType('motorcycle-type');
                    loadMotorcycleType('motorcycle-type-2');
                }
            });

            return false;
        });
        @endif

        @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
        var mTypeCtx = document.getElementById('motorcycle-type-chart').getContext('2d');
        window.mTypeChart = new Chart(mTypeCtx, {
            type: 'horizontalBar',
            data: mTypeData,
            options: mTypeOpt
        });
        window.mTypeChart.canvas.parentNode.style.height = '750px';
        
        var mainDealerCtx = document.getElementById('motorcycle-model-chart').getContext('2d');
        window.mModelChart = new Chart(mainDealerCtx, {
            type: 'horizontalBar',
            data: mainDealerData,
            options: mainDealerOpt
        });
        window.mModelChart.canvas.parentNode.style.height = '750px';

        var districtCtx = document.getElementById('district-chart').getContext('2d');
        window.districtChart = new Chart(districtCtx, {
            type: 'horizontalBar',
            data: districtData,
            options: districtOpt
        });
        window.districtChart.canvas.parentNode.style.height = '750px';
        @endif
        
        @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
        $('#motorcycle-type-2').change(function () {
            loadMotorcycleModel($(this).val());
        });

        $('#generate-type-chart').click(function () {
            requestChartData('motorcycle-type-chart', 'total-mtype', '0', '0');
        });

        $('#generate-model-chart').click(function () {
            type = $('#motorcycle-type').val();
            
            requestChartData('motorcycle-model-chart', 'total-mmodel', type, '0');
        });
        
        $('#generate-mcycle-district-chart').click(function () {
            type = $('#motorcycle-type-2').val();
            model = $('#motorcycle-model').val();

            requestChartData('district-chart', 'total-district', type, model);
        });
        @endif
    });

    @if (Auth::user()->role == \App\User::ROLE_ADMIN)
    function readURL(input, fileClass) {
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

    function requestChartData(elementId, chartType, type, model) {
        $.ajax({
            method: 'get',
            url: '{{ route('project.totalcqi.chart', ['uuid' => $project->uuid]) }}' 
                    + '?chartType=' + chartType + '&type=' + type + '&model=' + model,
            error: function (response) {
                alert(response.responseText);
            },
            success: function (response) {
               generateTotalChart(elementId, response.achievement, response.target, response.label, response.total_achievement);
            }
        });
    }

    function loadMotorcycleType(elementId) {
        $.ajax({
            method: 'get',
            url: '{{ route('project.progresscqi.mtype', ['uuid' => $project->uuid]) }}',
            error: function (response) {
                
            },
            success: function (response) {
                htmlContent = '<option value="0">Select Motorcycle Type</option>';
                for(i = 0; i < response.length; ++i) {
                    htmlContent += '<option value="' + response[i].type + '">' 
                            + response[i].type + '</option>';
                }
                
                $('#' + elementId).html(htmlContent);
            }
        });
    }

    function loadMotorcycleModel(type) {
        $.ajax({
            method: 'get',
            url: '{{ route('project.progresscqi.mmodel', ['uuid' => $project->uuid]) }}?type=' + type,
            error: function (response) {
                
            },
            success: function (response) {
                htmlContent = '<option value="0">Select Model</option>';
                for(i = 0; i < response.length; ++i) {
                    htmlContent += '<option value="' + response[i].model + '">' 
                            + response[i].model + '</option>';
                }
                $('#motorcycle-model').html(htmlContent);
            }
        });
    }

    function generateTotalChart(elementId, achievementData, targetData, labels, totalAchievement) {
        @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
        if(elementId == 'motorcycle-type-chart') {
            window.mTypeChart.data.datasets.forEach(function (dataset, key) {
                if(key == 1) {
                    dataset.data = achievementData;
                }
                if(key == 0) {
                    dataset.data = targetData;
                }
            });
            window.mTypeChart.config.data.labels = labels;
            window.mTypeChart.update();

            $('#achievement-motorcycle-type').html(totalAchievement);
        }
        
        if(elementId == 'motorcycle-model-chart') {
            window.mModelChart.data.datasets.forEach(function (dataset, key) {
                if(key == 1) {
                    dataset.data = achievementData;
                }
                if(key == 0) {
                    dataset.data = targetData;
                }
            });
            window.mModelChart.config.data.labels = labels;
            window.mModelChart.update();

            $('#achievement-motorcycle-model').html(totalAchievement);
        }

        if(elementId == 'district-chart') {
            window.districtChart.data.datasets.forEach(function (dataset, key) {
                if(key == 1) {
                    dataset.data = achievementData;
                }
                if(key == 0) {
                    dataset.data = targetData;
                }
            });
            window.districtChart.config.data.labels = labels;
            window.districtChart.update();

            $('#achievement-district').html(totalAchievement);
        }
        @endif
    }
</script>
@endsection