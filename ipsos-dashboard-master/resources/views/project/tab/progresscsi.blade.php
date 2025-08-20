<div class="container">
    <div class="row">
        <div class="col-3"> 
            @if (Auth::user()->role != \App\User::ROLE_MAIN_DEALER)
            <h4>Brand</h4>
            <select name="brand" id="brand" class="select2" style="width: 200px;">
                <option value="0">Select Brand</option>
            </select>
            <br/>
            <h4>Main Dealer</h4>
            <select name="main-dealer" id="main-dealer" class="select2" style="width: 200px;">
                <option value="0">Select Main Dealer</option>
            </select>
            <br/>
            @endif
            <h4>District</h4>
            <select name="region" id="region" class="select2" style="width: 200px;">
                <option value="0">Select District</option>
            </select>
            <br/><br/>
            <button class="btn btn-primary" id="generate-progress-chart" style="width: 200px;">Generate Chart</button>

            @if (Auth::user()->role == \App\User::ROLE_ADMIN)
            <br/><br/><br/>

            <form id="form-progress" class="form-horizontal form-progress" action="{{ route('project.progresscsi.import') }}" method="POST" enctype="multipart/form-data">
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

            @endif
        </div>
        <div class="col-1"></div>
        <div class="col-8" style="margin-top: 20px;">
            <div class="row" style="margin-bottom: 20px;">
                <div class="col-6">
                    <table class="w-100 table table-striped">
                        <tbody>
                            <tr>
                                <td style="font-weight: bold; color: #000000">H1 Total Target</td>
                                <td id="h1-total"></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; color: #000000">H2 Total Target</td>
                                <td id="h2-total"></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; color: #000000">H3 Total Target</td>
                                <td id="h3-total"></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; color: #000000">Total Target</td>
                                <td id="total"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-6">
                    <table class="w-100 table table-striped">
                        <tbody>
                            <tr>
                                <td style="font-weight: bold; color: #000000">H1 Total Achievement</td>
                                <td id="h1-achievement"></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; color: #000000">H2 Total Achievement</td>
                                <td id="h2-achievement"></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; color: #000000">H3 Total Achievement</td>
                                <td id="h3-achievement"></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; color: #000000">Total Achievement</td>
                                <td id="total-achievement"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-3"><h4>PREMIUM</h4></div>
                <div class="col-9">
                    <canvas id="premium-progress-chart"></canvas>
                </div>
            </div>
            <div class="row">
                <div class="col-3"><h4>REGULAR</h4></div>
                <div class="col-9">
                    <canvas id="regular-progress-chart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <br/><br/>
</div>

<style>
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

@section('progress-js')
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.15/css/bootstrap-multiselect.css" type="text/css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.15/js/bootstrap-multiselect.js"></script> -->
<script type="text/javascript">
    $(document).ready(function() {
        loadBrands();

        var labels = ['H1', 'H2', 'H3'];

        var premiumData = {
                labels: labels,
                datasets: [{
                    label: "Target",
                    backgroundColor: '#EB5757',
                    data: [0, 0, 0],
                    datalabels: {
                        formatter: function(value, context) {
                            return value == 0 ? '0%' : '100%';
                        }
                    }
                },
                {
                    label: "Achievement",
                    backgroundColor: '#4990E2',
                    data: [0, 0, 0],
                    datalabels: {
                        formatter: function(value, context) {
                            targetVal = premiumData.datasets[0].data[context.dataIndex];
                            returnVal = 0;
                            if (targetVal != 0) {
                                returnVal = value / targetVal * 100
                            }
                            return Math.round(returnVal) + '%';
                        }
                    }
                }]
            };

        var regularData = {
                labels: labels,
                datasets: [{
                    label: "Target",
                    backgroundColor: '#EB5757',
                    data: [0, 0, 0],
                    datalabels: {
                        formatter: function(value, context) {
                            return value == 0 ? '0%' : '100%';
                        }
                    }
                },
                {
                    label: "Achievement",
                    backgroundColor: '#4990E2',
                    data: [0, 0, 0],
                    datalabels: {
                        formatter: function(value, context) {
                            targetVal = regularData.datasets[0].data[context.dataIndex];
                            returnVal = 0;
                            if (targetVal != 0) {
                                returnVal = value / targetVal * 100
                            }
                            return Math.round(returnVal) + '%';
                        }
                    }
                }]
            };
        
        var premiumOpt = {
                legend: {
                    display: false
                },
                layout: {
                    padding: {
                        top: 30,
                        bottom: 30
                    }
                },
                title: {
                    display: false
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                },
                plugins: {
                    datalabels: {
                        color: '#000000',
                        align: 'top',
						anchor: 'end'
                    }
                }
            };

        var regularOpt = {
                legend: {
                    display: false
                },
                layout: {
                    padding: {
                        top: 30,
                        bottom: 30
                    }
                },
                title: {
                    display: false
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                },
                plugins: {
                    datalabels: {
                        color: '#000000',
                        align: 'top',
						anchor: 'end'
                    }
                }
            };

        var premiumCtx = document.getElementById('premium-progress-chart').getContext('2d');
        window.premiumChart = new Chart(premiumCtx, {
            type: 'bar',
            data: premiumData,
            options: premiumOpt
        });

        var regularCtx = document.getElementById('regular-progress-chart').getContext('2d');
        window.regularChart = new Chart(regularCtx, {
            type: 'bar',
            data: regularData,
            options: regularOpt
        });
        
        @if (Auth::user()->role != \App\User::ROLE_MAIN_DEALER)
        $('#brand').change(function () {
            $('#main-dealer').html('<option value="0">Select Main Dealer</option>');
            $('#region').html('<option value="0">Select District</option>');
            loadMainDealers($('#brand').val());
        });
        
        $('#main-dealer').change(function () {
            $('#region').html('<option value="0">Select District</option>');
            loadRegions($('#brand').val(), $('#main-dealer').val());
        });
        @endif

        $('#generate-progress-chart').click(function () {
            brand = $('#brand').val();
            mainDealerCode = $('#main-dealer').val();
            region = $('#region').val();

            $.ajax({
                method: 'get',
                url: '{{ route('project.progress.chart', ['uuid' => $project->uuid]) }}' 
                        + '?brand=' + brand + '&mainDealerCode=' + mainDealerCode + '&region=' + region,
                error: function (response) {
                    
                },
                success: function (response) {
                    $('#h1-total').html(response.h1_total);
                    $('#h2-total').html(response.h2_total);
                    $('#h3-total').html(response.h3_total);
                    $('#total').html(response.total);

                    $('#h1-achievement').html(response.h1_achievement);
                    $('#h2-achievement').html(response.h2_achievement);
                    $('#h3-achievement').html(response.h3_achievement);
                    $('#total-achievement').html(response.total_achievement);

                    generateChart('premium-progress-chart', response.premium_achievement, response.premium_target);
                    generateChart('regular-progress-chart', response.regular_achievement, response.regular_target);
                }
            });
        });

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
                url: '{{ route('project.progresscsi.import') }}',
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
                    loadBrands();
                }
            });

            return false;
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

    function loadBrands() {
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
                
                $('#brand').html(htmlContent);
            }
        });
    }

    function loadMainDealers(brand) {
        $.ajax({
            method: 'get',
            url: '{{ route('project.progress.maindealers', ['uuid' => $project->uuid]) }}?brand=' + brand,
            error: function (response) {
                
            },
            success: function (response) {
                @if (Auth::user()->role != \App\User::ROLE_MAIN_DEALER)
                htmlContent = '<option value="0">Select Main Dealer</option>';
                @endif
                for(i = 0; i < response.length; ++i) {
                    htmlContent += '<option value="' + response[i].main_dealer_code + '">' 
                            + response[i].main_dealer_code + ' - ' + response[i].main_dealer_name + '</option>';
                }
                
                @if (Auth::user()->role != \App\User::ROLE_MAIN_DEALER)
                $('#main-dealer').html(htmlContent);
                @endif
            }
        });
    }

    function loadRegions(brand, mainDealerCode) {
        $.ajax({
            method: 'get',
            url: '{{ route('project.progress.regions', ['uuid' => $project->uuid]) }}?brand=' + brand + '&mainDealerCode=' + mainDealerCode,
            error: function (response) {
                
            },
            success: function (response) {
                htmlContent = '<option value="0">Select District</option>';
                for(i = 0; i < response.length; ++i) {
                    htmlContent += '<option value="' + response[i].district + '">' 
                            + response[i].district + '</option>';
                }
                
                $('#region').html(htmlContent);
            }
        });
    }

    function generateChart(elementId, achievementData, targetData) {
        if(elementId == 'premium-progress-chart') {
            window.premiumChart.data.datasets.forEach(function (dataset, key) {
                if(key == 1) {
                    dataset.data = achievementData;
                }
                if(key == 0) {
                    dataset.data = targetData;
                }
            });
            window.premiumChart.update();
        }

        if(elementId == 'regular-progress-chart') {
            window.regularChart.data.datasets.forEach(function (dataset, key) {
                if(key == 1) {
                    dataset.data = achievementData;
                }
                if(key == 0) {
                    dataset.data = targetData;
                }
            });
            window.regularChart.update();
        }
    }
</script>
@endsection