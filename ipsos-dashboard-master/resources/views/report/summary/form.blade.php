<div class="panel panel-iris summary-table-form">
    <div class="panel-body text-left">
        <form action="{{ route('report.save.summary_table', ['report_id' => $report->uuid, 'project_id' => $project->uuid]) }}" method="POST">
            {{ csrf_field() }}
            <div class="summary-header">
                <h4>Summary Table</h4>
                <label><input type="radio" name="toggle_summary" id="summary_on" class="toggle_summary" value="1" {{ $report->show_summary == 1 ? 'checked="checked"' : '' }}> On</label>
                &nbsp;
                <label><input type="radio" name="toggle_summary" id="summary_off" class="toggle_summary" value="0" {{ $report->show_summary == 0 ? 'checked="checked"' : '' }}> Off</label>
            </div>
            <div class="summary-form">
                <select name="summary[]" class="select-multiple form-control {{ $errors->default->first('summary') ? 'is-invalid' : '' }}" multiple="multiple">
                    @if($questionFilteredList['summary'])
                        @foreach($questionFilteredList['summary'] as $question)
                            <option value="{{ $question->id }}" {{ array_key_exists($question->id, $summaryField) ? 'selected' : '' }}>{{ $question->alias }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <br>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>