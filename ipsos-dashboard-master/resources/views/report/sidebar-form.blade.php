<form id="filter-form" method="post" action="{{ route('report.save', ['project_id' => $project->uuid]) }}">
    {{ csrf_field() }}
    @if ($report)
        <input type="hidden" name="reportid" value="{{ $report->uuid }}">
    @endif
    <input type="hidden" name="projectid" value="{{ $project->uuid }}">
    @if ($report)
        @if (isset($report->user->role))
        <div class="form-group">
            <label for="type">Dashboard List</label>
            <select name="type" id="type" class="select2 form-control report-admin {{ $errors->default->first('type') ? 'is-invalid' : '' }}"
                onchange="window.location.href='{{ route('report.generate', ['project_id' => $project->uuid]) }}/'+this.value"
            >
                @foreach($reportAdmin as $key => $value)
                    <option value="{{ $value->uuid }}" {{ $report && $report->uuid == $value->uuid ? 'selected' : '' }}>{{ $value->name }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback">
                {{ $errors->default->first('type') }}
            </div>
        </div>
        @endif
    @endif
    <div class="form-group">
        <label for="column">Top</label>
        <select name="top" id="column" class="select2 form-control {{ $errors->default->first('top') ? 'is-invalid' : '' }}">
            <option value="">Choose...</option>
            @if ($questionFilteredList['column'])
                @foreach($questionFilteredList['column'] as $question)
                    <option value="{{ $question->id }}" {{ ($report && $report->column == $question->id) || (old('side') == $question->id) ? 'selected' : '' }}>{{ $question->alias }}</option>
                @endforeach
            @endif
        </select>
        <div class="invalid-feedback">
            {{ $errors->default->first('top') }}
        </div>
    </div>
    <div class="form-group">
        <label for="row">Side</label>
        <select name="side" id="row" class="select2 form-control {{ $errors->default->first('side') ? 'is-invalid' : '' }}">
            <option value="">Choose...</option>
            @if($questionFilteredList['row'])
                @foreach($questionFilteredList['row'] as $question)
                    <option value="{{ $question->id }}" {{ ($report && $report->row == $question->id) || (old('side') == $question->id) ? 'selected' : '' }}>{{ $question->alias }}</option>
                @endforeach
            @endif
        </select>
        <div class="invalid-feedback">
            {{ $errors->default->first('side') }}
        </div>
    </div>
    <div class="form-group">
        <label for="value">Value</label>
        <select name="value" id="value" class="select2 form-control {{ $errors->default->first('value') ? 'is-invalid' : '' }}">
            <option value="">Choose...</option>
            @if($questionFilteredList['value'])
                @foreach($questionFilteredList['value'] as $question)
                    <option value="{{ $question->id }}" {{ ($report && $report->data == $question->id) || (old('value') == $question->id) ? 'selected' : '' }}>{{ $question->alias }}</option>
                @endforeach
            @endif
        </select>
        <div class="invalid-feedback">
            {{ $errors->default->first('value') }}
        </div>
    </div>
    <div class="form-group">
        <label for="">Operation</label>
        <div class="btn-group btn-group-toggle {{ $errors->default->first('operation') ? 'is-invalid' : '' }}" data-toggle="buttons">
            @foreach($reportOperation as $key => $operation)
            <label class="btn btn-outline-primary {{ $report && $report->operation == $key || old('operation') == $key ? 'active' : '' }}">
                <input type="radio" name="operation" id="operation-{{$operation}}" autocomplete="off" value="{{ $key }}" 
                    {{ $report && $report->operation == $key ? 'checked' : '' }}
                > 
                {{ $operation }}
            </label>
            @endforeach
        </div>
    </div>
    <div class="invalid-feedback">
        {{ $errors->default->first('operation') }}
    </div>
    <br>
    <div class="form-group">
        <label for="type">Type</label>
        <select name="type" id="type" class="form-control {{ $errors->default->first('type') ? 'is-invalid' : '' }}">
            <option value="">Choose...</option>
            @foreach($reportType as $key => $type)
                <option value="{{ $key }}" {{ ($report && $report->type == $key) || (old('type') == $key) ? 'selected' : '' }}>{{ $type }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback">
            {{ $errors->default->first('type') }}
        </div>
    </div>
    <div class="form-group">
        <label for="reportname">Name</label>
        <input name="reportname" id="reportname" class="form-control {{ $errors->default->first('reportname') ? 'is-invalid' : '' }}" rows="3" placeHolder="Report name" value="{{ $report ? $report->name : old('reportname') }}">
        <div class="invalid-feedback">
            {{ $errors->default->first('reportname') }}
        </div>
    </div>
    <div class="barlineform" style="
    @if ((isset($report) && $report->type != \App\Report::TYPE_BAR_LINE) || !isset($report))
    display:none
    @endif
    ">
        <hr>
        <div class="form-group">
            <label for="row_combo2">Side</label>
            <select name="side_combo2" id="row_combo2" class="select2 form-control {{ $errors->default->first('side_combo2') ? 'is-invalid' : '' }}">
                <option value="">Choose...</option>
                @if($questionFilteredList['row'])
                    @foreach($questionFilteredList['row'] as $question)
                        <option value="{{ $question->id }}" {{ $report && $report->row_combo2 == $question->id ? 'selected' : '' }}>{{ $question->alias }}</option>
                    @endforeach
                @endif
            </select>
            <div class="invalid-feedback">
                {{ $errors->default->first('side_combo2') }}
            </div>
        </div>
        <div class="form-group">
            <label for="data_combo2">Value</label>
            <select name="data_combo2" id="data_combo2" class="select2 form-control {{ $errors->default->first('data_combo2') ? 'is-invalid' : '' }}">
                <option value="">Choose...</option>
                @if($questionFilteredList['value'])
                    @foreach($questionFilteredList['value'] as $question)
                        <option value="{{ $question->id }}" {{ $report && $report->data_combo2 == $question->id ? 'selected' : '' }}>{{ $question->alias }}</option>
                    @endforeach
                @endif
            </select>
            <div class="invalid-feedback">
                {{ $errors->default->first('data_combo2') }}
            </div>
        </div>
        <div class="form-group">
            <label for="">Operation</label>
            <div class="btn-group btn-group-toggle {{ $errors->default->first('operation_combo2') ? 'is-invalid' : '' }}" data-toggle="buttons">
                @foreach($reportOperation as $key => $operation)
                <label class="btn btn-outline-primary {{ $report && $report->operation_combo2 == $key ? 'active' : '' }}">
                    <input type="radio" name="operation_combo2" id="operation_combo2-{{$operation}}" autocomplete="off" value="{{ $key }}" 
                        {{ $report && $report->operation_combo2 == $key ? 'checked' : '' }}
                    > 
                    {{ $operation }}
                </label>
                @endforeach
            </div>
        </div>
        <div class="invalid-feedback">
            {{ $errors->default->first('operation_combo2') }}
        </div>
    </div>
    <hr>
    <div class="text-center">
        <ul class="nav">
            <li>
                <button type="submit" class="btn btn-primary btn-generate">{{ $report ? 'Update' : 'Generate' }}</button>
                <a href="javascript:;" style="position: fixed; top: 200px;right:0px;" class="sidebar-minify-btn" data-click="sidebar-minify"><i class="fa fa-angle-double-left"></i></a>
            </li>
        </ul>
    </div>
</form>