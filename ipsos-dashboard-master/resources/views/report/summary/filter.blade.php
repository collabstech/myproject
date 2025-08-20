<div class="filter row text-left filter-summary">
    <div class="col-8">
        <form action="{{ route('report.save.filter.summary', ['report_id' => $report->uuid, 'project_id' => $project->uuid]) }}" method="POST">
            {{ csrf_field() }}
            <h4>Filter</h4>
            <table cellpadding="5" class="main-filter-summary">
                @if (count($reportFilterSummary) < 1)
                    <tr class="question-filter-summary" index="1">
                        <td nowrap="nowrap">QuestionSummary [1]</td>
                        <td>
                            <select name="filterQuestionSummary[1]" id="filterQuestionSummary[1]" class="select-filter-question-summary">
                                <option value="">Choose...</option>
                                @if($questionFilteredList['filter'])
                                    @foreach($questionFilteredList['filter'] as $question)
                                        <option value="{{ $question->id }}">{{ $question->alias }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        <td>
                            <select name="filterAnswerSummary[1][]" id="filterAnswerSummary[1][]" class="select-filter-answer-summary" question-id="1" multiple="multiple" style="width: 500;">
                            </select>
                        </td>
                        <td><a href="javascript:;" class="btn btn-danger btn-remove-filter-summary"><i class="fa fa-close"></i> Remove</a></td>
                    </tr>
                @else
                    <?php $index = 0; ?>
                    @foreach ($reportFilterSummary as $filter)
                    <?php $index++; ?>
                    @if (isset($report->user->role) && $report->user->role == \App\User::ROLE_ADMIN && Auth::user()->role != \App\User::ROLE_ADMIN && Auth::user()->id != $filter->user_id)                        <input type="hidden" name="filterQuestionSummary[{{ $index }}]" value="{{ $filter->question_id }}">
                    @endif
                    <input type="hidden" class="filter-user-id" name="filterUserId[{{ $index }}]" value="{{ $filter->user_id }}">
                    <tr class="question-filter-summary" index="{{ $index }}">
                        <td nowrap="nowrap">QuestionSummary [{{ $index }}]</td>
                        <td>
                            <select name="filterQuestionSummary[{{ $index }}]" id="filterQuestionSummary[{{ $index }}]" class="select-filter-question-summary"
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
                            <select name="filterAnswerSummary[{{ $index }}][]" id="filterAnswerSummary[{{ $index }}][]" class="select-filter-answer-summary" question-id="{{ $filter->question_id }}" multiple="multiple" style="width: 500;">
                                @if (count($filterAnswerSummary[$filter->question_id]) > 0)
                                    @foreach ($filterAnswerSummary[$filter->question_id] as $answer)
                                        @if($answer)
                                            <option value="{{ $answer }}" selected>{{ $answer }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        @if ((isset($filter->user->role) && $filter->user->role != \App\User::ROLE_ADMIN) || Auth::user()->role == \App\User::ROLE_ADMIN)
                            <td><a href="javascript:;" class="btn btn-danger btn-remove-filter-summary"><i class="fa fa-close"></i> Remove</a></td>
                        @endif
                    </tr>
                    @endforeach
                @endif
                <tr>
                    <td>&nbsp;</td>
                    <td colspan="2">
                        <a href="javascript:;" class="btn btn-success btn-add-filter-summary"><i class="fa fa-plus"></i> Add</a> 
                        <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Save Filter</button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>