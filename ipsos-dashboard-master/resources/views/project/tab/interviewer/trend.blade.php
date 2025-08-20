<div class="row">
    <div class="col-3">
        <h4>Main Dealer </h4>
        <select name="main-dealer-trend-interviewer" id="main-dealer-trend-interviewer" class="select2"
                style="width: 200px;">
            <option value="0">
                Select Main Dealer
            </option>
        </select>

        <h4>ID Interviewer </h4>
        <select name="id-interviewer-trend-interviewer" id="id-interviewer-trend-interviewer" class="select2"
                style="width: 200px;">
            <option value="0">
                Select ID Interviewer
            </option>
        </select>
        <br/><br/>
        <div class="mb-4">
            <div class="input-group input-daterange">
                <input type="text" id="filter-trend-date-from" class="form-control" placeholder="Start Date">
                <div class="input-group-addon">to</div>
                <input type="text" id="filter-trend-date-to" class="form-control" placeholder="End Date">
            </div>
        </div>
        <button class="btn btn-primary" id="generate-trend-interviewer-chart" style="width: 200px;">Generate Chart</button>
    </div>
    <div class="col-1"></div>
    <div class="col-8" id="interview-chart-section">
        <h4 style="text-align: center;">Trend Interviewer</h4>
        <div id="trend-interviewer-chart-body">
            <canvas id="trend-interviewer-chart"></canvas>
        </div>
    </div>
</div>

@section('trend-interviewer-js')
    <script type="text/javascript">
        let defaultTrendInterviewChartWidth = 500;
        $(document).ready(function () {
            $('.input-daterange input').each(function () {
                $(this).datepicker({
                    autoclose: true,
                    format: 'yyyy-mm-dd',
                    orientation: 'bottom',
                });
            });

            const trendInterviewerData = {
                labels: [],
                datasets: [{
                    label: "Achievement",
                    backgroundColor: '#4990E2',
                    data: [],
                    datalabels: {
                        formatter: function (value, context) {
                            return value;
                        }
                    }
                }]
            };

            const trendInterviewerOpt = {
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
                            display: false
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            beginAtZero: true,
                            autoSkip: false,
                            stepSize: 5
                        },
                        gridLines: {
                            display: false
                        }
                    }]
                },
                plugins: {
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        formatter: Math.round,
                        font: {
                            weight: 'bold'
                        }
                    }
                }
            };

            var interviewerCtx = document.getElementById('trend-interviewer-chart').getContext('2d');
            window.trendInterviewerChart = new Chart(interviewerCtx, {
                type: 'line',
                data: trendInterviewerData,
                options: trendInterviewerOpt
            });
            window.trendInterviewerChart.canvas.parentNode.style.height = '500px';
            window.trendInterviewerChart.canvas.parentNode.style.width = defaultTrendInterviewChartWidth + 'px';

            $('#generate-trend-interviewer-chart').click(function () {
                let mainDealerCode = $('#main-dealer-trend-interviewer').val();

                if (mainDealerCode == 0) {
                    alert('You have to select Main Dealer');
                    return;
                }
                let idInterviewer = $('#id-interviewer-trend-interviewer').val();

                if (idInterviewer == 0) {
                    alert('You have to select Interviewer');
                    return;
                }

                const filterDateFrom = $('#filter-trend-date-from').val();
                const filterDateTo = $('#filter-trend-date-to').val();

                if (filterDateFrom !== '' && filterDateTo !== '') {
                    const filterDateFromDate = new Date(filterDateFrom);
                    const filterDateToDate = new Date(filterDateTo);
                    if (filterDateFromDate > filterDateToDate) {
                        alert('Filter date from must be less than filter date to');
                        return false;
                    }
                }
                let url = '{{ route('project.interviewer.chart.by-id', ['uuid' => $project->uuid]) }}?mainDealerCode=' + mainDealerCode + '&idInterviewer=' + idInterviewer

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
                        generateChartTrendInterviewer(response.achievement, response.dates);
                        $('.interviewer-count').text(response.labels.length);
                        $('.threshold-count').text(response.threshold);
                    }
                });
            });

            $('#main-dealer-trend-interviewer').change(function() {
                loadIDInterviewer();
            });
        });

        function loadIDInterviewer() {
            let mainDealerCode = $('#main-dealer-trend-interviewer').val();
            $.ajax({
                method: 'get',
                url: '{{ route('project.interviewer.ids', ['uuid' => $project->uuid]) }}?mainDealerCode=' + mainDealerCode,
                success: function (response) {
                    htmlContent = '<option value="0">Select ID Interviewer</option>';
                    for (i = 0; i < response.length; ++i) {
                        htmlContent += '<option value="' + response[i].interviewer_id + '">'
                            + response[i].interviewer_id + '</option>';
                    }

                    $('#id-interviewer-trend-interviewer').html(htmlContent);
                }
            });
        }

        function generateChartTrendInterviewer(achievementData, labels) {
            window.trendInterviewerChart.data.datasets.forEach(function (dataset, key) {
                dataset.data = achievementData;
                dataset.fill = false;
                dataset.borderColor = 'rgb(75, 192, 192)';
                dataset.lineTension = 0;
            });
            let newWidth = (labels.length * 30);

            window.trendInterviewerChart.config.data.labels = labels;
            window.trendInterviewerChart.canvas.parentNode.style.width = newWidth + 'px';

            if (newWidth < defaultTrendInterviewChartWidth) {
                window.trendInterviewerChart.canvas.parentNode.style.width = defaultTrendInterviewChartWidth + 'px';
            }

            window.trendInterviewerChart.update();
            window.trendInterviewerChart.resize();
        }
    </script>
@endsection