<div class="panel panel-iris panel-report summary-table">
    <div class="panel-body">
        <table id="pivotTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    @foreach ($summaryField as $value)
                        <th class="text-center">{{ isset($questionArray[$value]) ? $questionArray[$value]->alias : '' }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($resultSummary as $row)
                    <tr>
                        @foreach ($summaryField as $field)
                            <td>
                                @if (!empty($row[$questionArray[$field]->alias]))
                                    @if (count($row[$questionArray[$field]->alias]) > 1)
                                        @foreach ($row[$questionArray[$field]->alias] as $answerColumn)
                                            {{ is_numeric($answerColumn) && $row[$questionArray[$field]]->code != \App\Report::Q_NOT_INTEGER ? number_format($answerColumn, 0, ',', '.') : $answerColumn }}
                                            <br>
                                        @endforeach                                
                                    @else
                                        {{ is_numeric($row[$questionArray[$field]->alias][1]) && $questionArray[$field]->code != \App\Report::Q_NOT_INTEGER ? number_format($row[$questionArray[$field]->alias][1], 0, ',', '.') : $row[$questionArray[$field]->alias][1] }}
                                    @endif
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach 
            </tbody>
        </table>
    </div>
</div>