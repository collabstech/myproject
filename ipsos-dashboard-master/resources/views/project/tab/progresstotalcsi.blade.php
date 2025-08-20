<div class="container">
    @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
    <div class="row">
        <div class="col-12">
            <div class="alert-message alert" id="info-upload-progress" role="alert" style="display: none;"></div>
        </div>
    </div>
    <div class="row">
        @if (Auth::user()->role == \App\User::ROLE_ADMIN)
        <div class="col-2">
            <form id="form-upload-progress" class="form-horizontal form-progress-upload" action="{{ route('project.total.upload', ['uuid' => $project->uuid]) }}" method="POST" enctype="multipart/form-data">
                {{ csrf_field() }}
                <input type="file" id="file-upload-progress" name="upload_progress" class="file-hidden" />
                <div class="progress-upload"></div>
                <button class="btn btn-primary" id="btn-upload-progress" style="width: 100%;">Upload File</button>
                <button type="submit" id="submit-upload-progress" style="display: none;">
            </form>
        </div>
        <div class="col-2">
            <form id="form-delete-progress" class="form-horizontal form-delete-upload" action="{{ route('project.total.delete', ['uuid' => $project->uuid]) }}" method="POST">
                {{ csrf_field() }}
                <button class="btn btn-danger" id="btn-delete-progress" style="width: 100%;">Delete File</button>
            </form>
        </div>
        @endif
        <div class="col-2">
            <button class="btn btn-success" id="btn-export-progress" style="width: 100%;">Export Excel</button>
        </div>
    </div>
    <hr/>
    @endif
    <div class="row">
        @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
        <div class="col-4">
            <div class="row">
                <div class="col-12"> 
                    <select name="brand-survey" id="brand-survey" class="select2" style="width: 100%;">
                        <option value="0">Select Survey Type</option>
                        <option value="1">H1</option>
                        <option value="2">H2</option>
                        <option value="3">H3</option>
                    </select>
                    <br/><br/>
                </div>
            </div>
            <div class="row">
                <div class="col-12"> 
                    <select name="brand-respondent" id="brand-respondent" class="select2" style="width: 100%;">
                        <option value="0">Select Respondent Type</option>
                        <option value="1">Premium</option>
                        <option value="2">Regular</option>
                    </select>
                    <br/><br/>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <button class="btn btn-primary" id="generate-brand-chart" style="width: 100%;">Generate Chart</button>
                </div>
            </div>
        </div>
        @endif
        @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
        <div class="col-4">
        @else
        <div class="col-12">
        @endif
            @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
            <div class="row">
                <div class="col-12"> 
                    <select name="main-dealer-brand" id="main-dealer-brand" class="select2" style="width: 100%;">
                        <option value="0">Select Brand</option>
                    </select>
                    <br/><br/>
                </div>
            </div>
            @endif
            <div class="row">
                <div class="col-6"> 
                    <select name="main-dealer-survey" id="main-dealer-survey" class="select2" style="width: 100%;">
                        <option value="0">Select Survey Type</option>
                        <option value="1">H1</option>
                        <option value="2">H2</option>
                        <option value="3">H3</option>
                    </select>
                    <br/><br/>
                </div>
                <div class="col-6">
                    <select name="main-dealer-respondent" id="main-dealer-respondent" class="select2" style="width: 100%;">
                        <option value="0">Select Respondent Type</option>
                        <option value="1">Premium</option>
                        <option value="2">Regular</option>
                    </select>
                    <br/><br/>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <button class="btn btn-primary" id="generate-main-dealer-chart" style="width: 100%;">Generate Chart</button>
                </div>
            </div>
        </div>
        @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
        <div class="col-4">
            <div class="row">
                <div class="col-6"> 
                    <select name="district-brand" id="district-brand" class="select2" style="width: 100%;">
                        <option value="0">Select Brand</option>
                    </select>
                    <br/><br/>
                    <select name="district-survey" id="district-survey" class="select2" style="width: 100%;">
                        <option value="0">Select Survey Type</option>
                        <option value="1">H1</option>
                        <option value="2">H2</option>
                        <option value="3">H3</option>
                    </select>
                    <br/><br/>
                </div>
                <div class="col-6"> 
                    <select name="district-main-dealer" id="district-main-dealer" class="select2" style="width: 100%;">
                        <option value="0">Select Main Dealer</option>
                    </select>
                    <br/><br/>
                    <select name="district-respondent" id="district-respondent" class="select2" style="width: 100%;">
                        <option value="0">Select Respondent Type</option>
                        <option value="1">Premium</option>
                        <option value="2">Regular</option>
                    </select>
                    <br/><br/>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <button class="btn btn-primary" id="generate-district-chart" style="width: 100%;">Generate Chart</button>
                </div>
            </div>
        </div>
        @endif
    </div>
    <div class="row">
        @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
        <div class="col-4">
            <br/><br/>
            <h4 style="width: 100%; text-align: center;">Total Per Brand</h4>
            <div style="width: 100%; text-align: center;">
                <span style="font-weight: bold; color: #242a30;">Total Achievement: </span>&nbsp;
                <span id="achievement-brand" style="color: #242a30;"></span>
            </div>
            <canvas id="brand-chart"></canvas>
        </div>
        @endif
        @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
        <div class="col-4">
        @else
        <div class="col-12">
        @endif
            <br/><br/>
            <h4 style="width: 100%; text-align: center;">Total Per Main Dealer</h4>
            <div style="width: 100%; text-align: center;">
                <span style="font-weight: bold; color: #242a30;">Total Achievement: </span>&nbsp;
                <span id="achievement-main-dealer" style="color: #242a30;"></span>
            </div>
            <canvas id="main-dealer-chart"></canvas>
        </div>
        @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
        <div class="col-4">
            <br/><br/>
            <h4 style="width: 100%; text-align: center;">Total Per District</h4>
            <div style="width: 100%; text-align: center;">
                <span style="font-weight: bold; color: #242a30;">Total Achievement: </span>&nbsp;
                <span id="achievement-district" style="color: #242a30;"></span>
            </div>
            <canvas id="district-chart"></canvas>
        </div>
        @endif
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

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

@section('total-js')
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.5.0"></script>
<script type="text/javascript">
    $(document).ready(function() {
        window.sortedBrandLabels = null;
        window.sortedDistrictLabels = null;
        window.sortedMainDealerLabels = null;

        loadTotalBrands('main-dealer-brand');

        @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
        loadTotalBrands('district-brand');
        @endif

        @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
        var brandData = {
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
                            targetVal = brandData.datasets[0].data[context.dataIndex];
                            returnVal = 0;
                            if (targetVal != 0) {
                                returnVal = value / targetVal * 100
                            }
                            return Math.round(returnVal) + '%';
                        }
                    }
                }]
            };

        var brandOpt = {
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
        @endif

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
                @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
                aspectRatio: 0.5
                @endif
            };

        @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
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
                    $('#info-upload-progress').hide().removeClass('alert-success alert-warning');
                    $('.progress-upload').show();
                },
                error: function (response) {
                    progress = 100;
                    $('#info-upload-progress').show().removeClass('alert-success').addClass('alert-warning').text(response.responseText);
                    $('.progress-upload').hide();
                    $('#btn-upload-progress').show();
                },
                success: function (response) {
                    progress = 100;
                    $('#info-upload-progress').show().removeClass('alert-warning').addClass('alert-success').text(response);
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
                    $('#info-upload-progress').hide().removeClass('alert-success alert-warning');
                },
                error: function (response) {
                    $('#info-upload-progress').show().removeClass('alert-success').addClass('alert-warning').text(response.responseText);
                },
                success: function (response) {
                    $('#info-upload-progress').show().removeClass('alert-warning').addClass('alert-success').text(response);
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

        @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
        var brandCtx = document.getElementById('brand-chart').getContext('2d');
        window.brandChart = new Chart(brandCtx, {
            type: 'horizontalBar',
            data: brandData,
            options: brandOpt
        });
        window.brandChart.canvas.parentNode.style.height = '750px';
        @endif

        var mainDealerCtx = document.getElementById('main-dealer-chart').getContext('2d');
        window.mainDealerChart = new Chart(mainDealerCtx, {
            type: 'horizontalBar',
            data: mainDealerData,
            options: mainDealerOpt
        });
        window.mainDealerChart.canvas.parentNode.style.height = '750px';

        @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
        var districtCtx = document.getElementById('district-chart').getContext('2d');
        window.districtChart = new Chart(districtCtx, {
            type: 'horizontalBar',
            data: districtData,
            options: districtOpt
        });
        window.districtChart.canvas.parentNode.style.height = '750px';
        @endif
        
        @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
        $('#district-brand').change(function () {
            loadTotalMainDealers('district-main-dealer', $('#district-brand').val());
        });

        $('#generate-brand-chart').click(function () {
            brand = '0';
            mainDealerCode = '0';
            surveyType = $('#brand-survey').val();
            respondentType = $('#brand-respondent').val();

            requestChartData('brand-chart', 'total-brand', brand, mainDealerCode, surveyType, respondentType);
        });
        @endif

        $('#generate-main-dealer-chart').click(function () {
            brand = $('#main-dealer-brand').val();
            mainDealerCode = '0';
            surveyType = $('#main-dealer-survey').val();
            respondentType = $('#main-dealer-respondent').val();

            requestChartData('main-dealer-chart', 'total-main-dealer', brand, mainDealerCode, surveyType, respondentType);
        });
        
        @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
        $('#generate-district-chart').click(function () {
            brand = $('#district-brand').val();    
            mainDealerCode = $('#district-main-dealer').val();
            surveyType = $('#district-survey').val();
            respondentType = $('#district-respondent').val();

            requestChartData('district-chart', 'total-district', brand, mainDealerCode, surveyType, respondentType);
        });
        @endif
    });

    function requestChartData(elementId, chartType, brand, mainDealerCode, surveyType, respondentType) {
        sortedLabelsParam = '';
        if(chartType == 'total-brand' & window.sortedBrandLabels != null) {
            sortedLabelsParam = '&sortedLabels=' + JSON.stringify(window.sortedBrandLabels);
        }
        if(chartType == 'total-main-dealer' & window.sortedMainDealerLabels != null) {
            sortedLabelsParam = '&sortedLabels=' + JSON.stringify(window.sortedMainDealerLabels);
        }
        if(chartType == 'total-district' & window.sortedDistrictLabels != null) {
            sortedLabelsParam = '&sortedLabels=' + JSON.stringify(window.sortedDistrictLabels);
        }

        $.ajax({
            method: 'get',
            url: '{{ route('project.totalcsi.chart', ['uuid' => $project->uuid]) }}' 
                    + '?type=' + chartType + '&brand=' + brand + '&mainDealerCode=' + mainDealerCode 
                    + '&surveyType=' + surveyType + '&respondentType=' + respondentType + sortedLabelsParam,
            error: function (response) {
                alert(response.responseText);
            },
            success: function (response) {
                if(response.type == 'total-brand') {
                    window.sortedBrandLabels = response.label;
                }
                if(response.type == 'total-main-dealer') {
                    window.sortedMainDealerLabels = response.label;
                }
                if(response.type == 'total-district') {
                    window.sortedDistrictLabels = response.label;   
                }
                generateTotalChart(elementId, response.achievement, response.target, response.label, response.total_achievement);
            }
        });
    }

    function loadTotalBrands(elementId) {
        $.ajax({
            method: 'get',
            url: '{{ route('project.progress.brands', ['uuid' => $project->uuid]) }}',
            error: function (response) {
                
            },
            success: function (response) {
                htmlContent = '<option value="0">Select Brand</option>';
                for(i = 0; i < response.length; ++i) {
                    htmlContent += '<option value="' + response[i].brand + '">' 
                            + response[i].brand + '</option>';
                }
                
                $('#' + elementId).html(htmlContent);
            }
        });
    }

    function loadTotalMainDealers(elementId, brand) {
        $.ajax({
            method: 'get',
            url: '{{ route('project.progress.maindealers', ['uuid' => $project->uuid]) }}?brand=' + brand,
            error: function (response) {
                
            },
            success: function (response) {
                htmlContent = '<option value="0">Select Main Dealer</option>';
                for(i = 0; i < response.length; ++i) {
                    htmlContent += '<option value="' + response[i].main_dealer_code + '">' 
                            + response[i].main_dealer_code + ' - ' + response[i].main_dealer_name + '</option>';
                }
                $('#' + elementId).html(htmlContent);
            }
        });
    }

    function generateTotalChart(elementId, achievementData, targetData, labels, totalAchievement) {
        @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
        if(elementId == 'brand-chart') {
            window.brandChart.data.datasets.forEach(function (dataset, key) {
                if(key == 1) {
                    dataset.data = achievementData;
                }
                if(key == 0) {
                    dataset.data = targetData;
                }
            });
            window.brandChart.config.data.labels = labels;
            window.brandChart.update();

            $('#achievement-brand').html(totalAchievement);
        }
        @endif
        
        if(elementId == 'main-dealer-chart') {
            window.mainDealerChart.data.datasets.forEach(function (dataset, key) {
                if(key == 1) {
                    dataset.data = achievementData;
                }
                if(key == 0) {
                    dataset.data = targetData;
                }
            });
            window.mainDealerChart.config.data.labels = labels;
            window.mainDealerChart.update();

            $('#achievement-main-dealer').html(totalAchievement);
        }

        @if (Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)
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