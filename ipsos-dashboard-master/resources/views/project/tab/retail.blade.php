<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="alert-message alert" id="info-upload-progress" role="alert" style="display: none;"></div>
        </div>
    </div>
    @if (Auth::user()->role == \App\User::ROLE_ADMIN)
    <form id="form-progress" class="form-horizontal form-progress" action="{{ route('project.progresscsi.import') }}" method="POST" enctype="multipart/form-data">
        {{ csrf_field() }}
        <input type="file" id="file-import-progress" name="import_progress" class="file-hidden project-file" />
        <div class="row">
            <div class="col-2">
                <div class="thumbnail-dashed thumbnail-progress"><i class="fa fa-file"></i></div>
            </div>
            <button class="btn btn-success" id="btn-import-progress">Choose File</button>&nbsp;&nbsp;
            <button type="submit" class="btn btn-primary" id="upload">Upload</button>
            <div class="col-2">
                <div class="progress-save"></div>
            </div>
        </div>
    </form>
    <br/>
    @endif
    @if ($project->type != \App\Project::BLACKHOLE_TYPE)
    <div class="row">
        <div class="col-10">
            <div class="row">
                <div class="col-2">
                    <select name="achievement-province[]" id="achievement-province" multiple="multiple" style="width: 100%;">
                    </select>
                </div>
                <div class="col-2"> 
                    <select name="achievement-kabupaten" id="achievement-kabupaten" multiple="multiple" style="width: 100%;">
                    </select>
                </div>
                <div class="col-2"> 
                    <select name="achievement-kecamatan" id="achievement-kecamatan" class="select2" style="width: 100%;">
                        <option value="0">Select Kecamatan</option>
                    </select>
                </div>
                <div class="col-2"> 
                    <select name="achievement-kelurahan" id="achievement-kelurahan" class="select2" style="width: 100%;">
                        <option value="0">Select Kelurahan</option>
                    </select>
                </div>
                <div class="col-2"> 
                    <select name="achievement-retail-segment" id="achievement-retail-segment" multiple="multiple" style="width: 100%;">
                    </select>
                </div>
                <div class="col-2"> 
                    <select name="achievement-brand" id="achievement-brand" multiple="multiple" style="width: 100%;">
                    </select>
                </div>
            </div>
        </div>
        <div class="col-2"> 
            <button class="btn btn-primary" id="achievement-generate-chart" style="width: 100%;">Generate Chart</button>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-12">
        @if ($project->uuid != '8bb65670-a2db-11e9-9c9b-89d054d94e2f')
        <table id="store-stats">
            <tr>
                <td id="total-toko-title" style="background-color: #1f497d; color: #ffffff">Total Toko</td>
                <td id="total-toko" style="background-color: #f3f3f3; text-align: right;"></td>
                <td id="total-toko-space">&nbsp;&nbsp;</td>
            </tr>
        </table>
        @else
        <table id="table-information" width="100%">
            <tr>
                <th></th>
                <th colspan="2">Total</th>
                <th colspan="2">DKI Jakarta</th>
                <th colspan="2">Banten</th>
                <th colspan="2">Jawa Barat</th>
            </tr>
            <tr>
                <td>Total Visit</td>
                <td>13930</td>
                <td>100%</td>
                <td>1485</td>
                <td>100%</td>
                <td>2010</td>
                <td>100%</td>
                <td>10435</td>
                <td>100%</td>
            </tr>
            <tr>
                <td>Sukses Interview</td>
                <td>9214</td>
                <td>66%</td>
                <td>792</td>
                <td>53%</td>
                <td>1607</td>
                <td>80%</td>
                <td>6815</td>
                <td>65%</td>
            </tr>
            <tr>
                <td>Menolak</td>
                <td>1427</td>
                <td>10%</td>
                <td>643</td>
                <td>43%</td>
                <td>284</td>
                <td>14%</td>
                <td>500</td>
                <td>5%</td>
            </tr>
            <tr>
                <td>Bukan Toko Semen & Modern Trade Chain</td>
                <td>3289</td>
                <td>24%</td>
                <td>50</td>
                <td>3%</td>
                <td>119</td>
                <td>6%</td>
                <td>3120</td>
                <td>30%</td>
            </tr>
        </table>
        @endif
        </div>
    </div>
    <div class="row">
        <div class="col-4">
            <br/>
            <h4 id="chart-title1" style="width: 100%; text-align: center;">Retail Segment Distribution</h4>
            <canvas id="retail-segment-chart"></canvas>
        </div>
        <div class="col-4">
            <br/>
            <h4 id="chart-title2" style="width: 100%; text-align: center;">Cement Brand Distribution</h4>
            <canvas id="brand-chart"></canvas>
        </div>
        <div class="col-4">
            <br/>
            <h4 id="chart-title3" style="width: 100%; text-align: center;">Business Turn Over Per Month (Ton)</h4>
            <table id="month-table"></table>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-4">
            @if ($project->type != \App\Project::BLACKHOLE_TYPE)
            <select name="progress-province" id="progress-province" multiple="multiple" style="width: 100%;">
            </select>
            @else
            <select name="progress-province" id="progress-province" class="select2" style="width: 100%;">
                <option value="0">Select Province</option>
            </select>
            @endif
        </div>
        <div class="col-2"> 
            <button class="btn btn-primary" id="progress-generate-chart" style="width: 100%;">Generate Chart</button>
        </div>
    </div>
    <div class="row">
        <div class="col-7">
            <br/>
            <h4 id="chart-title4" style="width: 100%; text-align: center;">Number of Retail Visited (Accumulative)</h4>
            <canvas id="retail-visited-chart"></canvas>
        </div>
        <div class="col-5">
            <br/>
            <h4 id="chart-title5" style="width: 100%; text-align: center;">Progress at Kelurahan Level</h4>
            <canvas id="progress-kelurahan-chart"></canvas>
        </div>
    </div>
</div>

<style>
.progress-save {
    display: none;
    border: 8px solid #f3f3f3; /* Light grey */
    border-top: 8px solid #3498db; /* Blue */
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 2s linear infinite;
    text-align: center;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

#month-table {
    margin-top: 40px;
    margin-left: 5%;
    margin-right: 5%;
    width: 90%;
}

#month-table>tbody>tr>td {
    padding-top: 3px;
    padding-bottom: 3px;
    padding-left: 5px;
    padding-right: 5px;
    border: 2px solid #ffffff;
}

#store-stats>tbody>tr>td {
    padding-top: 5px;
    padding-bottom: 5px;
    padding-left: 10px;
    padding-right: 10px;
    border: 2px solid #ffffff;
    font-weight: 500;
    font-size: 13px;
}

#table-information>tbody>tr>td {
    padding-top: 5px;
    padding-bottom: 5px;
    padding-left: 5px;
    padding-right: 5px;
    border: 2px solid #ffffff;
    background-color: #eeeeee;
}

#table-information>tbody>tr>th {
    padding-top: 7px;
    padding-bottom: 7px;
    padding-left: 5px;
    padding-right: 5px;
    border: 2px solid #ffffff;
    background-color: #1F497D;
    color: #ffffff;
    text-align: center;
}
</style>

@section('progress-js')
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.5.0"></script>
<script type="text/javascript">
$(document).ready(function() {
    window.retailSegmentSort = [];
    window.brandSort = [];
    window.sumOfRetailSegment = 0;
    window.sumOfBrand = 0;

    loadProgressProvince();
    @if ($project->type != \App\Project::BLACKHOLE_TYPE)
    loadAchievementProvince();
    loadSegment();
    loadBrand();
    @if ($project->uuid != '8bb65670-a2db-11e9-9c9b-89d054d94e2f')
    loadRetailInformation();
    @endif
    @endif

    $('#achievement-province').multiselect({
        includeSelectAllOption: true,
        enableFiltering: true,
        buttonWidth: '100%'
    });
    $('#achievement-kabupaten').multiselect({
        includeSelectAllOption: true,
        enableFiltering: true,
        buttonWidth: '100%'
    });
    $('#achievement-retail-segment').multiselect({
        includeSelectAllOption: true,
        enableFiltering: true,
        buttonWidth: '100%'
    });
    $('#achievement-brand').multiselect({
        includeSelectAllOption: true,
        enableFiltering: true,
        buttonWidth: '100%'
    });
    @if ($project->type != \App\Project::BLACKHOLE_TYPE)
    $('#progress-province').multiselect({
        includeSelectAllOption: true,
        enableFiltering: true,
        buttonWidth: '100%'
    });
    @endif

    var retailVisitedData = {
            labels: [],
            datasets: [{
                label: "Visited",
                backgroundColor: '#99b433',
                data: [],
                datalabels: {
                    formatter: function(value, context) {
                        return value;
                    }
                }
            }]
        };

    var retailVisitedOpt = {
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
                        size: 10
                    }
                }
            },
        };

    var progressKelurahanData = {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [],
                datalabels: {
                    formatter: function(value, context) {
                        return context.chart.data.labels[context.dataIndex] + ': ' + value;
                    }
                }
            }]
        };

    var progressKelurahanOpt = {
            legend: {
                display: false
            },
            layout: {
                padding: {
                    top: 30,
                    bottom: 30,
                    left: 50,
                    right: 50
                }
            },
            title: {
                display: false
            },
            plugins: {
                datalabels: {
                    color: '#000000',
                    align: 'end',
                    anchor: 'end',
                    font: {
                        size: 10
                    }
                }
            },
            responsive: true,
            aspectRatio: 1.5
        };

    @if ($project->type != \App\Project::BLACKHOLE_TYPE)
    var retailSegmentData = {
            labels: [],
            datasets: [{
                label: "Achievement",
                backgroundColor: [],
                data: [],
                datalabels: {
                    formatter: function(value, context) {
                        return (value / window.sumOfRetailSegment * 100).toFixed(2) + '%';
                    }
                }
            }]
        };

    var retailSegmentOpt = {
            legend: {
                display: false
            },
            layout: {
                padding: {
                    top: 30,
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
                        autoSkip: false,
                        beginAtZero: true
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
                        size: 10
                    }
                }
            },
            aspectRatio: 0.5
        };

    var brandData = {
            labels: [],
            datasets: [{
                label: "Achievement",
                backgroundColor: [],
                data: [],
                datalabels: {
                    formatter: function(value, context) {
                        return (window.sumOfBrand == 0 ? 0 : (value / window.sumOfBrand * 100).toFixed(2)) + '%';;
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
                    right: 50,
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
                        autoSkip: false,
                        beginAtZero: true
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
                        size: 10
                    }
                }
            },
            aspectRatio: 0.5
        };
    @endif

    var retailVisitedCtx = document.getElementById('retail-visited-chart').getContext('2d');
    window.retailVisitedChart = new Chart(retailVisitedCtx, {
        type: 'bar',
        data: retailVisitedData,
        options: retailVisitedOpt
    });

    var progressKelurahanCtx = document.getElementById('progress-kelurahan-chart').getContext('2d');
    window.progressKelurahanChart = new Chart(progressKelurahanCtx, {
        type: 'pie',
        data: progressKelurahanData,
        options: progressKelurahanOpt
    });

    @if ($project->type != \App\Project::BLACKHOLE_TYPE)
    var retailSegmentCtx = document.getElementById('retail-segment-chart').getContext('2d');
    window.retailSegmentChart = new Chart(retailSegmentCtx, {
        type: 'horizontalBar',
        data: retailSegmentData,
        options: retailSegmentOpt
    });
    window.retailSegmentChart.canvas.parentNode.style.height = '750px';

    var brandCtx = document.getElementById('brand-chart').getContext('2d');
    window.brandChart = new Chart(brandCtx, {
        type: 'horizontalBar',
        data: brandData,
        options: brandOpt
    });
    window.brandChart.canvas.parentNode.style.height = '750px';
    @endif

    $('#progress-generate-chart').click(function() {
        progressProvs = [];
        @if ($project->type != \App\Project::BLACKHOLE_TYPE)
        $('#progress-province > option:selected').each(function() {
            progressProvs.push($(this).val());
        });
        @else
        provinceId = $('#progress-province').val();
        if (!(provinceId == 0 || provinceId == '0')) {
            progressProvs.push(provinceId);
        }
        @endif
        provinces = JSON.stringify(progressProvs);
        
        $.ajax({
            method: 'get',
            url: '{{ route('project.retail.retailvisited.chart', ['uuid' => $project->uuid]) }}' 
                    + '?provinces=' + provinces,
            error: function (response) {
                
            },
            success: function (response) {
                generateRetailVisitedChart(response.visited, response.label);
            }
        });

        $.ajax({
            method: 'get',
            url: '{{ route('project.retail.progresskelurahan.chart', ['uuid' => $project->uuid]) }}' 
                    + '?provinces=' + provinces,
            error: function (response) {
                
            },
            success: function (response) {
                generateKelurahanProgressChart(response.progress, response.label, response.color);
            }
        });
    });

    @if ($project->type != \App\Project::BLACKHOLE_TYPE)
    $('#achievement-province').change(function () {
        $('#achievement-kabupaten').html('');
        $('#achievement-kabupaten').multiselect('rebuild');
        $('#achievement-kecamatan').html('<option value="0">Select Kecamatan</option>');
        $('#achievement-kelurahan').html('<option value="0">Select Kelurahan</option>');

        achievementProvs = [];
        $('#achievement-province > option:selected').each(function() {
            achievementProvs.push($(this).val());
        });

        loadAchievementKabupaten(JSON.stringify(achievementProvs));
    });

    $('#achievement-kabupaten').change(function () {
        $('#achievement-kecamatan').html('<option value="0">Select Kecamatan</option>');
        $('#achievement-kelurahan').html('<option value="0">Select Kelurahan</option>');

        achievementProvs = [];
        $('#achievement-province > option:selected').each(function() {
            achievementProvs.push($(this).val());
        });

        achievementKabs = [];
        $('#achievement-kabupaten > option:selected').each(function() {
            achievementKabs.push($(this).val());
        });

        loadAchievementKecamatan(JSON.stringify(achievementProvs), JSON.stringify(achievementKabs));
    });

    $('#achievement-kecamatan').change(function () {
        $('#achievement-kelurahan').html('<option value="0">Select Kelurahan</option>');

        achievementProvs = [];
        $('#achievement-province > option:selected').each(function() {
            achievementProvs.push($(this).val());
        });

        achievementKabs = [];
        $('#achievement-kabupaten > option:selected').each(function() {
            achievementKabs.push($(this).val());
        });

        loadAchievementKelurahan(JSON.stringify(achievementProvs), JSON.stringify(achievementKabs), 
            $('#achievement-kecamatan').val());
    });

    $('#achievement-generate-chart').click(function() {
        achievementProvs = [];
        $('#achievement-province > option:selected').each(function() {
            achievementProvs.push($(this).val());
        });

        achievementKabs = [];
        $('#achievement-kabupaten > option:selected').each(function() {
            achievementKabs.push($(this).val());
        });

        achievementRetailSegments = [];
        $('#achievement-retail-segment > option:selected').each(function() {
            achievementRetailSegments.push($(this).val());
        });

        achievementRetailBrands = [];
        $('#achievement-brand > option:selected').each(function() {
            achievementRetailBrands.push($(this).val());
        });

        provinces = JSON.stringify(achievementProvs);
        kabupatens = JSON.stringify(achievementKabs);
        kecamatan = $('#achievement-kecamatan').val();
        kelurahan = $('#achievement-kelurahan').val();
        retailSegments = JSON.stringify(achievementRetailSegments);
        retailBrands = JSON.stringify(achievementRetailBrands);

        rsSort = '';
        if (window.retailSegmentSort != '') {
            rsSort = JSON.stringify(window.retailSegmentSort);
        }

        bSort = '';
        if (window.brandSort != '') {
            bSort = JSON.stringify(window.brandSort);
        }
        
        $.ajax({
            method: 'get',
            url: '{{ route('project.retail.retailsegment.chart', ['uuid' => $project->uuid]) }}' 
                    + '?provinces=' + provinces + '&kabupatens=' + kabupatens + '&kecamatan=' + kecamatan
                    + '&kelurahan=' + kelurahan + '&retailSegments=' + retailSegments 
                    + '&brands=' + retailBrands + '&sort=' + rsSort,
            error: function (response) {
                
            },
            success: function (response) {
                window.retailSegmentSort = response.sort;
                window.sumOfRetailSegment = 0;
                for (i = 0; i < response.achievement.length; ++i) {
                    window.sumOfRetailSegment += response.achievement[i];
                }
                generateRetailSegmentChart(response.achievement, response.label, response.color);
            }
        });

        $.ajax({
            method: 'get',
            url: '{{ route('project.retail.brand.chart', ['uuid' => $project->uuid]) }}' 
                    + '?provinces=' + provinces + '&kabupatens=' + kabupatens + '&kecamatan=' + kecamatan
                    + '&kelurahan=' + kelurahan + '&retailSegments=' + retailSegments 
                    + '&brands=' + retailBrands + '&sort=' + bSort,
            error: function (response) {
                
            },
            success: function (response) {
                window.brandSort = response.sort;
                window.sumOfBrand = response.total_data;

                $('#total-toko').html(window.sumOfBrand);

                generateBrandChart(response.achievement, response.label, response.color);
                generateMonthTable(response.month_sales, response.label_ms);
            }
        });
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
        formData.append('project_id', {!! $project->id !!});
        formData.append('_token', $('[name="_token"]').val());

        var progress = 0;

        $.ajax({
            method: 'post',
            url: '{{ route('project.retail.import') }}',
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
                loadProgressProvince();
                @if ($project->uuid != '8bb65670-a2db-11e9-9c9b-89d054d94e2f')
                loadRetailInformation();
                @endif
            }
        });

        return false;
    });
    @endif
});

function loadProgressProvince() {
    $.ajax({
        method: 'get',
        url: '{{ route('project.retail.progress.province', ['uuid' => $project->uuid]) }}',
        error: function (response) {
            
        },
        success: function (response) {
            @if ($project->type != \App\Project::BLACKHOLE_TYPE)
            htmlContent = '';
            @else
            htmlContent = '<option value="0">Select Province</option>';
            @endif
            for(i = 0; i < response.length; ++i) {
                htmlContent += '<option value="' + response[i].province + '">' 
                        + response[i].province + '</option>';
            }
            $('#progress-province').html(htmlContent);
            @if ($project->type != \App\Project::BLACKHOLE_TYPE)
            $('#progress-province').multiselect('rebuild');
            changeMultiSelectIcon();
            @endif
        }
    });
}

function generateRetailVisitedChart(visited, label) {
    window.retailVisitedChart.data.datasets.forEach(function (dataset, key) {
        dataset.data = visited;
    });
    window.retailVisitedChart.config.data.labels = label;
    window.retailVisitedChart.update();
}

function generateKelurahanProgressChart(progress, label, color) {
    window.progressKelurahanChart.data.datasets.forEach(function (dataset, key) {
        dataset.data = progress;
        dataset.backgroundColor = color;
    });
    window.progressKelurahanChart.config.data.labels = label;
    window.progressKelurahanChart.update();
}

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

@if ($project->type != \App\Project::BLACKHOLE_TYPE)
function loadAchievementProvince() {
    $.ajax({
        method: 'get',
        url: '{{ route('project.retail.achievement.province', ['uuid' => $project->uuid]) }}',
        error: function (response) {
            
        },
        success: function (response) {
            htmlContent = '';
            for(i = 0; i < response.length; ++i) {
                htmlContent += '<option value="' + response[i].province + '">' 
                        + response[i].province + '</option>';
            }
            $('#achievement-province').html(htmlContent);
            $('#achievement-province').multiselect('rebuild');
            changeMultiSelectIcon();
        }
    });
}

function loadAchievementKabupaten(provinces) {
    $.ajax({
        method: 'get',
        url: '{{ route('project.retail.achievement.kabupaten', ['uuid' => $project->uuid]) }}?provinces=' + provinces,
        error: function (response) {
            
        },
        success: function (response) {
            htmlContent = '';
            for(i = 0; i < response.length; ++i) {
                htmlContent += '<option value="' + response[i].kabupaten + '">' 
                        + response[i].kabupaten + '</option>';
            }
            $('#achievement-kabupaten').html(htmlContent);
            $('#achievement-kabupaten').multiselect('rebuild');
            changeMultiSelectIcon();
        }
    });
}

function loadAchievementKecamatan(provinces, kabupatens) {
    $.ajax({
        method: 'get',
        url: '{{ route('project.retail.achievement.kecamatan', ['uuid' => $project->uuid]) }}?provinces=' + provinces 
                + '&kabupatens=' + kabupatens,
        error: function (response) {
            
        },
        success: function (response) {
            htmlContent = '<option value="0">Select Kecamatan</option>';
            for(i = 0; i < response.length; ++i) {
                htmlContent += '<option value="' + response[i].kecamatan + '">' 
                        + response[i].kecamatan + '</option>';
            }
            $('#achievement-kecamatan').html(htmlContent);
        }
    });
}

function loadAchievementKelurahan(provinces, kabupatens, kecamatan) {
    $.ajax({
        method: 'get',
        url: '{{ route('project.retail.achievement.kelurahan', ['uuid' => $project->uuid]) }}?provinces=' + provinces 
                + '&kabupatens=' + kabupatens + '&kecamatan=' + kecamatan,
        error: function (response) {
            
        },
        success: function (response) {
            htmlContent = '<option value="0">Select Kelurahan</option>';
            for(i = 0; i < response.length; ++i) {
                htmlContent += '<option value="' + response[i].kelurahan + '">' 
                        + response[i].kelurahan + '</option>';
            }
            $('#achievement-kelurahan').html(htmlContent);
        }
    });
}

function loadSegment() {
    $.ajax({
        method: 'get',
        url: '{{ route('project.retail.segment', ['uuid' => $project->uuid]) }}',
        error: function (response) {
            
        },
        success: function (response) {
            htmlContent = '';
            for(i = 0; i < response.length; ++i) {
                htmlContent += '<option value="' + response[i].id + '">' 
                        + response[i].name + '</option>';
            }
            $('#achievement-retail-segment').html(htmlContent);
            $('#achievement-retail-segment').multiselect('rebuild');
            changeMultiSelectIcon();
        }
    });
}

function loadBrand() {
    $.ajax({
        method: 'get',
        url: '{{ route('project.retail.brand', ['uuid' => $project->uuid]) }}',
        error: function (response) {
            
        },
        success: function (response) {
            htmlContent = '';
            for(i = 0; i < response.length; ++i) {
                htmlContent += '<option value="' + response[i].id + '">' 
                        + response[i].name + '</option>';
            }
            $('#achievement-brand').html(htmlContent);
            $('#achievement-brand').multiselect('rebuild');
            changeMultiSelectIcon();
        }
    });
}

function loadRetailInformation() {
    $.ajax({
        method: 'get',
        url: '{{ route('project.retail.information', ['uuid' => $project->uuid]) }}',
        error: function (response) {
            
        },
        success: function (response) {
            @if ($project->type != \App\Project::BLACKHOLE_TYPE)

            htmlContent = '';
            for (i = 0; i < response.store_stat_titles.length; ++i) {
                if (response.store_stat_titles[i] != '' && response.store_stat_titles[i] != null) {
                    if (i == 0) {
                        $('#total-toko-title').html(response.store_stat_titles[i]);
                        continue;
                    }
                    htmlContent += '<td class="additional-stat" style="background-color: #1f497d; color: #ffffff">' + response.store_stat_titles[i] 
                    + '</td><td class="additional-stat" style="background-color: #f3f3f3; text-align: right;">' + response.store_stat_values[i]
                    + '</td><td class="additional-stat">&nbsp;&nbsp;</td>';
                }
            }
            $('.additional-stat').remove();
            $('#total-toko-space').after(htmlContent);

            $('#chart-title1').html(response.chart_title1);
            $('#chart-title2').html(response.chart_title2);
            $('#chart-title3').html(response.chart_title3);
            @endif
            $('#chart-title4').html(response.chart_title4);
            $('#chart-title5').html(response.chart_title5);
        }
    });
}

function generateRetailSegmentChart(achievement, label, color) {
    window.retailSegmentChart.data.datasets.forEach(function (dataset, key) {
        dataset.data = achievement;
        dataset.backgroundColor = color;
    });
    window.retailSegmentChart.config.data.labels = label;
    window.retailSegmentChart.update();
}

function generateBrandChart(achievement, label, color) {
    window.brandChart.data.datasets.forEach(function (dataset, key) {
        dataset.data = achievement;
        dataset.backgroundColor = color;
    });
    window.brandChart.config.data.labels = label;
    window.brandChart.update();
}

function generateMonthTable(achievement, label) {
    content = '';
    for (i = 0; i < achievement.length; ++i) {
        content += '<tr><td style="background-color: #a9a9a9; color: #ffffff">' + label[i] 
                + '</td><td style="background-color: #f3f3f3; text-align: right;">' 
                + formatNumber(achievement[i].toFixed(2)) + '</td></tr>';
    }
    $('#month-table').html(content);
}

function formatNumber(num) {
    return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
}
@endif
</script>
@endsection