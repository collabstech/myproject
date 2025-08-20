<div class="filter row text-left">
    <div class="col-8">
        <form action="{{ route('report.save.filter', ['report_id' => $report->uuid, 'project_id' => $project->uuid]) }}" method="POST">
            {{ csrf_field() }}
            <h4>Filter</h4>
            <table cellpadding="5" class="main-filter">
                @if (count($reportFilter) < 1)
                    <tr class="question-filter" index="1">
                        <td nowrap="nowrap">Question [1]</td>
                        <td>
                            <select name="filterQuestion[1]" id="filterQuestion[1]" class="select-filter-question">
                                <option value="">Choose...</option>
                                @if($questionFilteredList['filter'])
                                    @foreach($questionFilteredList['filter'] as $question)
                                        <option value="{{ $question->id }}">{{ $question->alias }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        <td>
                            <select name="filterAnswer[1][]" id="filterAnswer[1][]" class="select-filter-answer" question-id="1" multiple="multiple" style="width: 500;">
                            </select>
                        </td>
                        <td><a href="javascript:;" class="btn btn-danger btn-remove-filter"><i class="fa fa-close"></i> Remove</a></td>
                    </tr>
                @else
                    <?php $index = 0; ?>
                    @foreach ($reportFilter as $filter)
                    <?php $index++; ?>
                    @if (isset($report->user->role) && $report->user->role == \App\User::ROLE_ADMIN && Auth::user()->role != \App\User::ROLE_ADMIN && Auth::user()->id != $filter->user_id)
                        <input type="hidden" name="filterQuestion[{{ $index }}]" value="{{ $filter->question_id }}">
                    @endif
                    <input type="hidden" class="filter-user-id" name="filterUserId[{{ $index }}]" value="{{ $filter->user_id }}">
                    <tr class="question-filter" index="{{ $index }}">
                        <td nowrap="nowrap">Question [{{ $index }}]</td>
                        <td>
                            <select name="filterQuestion[{{ $index }}]" id="filterQuestion[{{ $index }}]" class="select-filter-question"
                                role-id="{{ isset($filter->user) ? $filter->user->role : 0 }}"
                            >
                                <option value="">Choose...</option>
                                @if($questionFilteredList['filter'])
                                    @foreach($questionFilteredList['filter'] as $question)
                                        <option value="{{ $question->id }}" {{ $filter->question_id == $question->id ? 'selected' : '' }}>{{ $question->alias }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        <td>
                            <select name="filterAnswer[{{ $index }}][]" id="filterAnswer[{{ $index }}][]" class="select-filter-answer" question-id="{{ $filter->question_id }}" multiple="multiple" style="width: 500;">
                                @if (count($filterAnswer[$filter->question_id]) > 0)
                                    @foreach ($filterAnswer[$filter->question_id] as $answer)
                                        @if($answer)
                                            <option value="{{ $answer }}" selected>{{ $answer }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        @if ((isset($filter->user->role) && $filter->user->role != \App\User::ROLE_ADMIN) || Auth::user()->role == \App\User::ROLE_ADMIN)
                            <td><a href="javascript:;" class="btn btn-danger btn-remove-filter"><i class="fa fa-close"></i> Remove</a></td>
                        @endif
                    </tr>
                    @endforeach
                @endif
                <tr>
                    <td>&nbsp;</td>
                    <td colspan="2">
                        <a href="javascript:;" class="btn btn-success btn-add-filter"><i class="fa fa-plus"></i> Add</a> 
                        <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Save Filter</button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <div class="col-4">
        @if ((isset($report->user->role) && $report->user->role != \App\User::ROLE_ADMIN) || Auth::user()->role == \App\User::ROLE_ADMIN)
        <form action="{{ route('report.save.summary', ['project_id' => $project->uuid]) }}" method="POST">
            {{ csrf_field() }}
            @if ($report)
                <input type="hidden" name="reportid" value="{{ $report->uuid }}">
            @endif
            <input type="hidden" name="projectid" value="{{ $project->uuid }}">        
            <div class="form-group">
                <label for="summary"><h4>Summary</h4></label>
                <textarea name="summary" id="summary" class="form-control">{{ $report->summary }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
        @endif
    </div>
</div>