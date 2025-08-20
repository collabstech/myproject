<div class="generate-table">
    <table id="pivotTable" class="table table-striped table-bordered">
        <thead>
            <tr>
                @if ($generatedValue)
                <th style="text-align:center" colspan="{{ count($generatedValue['column']) + 1 + 1 }}">{{ $report->name }}</th>
                @endif
            </tr>
            <tr>
                <th style="text-align:center">{{ ucwords(str_replace('_', ' ', $report->rowQuestion->alias)) }}</th>
                @if ($resultRow)
                    <th style="text-align:center">Total</th>
                @endif
                @if ($generatedValue)
                    @foreach ($generatedValue['column'] as $item)
                        <th style="text-align:center">{{ $item }}</th>
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody>
            @if ($generatedValue)
                @foreach($generatedValue['row'] as $row)
                    <tr>
                        <td>{{ $row }}</td>
                        @if (isset($resultRow) && isset($resultRow[$row]))
                            <td>
                                @if($report->showvalues == \App\Report::SHOW_PERCENTAGE)
                                    {{ $resultRowPercentOfColumn[$row][$report->operation] }}%
                                @else
                                    {{ $resultRow[$row][$report->operation] }}
                                @endif
                            </td>
                        @else
                            <td>&nbsp;</td>
                        @endif
                        @foreach($generatedValue['column'] as $column)
                            <td>
                            @if (isset($resultValue[$row][$column]))
                                @if($report->showvalues == \App\Report::SHOW_PERCENTAGE)
                                    {{ $resultPercentOfColumn[$row][$column][$report->operation] }}%
                                @else
                                    {{ $resultValue[$row][$column][$report->operation] }}
                                @endif
                            @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            @endif
        </tbody>
        @if ($resultColumn)
        <tfoot>
            <tr>
                <th style="text-align:center">Total</th>
                <th style="text-align:center">
                    @if($report->showvalues == \App\Report::SHOW_PERCENTAGE)
                        100%
                    @else
                        @if ($report->operation == \App\Report::OPERATION_COUNT)
                            {{ $respondTotal[$report->operation] }}
                        @else
                            {{ $resultTotal[$report->operation] }}
                        @endif
                    @endif
                </th>
                @if ($generatedValue)
                    @foreach ($generatedValue['column'] as $column)
                        @if(isset($resultColumn[$column]))
                        <th style="text-align:center">
                            @if($report->showvalues == \App\Report::SHOW_PERCENTAGE)
                                100%
                            @else
                                @if ($report->operation == \App\Report::OPERATION_COUNT)
                                    {{ $respondColumn[$column][$report->operation] }}
                                @else
                                    {{ $resultColumn[$column][$report->operation] }}
                                @endif
                            @endif
                        </th>
                        @else
                            <th>&nbsp;</th>
                        @endif
                    @endforeach
                @endif
            </tr>
        </tfoot>
        @endif
    </table>
</div>
@section('generatejs')
<script type="text/javascript">
    $(document).ready(function () {
        exportExcel();
        exportPdf();
        exportImage();
    });
    function exportExcel() {
        $(document).on('click', '.btn-excel', function () {
            window.location.href = '{{ route('report.excel', ['project_id' => $project->uuid, 'report_id' => $report->uuid]) }}?export=excel&showValues={{ $report->showvalues }}';
        });
    }

    function exportImage() {
        $(document).on('click', '.btn-image', function () {
            $('.generate-table').css('overflow', 'visible');
            html2canvas(document.querySelector("#pivotTable")).then(canvas => {
                $('.generate-canvas').html(canvas);
                var img = canvas.toDataURL("image/png");
                canvas.toBlob(function(blob) {
                    saveAs(blob, "{{ date('dmY_His').'-'.str_slug($report->name) }}.png");
                });
            });
            $('.generate-table').css('overflow', 'scroll');
        });
    }

    function exportPdf() {
        $(document).on('click', '.btn-pdf', function () {
            $('.generate-table').css('overflow', 'visible');
            html2canvas(document.querySelector("#pivotTable")).then(canvas => {
                $('.generate-canvas').html(canvas);

                var svg = $('.generate-table').find('svg.main-svg:first');
                var img = canvas.toDataURL();
                var pdf = new jsPDF({
                    orientation: 'landscape',
                });

                pdf.addImage(img, 'png', 0, 0);
                pdf.save("{{ date('dmY_His').'-'.str_slug($report->name) }}.pdf");
            });
            $('.generate-table').css('overflow', 'scroll');
        });
    }
</script>
@endsection