<div class="containerChart mx-auto">
    <canvas id="generatedChart"></canvas>
    @if ($result)
        <div class="total-data">
            Total data: {{ count($result) }}
        </div>
    @endif
</div>

@section('generatejs')
    <script type="text/javascript">
        $(document).ready(function () {
            @if ($report && $report->type != \App\Report::TYPE_TABLE)
                var canvas = document.getElementById("generatedChart");
                generatechart(canvas);
                exportPdf();
                exportImage();
                exportPpt();
            @endif
        });

        function generatechart(canvas) {
            var ctx = canvas.getContext('2d');
            var backgroundColor = 'white';
            var plugin = {
                @if ($report->type == \App\Report::TYPE_BAR || $report->type == \App\Report::TYPE_BAR_LINE)
                afterDatasetsDraw: function(chart) {
                    var ctx = chart.ctx;

                    chart.data.datasets.forEach(function(dataset, i) {
                        var meta = chart.getDatasetMeta(i);
                        if (!meta.hidden) {
                            meta.data.forEach(function(element, index) {
                                // Draw the text in black, with the specified font
                                ctx.fillStyle = 'rgb(0, 0, 0)';

                                var fontSize = 12;
                                var fontStyle = 'normal';
                                var fontFamily = 'Montserrat';
                                ctx.font = Chart.helpers.fontString(fontSize, fontStyle, fontFamily);

                                // Just naively convert to string for now
                                // var dataString = dataset.data[index].toString();
                                @if($report->showvalues == \App\Report::SHOW_PERCENTAGE)
                                    var value = parseFloat(dataset.data[index]) / 100;
                                    var dataString = numeral(value).format('0.00%');
                                @else
                                    var dataString = dataset.data[index];
                                @endif

                                // Make sure alignment settings are correct
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle';

                                var padding = 5;
                                var position = element.tooltipPosition();
                                ctx.fillText(dataString, position.x, position.y - (fontSize / 2) - padding);
                            });
                        }
                    });
                }
                @endif
            };

            Chart.plugins.register(plugin);

            @if ($report)
                @switch ($report->type)
                    @case (\App\Report::TYPE_BAR)
                    @case (\App\Report::TYPE_BAR_LINE)
                        var myChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: [
                                    @if ($generatedValue)
                                        @foreach($generatedValue['column'] as $column)
                                            @if ($report->type == \App\Report::TYPE_BAR 
                                                && $report->columnQuestion->code == \App\Report::SORT_BY_DATE
                                                && strtotime($column) !== false
                                                )
                                                "{{ date('d', strtotime($column)) }}",
                                            @else
                                                @switch ($report->type)
                                                    @case (\App\Report::TYPE_BAR_LINE)
                                                        @foreach($generatedValue['row'] as $row)
                                                            "{{ $column.' - '.$row }}",
                                                        @endforeach
                                                    @break
                                                    @case (\App\Report::TYPE_BAR)
                                                        "{{ $column}}",
                                                    @break
                                                @endswitch
                                            @endif
                                        @endforeach
                                    @endif
                                ],
                                datasets: [
                                @if ($generatedValue)
                                    @if ($report->type == App\Report::TYPE_BAR_LINE)
                                        {
                                            yAxisID: 'y-axis-right',
                                            type: 'line',
                                            label: $('#data_combo2 option:selected').text(),
                                            borderColor: '#FF5F00',
                                            backgroundColor: '#FF5F00',
                                            borderWidth: 8,
                                            showLine: false,
                                            fill: false,
                                            data: [
                                                @foreach($generatedValue['column'] as $column)
                                                    @foreach($generatedValue['row'] as $row2)
                                                        @if (isset($resultRow2[$column][$row2]))
                                                            @if($report->showvalues == \App\Report::SHOW_PERCENTAGE)
                                                                {{ isset($resultRowPercentOfColumn2[$column][$row2][$report->operation_combo2])?$resultRowPercentOfColumn2[$column][$row2][$report->operation_combo2]:0 }},
                                                            @else
                                                                {{ isset($resultRow2[$column][$row2][$report->operation_combo2])?$resultRow2[$column][$row2][$report->operation_combo2]:"0" }},
                                                            @endif
                                                        @else
                                                        0,
                                                        @endif
                                                    @endforeach
                                                @endforeach
                                            ],
                                        },
                                        {
                                            yAxisID: 'y-axis-left',
                                            label: $('#value option:selected').text(),
                                            backgroundColor: dynamicColors()[0],
                                            borderWidth: 1,
                                            data: [
                                                @foreach($generatedValue['column'] as $column)
                                                    @foreach($generatedValue['row'] as $key => $row)
                                                        @if (isset($resultValue[$row][$column]))
                                                            @if($report->showvalues == \App\Report::SHOW_PERCENTAGE)
                                                                {{ isset($resultPercentOfColumn[$row][$column][$report->operation])?$resultPercentOfColumn[$row][$column][$report->operation]:0 }},
                                                            @else
                                                                {{ isset($resultValue[$row][$column][$report->operation])?$resultValue[$row][$column][$report->operation]:0 }},
                                                            @endif
                                                        @else
                                                        0,
                                                        @endif
                                                    @endforeach
                                                @endforeach
                                            ],
                                        },
                                    @else
                                        @foreach($generatedValue['row'] as $key => $row)
                                            {
                                                yAxisID: 'y-axis-left',
                                                label: '{{ $row }}',
                                                backgroundColor: dynamicColors()[{{ $key }}],
                                                borderWidth: 1,
                                                data: [
                                                    @foreach($generatedValue['column'] as $column)
                                                        @if (isset($resultValue[$row][$column]))
                                                            @if($report->showvalues == \App\Report::SHOW_PERCENTAGE)
                                                                {{ isset($resultPercentOfColumn[$row][$column][$report->operation])?$resultPercentOfColumn[$row][$column][$report->operation]:0 }},
                                                            @else
                                                                {{ isset($resultValue[$row][$column][$report->operation])?$resultValue[$row][$column][$report->operation]:0 }},
                                                            @endif
                                                        @else
                                                        0,
                                                        @endif
                                                    @endforeach
                                                ],
                                            },
                                        @endforeach
                                    @endif
                                @endif
                                ]
                            },
                            options: {
                                hover: {
                                    mode: 'nearest',
                                    intersect: true,
                                },
                                onClick: function(ctx, interaction) {
                                    var element = interaction[0];
                                    if (element == undefined) {
                                        $('.summary-table').hide();
                                        return;
                                    }

                                    var datasetLabel = this.data.datasets[element._datasetIndex].label;
                                    var label = this.data.labels[element._index];
                                    var dataset = this.data.datasets[element._datasetIndex].data[element._index];
                                    @if ($report->type == \App\Report::TYPE_BAR 
                                                && $report->columnQuestion->code == \App\Report::SORT_BY_DATE
                                                && strtotime($column) !== false
                                                )
                                        label = "{{ date('Y-m', strtotime($column)) }}-" + label +" {{ date('H:i:s', strtotime($column)) }}";

                                    @endif
                                    var data = {
                                        top: label,
                                        side: datasetLabel,
                                        value: dataset
                                    };

                                    $('.summary-table').show();
                                    loadSummaryData(data);
                                },
                                annotation: {
                                    annotations: [{
                                        type: 'line',
                                        mode: 'horizontal',
                                        scaleID: 'y-axis-left',
                                        value: {{ (double) $report->trendline }},
                                        endValue: {{ (double) $report->trendline }},
                                        borderColor: 'rgb(75, 192, 192)',
                                        borderWidth: 2,
                                        borderDash: [2, 2],
                                    }]
                                },
                                title: {
                                    display: true,
                                    fontSize: 24,
                                    text: @if ($report->type == \App\Report::TYPE_BAR 
                                        && $report->columnQuestion->code == \App\Report::SORT_BY_DATE
                                        && strtotime($column) !== false
                                        )
                                        "{{ $report->name.' ('.date('M', strtotime($column)).') ' }}",
                                    @else
                                        '{{ $report->name }}'
                                    @endif
                                },
                                responsive: true,
                                maintainAspectRatio: false,
                                tooltips: {
                                    mode: 'nearest',
                                    intersect: true,
                                    callbacks: {
                                        // Use the footer callback to display the sum of the items showing in the tooltip
                                        label: function(tooltipItem, data) {
                                            var label = data.datasets[tooltipItem.datasetIndex].label || '';

                                            if (label) {
                                                label += ': ';
                                            }
                                            // label += Math.round(tooltipItem.yLabel * 100) / 100;

                                            label += tooltipItem.yLabel;
                                            @if($report->showvalues == \App\Report::SHOW_PERCENTAGE)
                                                label += '%';
                                            @endif
                                            
                                            return label;
                                        }
                                    },
                                },
                                legend: {
                                    position: 'bottom',
                                },
                                scales: {
                                    yAxes: [{
                                        id: 'y-axis-left',
                                        position: 'left',
                                        gridLines: {
                                            display: false,
                                        },
                                        scaleLabel: {
                                            display: true,
                                            fontSize: 14,
                                            fontStyle: 'bold',
                                            labelString: '{{ ucwords(str_replace('_', ' ', $report->dataQuestion->alias)) }}'
                                        },
                                        ticks: {
                                            callback: function(value, index, values) {
                                                var label = value;
                                                
                                                @if($report->showvalues == \App\Report::SHOW_PERCENTAGE)
                                                    label += '%';
                                                @endif
                                                
                                                return label;
                                            }
                                        }
                                    },
                                    @if ($report->type == \App\Report::TYPE_BAR_LINE)
                                    {
                                        id: 'y-axis-right',
                                        position: 'right',
                                        gridLines: {
                                            display: false,
                                        },
                                        scaleLabel: {
                                            display: true,
                                            fontSize: 14,
                                            fontStyle: 'bold',
                                            labelString: '{{ ucwords(str_replace('_', ' ', $report->dataQuestion2->alias)) }}'
                                        },
                                        ticks: {
                                            beginAtZero: true,
                                            min: 0,
                                            // Include a dollar sign in the ticks
                                            callback: function(value, index, values) {
                                                var label = value;
                                                
                                                @if($report->showvalues == \App\Report::SHOW_PERCENTAGE)
                                                    label += '%';
                                                @endif
                                                
                                                return label;
                                            }
                                        }
                                    }
                                    @endif
                                    ],
                                    xAxes: [{
                                        gridLines: {
                                            display: false,
                                        },
                                        scaleLabel: {
                                            display: true,
                                            fontSize: 14,
                                            fontStyle: 'bold',
                                            labelString: '{{ ucwords(str_replace('_', ' ', $report->columnQuestion->alias)) }}'
                                        },
                                        ticks: {
                                            beginAtZero: true,
                                            min: 0,
                                            autoSkip: false,
                                        }
                                    }]
                                },
                            },
                        });
                    @break
                    @case (\App\Report::TYPE_PIE)
                        var colors = [];
                        var chartData = [
                            @if ($generatedValue)
                                @foreach($generatedValue['column'] as $column)
                                    {{ !empty($resultColumn[$column][$report->operation])?$resultColumn[$column][$report->operation]:"0" }},
                                @endforeach
                            @endif
                        ];
                        @if ($generatedValue)
                            @foreach($generatedValue['column'] as $item)
                                colors = dynamicColors();
                            @endforeach
                        @endif
                        var myChart = new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: [
                                    @if ($generatedValue)
                                        @foreach($generatedValue['column'] as $column)
                                            "{{ $column }}",
                                        @endforeach
                                    @endif
                                ],
                                datasets: [{
                                    data: chartData,
                                    backgroundColor: colors,
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                title: {
                                    display: true,
                                    text: '{{ $report->name }}'
                                },
                                responsive: true,
                                maintainAspectRatio: false,
                                legend: {
                                    position: 'bottom',
                                },
                                tooltips: {
                                    enabled: true,
                                    mode: 'index',
                                    intersect: true,
                                    callbacks: {
                                        label: function(tooltipItem, data) {
                                            var label = data.labels[tooltipItem.index] || '';

                                            if (label) {
                                                label += ': ';
                                            }
                                            label += data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                                            // label += numeral(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]).format('0,0');
                                            return label;   
                                        }
                                    },
                                },
                                pieceLabel: { mode: {{ \App\Report::SHOW_PERCENTAGE }}, fontColor: 'white', precision: 2 },
                            }
                        });
                    @break
                    @case (\App\Report::TYPE_LINE)
                        var myChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: [
                                    @if ($generatedValue)
                                        @foreach($generatedValue['column'] as $column)
                                            "{{ $column }}",
                                        @endforeach
                                    @endif
                                ],
                                datasets: [
                                @if ($generatedValue)
                                    @foreach($generatedValue['row'] as $key => $row)
                                        {
                                            label: '{{ $row }}',
                                            data: [
                                                @foreach($generatedValue['column'] as $column)
                                                    @if (isset($resultValue[$row][$column]))
                                                        @if($report->showvalues == \App\Report::SHOW_PERCENTAGE)
                                                            {{ !empty($resultPercentOfColumn[$row][$column][$report->operation])?$resultPercentOfColumn[$row][$column][$report->operation]:0 }},
                                                        @else
                                                            {{ !empty($resultValue[$row][$column][$report->operation])?$resultValue[$row][$column][$report->operation]:0 }},
                                                        @endif
                                                    @else
                                                    0,
                                                    @endif
                                                @endforeach
                                            ],
                                            backgroundColor: dynamicColors()[{{ $key }}],
                                            borderColor: dynamicColors()[{{ $key }}],
                                            borderWidth: 3,
                                            fill: false,
                                        },
                                    @endforeach
                                @endif
                                ]
                            },
                            options: {
                                title: {
                                    display: true,
                                    fontSize: 24,
                                    text: '{{ $report->name }}'
                                },
                                responsive: true,
                                maintainAspectRatio: false,
                                tooltips: {
                                    mode: 'index',
					                intersect: false,
                                    callbacks: {
                                        // Use the footer callback to display the sum of the items showing in the tooltip
                                        label: function(tooltipItem, data) {
                                            var label = data.datasets[tooltipItem.datasetIndex].label || '';

                                            if (label) {
                                                label += ': ';
                                            }
                                            // label += Math.round(tooltipItem.yLabel * 100) / 100;    
                                            // label += numeral(tooltipItem.yLabel).format('0,0.00');
                                            label += tooltipItem.yLabel;
                                            @if($report->showvalues == \App\Report::SHOW_PERCENTAGE)
                                                label += '%';
                                            @else
                                            @endif
                                            
                                            return label;
                                        }
                                    },
                                },
                                legend: {
                                    position: 'bottom',
                                },
                                scales: {
                                    yAxes: [{
                                        gridLines: {
                                            display: false,
                                        },
                                        scaleLabel: {
                                            display: true,
                                            fontSize: 14,
                                            fontStyle: 'bold',
                                            labelString: '{{ ucwords(str_replace('_', ' ', $report->dataQuestion->alias)) }}'
                                        },
                                        ticks: {
                                            // Include a dollar sign in the ticks
                                            callback: function(value, index, values) {
                                                var label = value;
                                                
                                                @if($report->showvalues == \App\Report::SHOW_PERCENTAGE)
                                                    label += '%';
                                                @endif
                                                
                                                return label;
                                            }
                                        }
                                    }],
                                    xAxes: [{
                                        gridLines: {
                                            display: false,
                                        },
                                        scaleLabel: {
                                            display: true,
                                            fontSize: 14,
                                            fontStyle: 'bold',
                                            labelString: '{{ ucwords(str_replace('_', ' ', $report->columnQuestion->alias)) }}'
                                        },
                                        ticks: {
                                            autoSkip: false
                                        }
                                    }]
                                }
                            },
                        });
                    @break
                @endswitch
            @endif
        }

        function exportPdf() {
            $(document).on('click', '.btn-pdf', function () {
                $('#formValues,#formTrendline').hide();
                html2canvas(document.querySelector(".panel-report>.panel-body")).then(canvas => {
                    // var canvas = document.getElementById("generatedChart");
                    var img = canvas.toDataURL("image/png");
                    var pdf = new jsPDF({
                        orientation: 'landscape',
                    });

                    pdf.addImage(img, 'png', 0, 0);
                    pdf.save("{{ date('dmY_His').'-'.str_slug($report->name) }}.pdf");
                });
                $('#formValues,#formTrendline').show();
            });
        }

        function exportImage() {
            $(document).on('click', '.btn-image', function () {
                $('#formValues,#formTrendline').hide();
                html2canvas(document.querySelector(".panel-report>.panel-body")).then(canvas => {
                    // var canvas = document.getElementById("generatedChart");
                    var img = canvas.toDataURL("image/png");
                    canvas.toBlob(function(blob) {
                        saveAs(blob, "{{ date('dmY_His').'-'.str_slug($report->name) }}.png");
                    });
                });
                $('#formValues,#formTrendline').show();
            });
        }

        function exportPpt() {
            trimDynamicColorHex();
            $(document).on('click', '.btn-ppt', function () {
                $('#formValues,#formTrendline').hide();
                @if ($report)
                    var pptx = new PptxGenJS();
                    pptx.setLayout('LAYOUT_WIDE');
                    var slide = pptx.addNewSlide();

                    var dataChart = {!! isset($jsonValue['default']) ? $jsonValue['default'] : 0 !!};
                    var multiOpts = {
                        w:12, h:6,
                        showLegend  : true,
                        legendPos: 'b',
                        legendFontSize: 12,
                        showTitle: true,
                        title: '{{ $report->name }}',
                        titleFontSize: 24,
                        showCatAxisTitle: true,
                        catAxisTitle: '{{ ucwords(str_replace('_', ' ', $report->columnQuestion->alias)) }}',
                        catAxisTitleFontSize: 14,
                        showValAxisTitle: true,
                        valAxisTitle: '{{ ucwords(str_replace('_', ' ', $report->dataQuestion->alias)) }}',
                        valAxisTitleFontSize: 14,
                        lineSmooth: true,
                        chartColors: trimDynamicColorHex(),
                    };
                    @switch ($report->type)
                        @case (\App\Report::TYPE_BAR)
                            @if($report->showvalues == \App\Report::SHOW_PERCENTAGE)
                                multiOpts.valAxisLabelFormatCode = '#.00%';
                                multiOpts.dataLabelFormatCode = '#.00%';
                            @else
                                multiOpts.valAxisLabelFormatCode = '#,###.00';
                                multiOpts.dataLabelFormatCode = '#,###.00';
                            @endif
                            multiOpts.showValue = true;

                            slide.addChart( pptx.charts.BAR, dataChart, multiOpts);
                            @break;
                        @case (\App\Report::TYPE_BAR_LINE)
                        var multiOpts = {
                            x:1.0, y:1.0, w:12, h:6,
                            showLegend: false,
                            valAxisMinVal: 0,
                            showTitle: true,
                            title: '{{ $report->name }}',
                            chartColors: trimDynamicColorHex(),
                            valAxes:[
                                {
                                    showValAxisTitle: true,
                                    valAxisTitle: 'Primary Value Axis'
                                },
                                {
                                    showValAxisTitle: true,
                                    valAxisTitle: 'Secondary Value Axis',
                                    valAxisMajorUnit: 1,
                                    valAxisMaxVal: 10,
                                    valAxisMinVal: 1,
                                    valGridLine: 'none'
                                }
                            ],
                            catAxes: [{ catAxisTitle: 'Primary Category Axis' }, { catAxisHidden: true }]
                        };  
                            @if($report->showvalues == \App\Report::SHOW_PERCENTAGE)                        
                                multiOpts.valAxisLabelFormatCode = '#.00%';
                                multiOpts.dataLabelFormatCode = '#.00%';
                            @else
                                multiOpts.valAxisLabelFormatCode = '#,###';
                                multiOpts.dataLabelFormatCode = '#,###';
                            @endif
                            multiOpts.showValue = true;
                            multiOpts.showLegend = false,
                            multiOpts.valAxes = [
                                  {
                                    showValAxisTitle: true,
                                    valAxisTitle: $('#value option:selected').text(),
                                    valAxisTitleFontSize: 14,
                                  },
                                  {
                                    showValAxisTitle: true,
                                    valAxisTitle: $('#data_combo2 option:selected').text(),
                                    valAxisMinVal: 0,
                                    valGridLine: 'none',
                                    valAxisTitleFontSize: 14,
                                  }
                            ];

                            var dataChart2 = {!! isset($jsonValue['default2']) ? $jsonValue['default2'] : 0 !!};

                            var chartTypes = [
                              {
                                type: pptx.charts.BAR,
                                data: dataChart,
                                options: {
                                    barDir: 'col',
                                    chartColors: ['5DA5DA'],
                                }
                              },
                              {
                                type: pptx.charts.LINE,
                                data: dataChart2,
                                options: {
                                  // NOTE: both are required, when using a secondary axis:
                                  secondaryValAxis: true,
                                  secondaryCatAxis: true,
                                  chartColors: ['FF5F00'],
                                  lineSize: 0,
                                }
                              }
                            ];

                            slide.addChart(chartTypes, multiOpts);
                            @break;
                        @case (\App\Report::TYPE_PIE)
                            multiOpts.dataLabelFormatCode = '#.00%';
                            var dataChart = {!! $jsonValue['pie'] !!};

                            slide.addChart( pptx.charts.PIE, dataChart, multiOpts);
                            @break;
                        @case (\App\Report::TYPE_LINE)
                        @if($report->showvalues == \App\Report::SHOW_PERCENTAGE)                        
                                multiOpts.valAxisLabelFormatCode = '#.00%';
                                multiOpts.dataLabelFormatCode = '#.00%';
                            @else
                                multiOpts.valAxisLabelFormatCode = '#,###';
                                multiOpts.dataLabelFormatCode = '#,###';
                            @endif

                            slide.addChart( pptx.charts.LINE, dataChart, multiOpts);
                            @break;
                    @endswitch
                    slide.addText('Total data: {{ count($result) }}', {
                        y: '95%',
                        autoFit: true,
                    });

                    pptx.save('{{ date('dmY_His').'-'.str_slug($report->name) }}');
                @endif
                $('#formValues,#formTrendline').show();
            });
        }

        function dynamicColors() {
            return [
                '#5DA5DA', '#FAA43A', '#60BD68', '#F17CB0', '#B2912F', '#B276B2', '#DECF3F', '#F15854', '#A7A7A7', '#5DA5DA', '#FAA43A', '#60BD68', '#F17CB0', '#B2912F', '#B276B2', '#DECF3F', '#F15854', '#A7A7A7',
                '#A682FF','#A3BDFF','#E8DDB5','#B2C9AB','#666A86','#FF1654','#F3FFBD','#B96D40','#3B1C32','#E5EAFA','#BF4E30','#3B3355','#5A2A27','#FFA5AB','#A53860','#F5F8DE','#FFD447','#320D6D', '#13C4A3',

            ];
        }

        function trimDynamicColorHex() {
            var color = dynamicColors();
            var trimColor = [];
            for (let index = 0; index < color.length; index++) {
                const element = color[index];
                trimColor[index] = color[index].replace('#', '');
            }

            return trimColor;
        }

        function loadSummaryData(data) {
            $('.summary-table').hide();
            var checked = $('.toggle_summary:checked').val();
            if (checked == 1 || {{ $report->show_summary }} == 1) {
                $('.summary-table').show();
                $.ajax({
                    method: 'get',
                    url: '{{ route('report.summary', ['project_id' => $project->uuid, 'report_id' => $report->uuid]) }}',
                    data: data,
                    error: () => {
                        $('.summary-table').html('<div class="alert alert-danger" role="alert">Failed to fetch data, please try again!</div>');
                    },
                    beforeSend: () => {
                        $('.summary-table').html('<div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div></div>');
                    },
                    success: (response, textStatus, xhr) => {
                        if (xhr.status == 200) {
                            $('.summary-table').html(response);
                        }
                    }
                });
            }
        }

        function isFloat(n){
            return Number(n) === n && n % 1 !== 0;
        }
    </script>
@endsection