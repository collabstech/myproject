@extends('layout.master')
@section('page-title', 'Generate Report')
@section('breadcrumb')
    <li><a href="{{ url('/') }}">Home</a></li>
    <li>Project</li>
    <li>Report</li>
    <li class="active">Generate Report</li>
@endsection

@section('sidebar')
    @include('report.sidebar-form')
@endsection

@section('content')
    <div class="text-center">
        <div class="button-menu">
            <div class="pull-left">
                <a href="{{ url('/') }}" class="btn btn-primary"><i class="fa fa-chevron-left"></i> Back</a>
                <a href="{{ route('report.generate', ['project_id' => $project->uuid]) }}" class="btn btn-success"><i class="fa fa-plus"></i> Generate new report</a>
            </div>
            <div class="pull-right">
                @if ($report)
                <div class="btn-group" role="group">
                    <button id="buttonExport" type="button" class="btn btn-danger dropdown-toggle btn-export" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-file"></i> Export To
                    </button>
                    <div class="dropdown-menu" aria-labelledby="buttonExport">
                        @switch ($report->type)
                            @case (\App\Report::TYPE_TABLE)
                                <a href="javascript:;" class="dropdown-item btn-excel"><i class="fa fa-file-excel-o"></i> Export to Excel</a>
                            @break
                            @default
                            <a href="javascript:;" class="dropdown-item btn-ppt" download><i class="fa fa-file-powerpoint-o"></i> Export to PPT</a>
                            <!-- <a href="{{ route('report.ppt', ['project_id' => $project->uuid, 'report_id' => $report->uuid]) }}?export=ppt" class="dropdown-item btn-ppt" download><i class="fa fa-file-powerpoint-o"></i> Export to PPT</a> -->
                        @endswitch
                        <a href="javascript:;" class="dropdown-item btn-pdf"><i class="fa fa-file-pdf-o"></i> Export to PDF</a>
                        <a href="javascript:;" class="dropdown-item btn-image"><i class="fa fa-image"></i> Export to Image</a>
                    </div>
                </div>
                @endif
                <a href="{{ route('project.detail', ['uuid' => $project->uuid]) }}" class="btn btn-primary"><i class="fa fa-table"></i> Project Detail</a>
            </div>
        </div>
        <div class="clearfix"></div>
        @if ($report)
            @include('report.filter')
        @endif
        <h3>Survey Result</h3>
        <div class="my-3"></div>
        <div class="panel panel-iris panel-report">
            <div class="panel-body">
                <div class="pull-right">
                    @if ($report)
                        @if ($report->type != \App\Report::TYPE_PIE)
                        <form action="{{ route('report.save.showvalues', ['project_id' => $project->uuid, 'report_id' => $report->uuid]) }}" id="formValues" method="POST">
                            {{ csrf_field() }}
                            @if ($report)
                                <input type="hidden" name="reportid" value="{{ $report->uuid }}">
                            @endif
                            <input type="hidden" name="projectid" value="{{ $project->uuid }}">
                            <table>
                                <tr>
                                    <td>Show value as</td>
                                    <td>
                                        <select name="showValues" id="showValues" class="select2"
                                            onchange="this.form.submit();"
                                        >
                                            <option value="{{ \App\Report::SHOW_NUMBER }}" {{ $report->showvalues == \App\Report::SHOW_NUMBER ? 'selected' : '' }}>Number</option>
                                            <option value="{{ \App\Report::SHOW_PERCENTAGE }}" {{ $report->showvalues == \App\Report::SHOW_PERCENTAGE ? 'selected' : '' }}>Percentage</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </form>
                        @endif
                    @endif
                </div>
                <div class="clearfix"></div>
                <div class="my-3"></div>
                @if (!$report || !isset($generatedValue))
                    <table class="table table-striped table-bordered">
                        <thead><tr><th nowrap>&nbsp;</th></tr></thead>
                        <tbody><tr><td colspan="100%">No data available.</td></tr></tbody>
                    </table>
                @else
                    @if($report->type == \App\Report::TYPE_BAR || $report->type == \App\Report::TYPE_BAR_LINE)
                    @if ((isset($report->user->role) && $report->user->role != \App\User::ROLE_ADMIN) || Auth::user()->role == \App\User::ROLE_ADMIN)
                        <form id="formTrendline" class="form-inline" action="{{ route('report.save.trendline', ['project_id' => $project->uuid]) }}" method="POST">
                            {{ csrf_field() }}
                            @if ($report)
                                <input type="hidden" name="reportid" value="{{ $report->uuid }}">
                            @endif
                            <input type="hidden" name="projectid" value="{{ $project->uuid }}">                    
                            <div class="form-group mb-2">
                                <label for="trendline">Trendline</label>
                                &nbsp;
                                <input type="number" class="form-control {{ $errors->default->first('trendline') ? 'is-invalid' : '' }}" id="trendline" name="trendline" value="{{ $report->trendline }}">
                                &nbsp;
                                <div class="invalid-feedback">
                                    {{ $errors->default->first('trendline') }}
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mb-2">Save</button>
                        </form>
                    @endif
                    @endif
                    <p class="text-left">{!! $report->summary !!}</p>
                    @switch ($report->type)
                        @case (\App\Report::TYPE_TABLE)
                            @include('report.type.table')
                        @break
                        @default
                            @include('report.type.chart')
                        @break
                    @endswitch
                @endif
                <div id="generateCanvas" class="generate-canvas"></div>
            </div>
        </div>
        @if($report && ($report->type == \App\Report::TYPE_BAR || $report->type == \App\Report::TYPE_BAR_LINE))
            @if ((isset($report->user->role) && $report->user->role != \App\User::ROLE_ADMIN) || Auth::user()->role == \App\User::ROLE_ADMIN)
                @include('report.summary.form')
            @endif
            @include('report.summary.filter')
            @include('report.summary.table')
        @endif
    </div>
@endsection

@section('js')
    <script type="text/javascript">
        $(document).ready(function () {
            $('.select2').select2();
            $('.select-multiple').select2({
                closeOnSelect: false,
            });
            $(".select-multiple").on("select2:select", function (evt) {
                var element = evt.params.data.element;
                var $element = $(element);
                
                $element.detach();
                $(this).append($element);
                $(this).trigger("change");
            });
            $('.select-filter-answer').select2({
                closeOnSelect: false
            });
            $('.select-filter-answer-summary').select2({
                closeOnSelect: false
            });
            $('.sidebar-minify-btn').on("click", function (evt){
                var form = $('.form-group');
                if(form.attr('hide') == 1){
                    form.removeClass('hide');
                    form.attr('hide','0');
                    $('.btn-generate').removeClass('hide');
                }else{
                    form.addClass('hide');
                    form.attr('hide','1');
                    $('.btn-generate').addClass('hide');
                }
            });

            @if ($report && (isset($report->user->role) && $report->user->role == \App\User::ROLE_ADMIN && Auth::user()->role != \App\User::ROLE_ADMIN ))
                $('input, select:not(.report-admin), textarea, button:not(.btn-export)').prop('disabled', true);
                $('.filter input, .filter select:not(.report-admin), .filter textarea, .filter button:not(.btn-export), select:not(.showValues)').prop('disabled', false);
                $('#formValues input').prop('disabled', false);
                $('.summary-table-form input, select, button').prop('disabled', false);
                $('.btn-generate').remove();
            @endif
            @if (session('code'))
                swal({
                    title: "{{ session('code') == 200 ? 'Success' : 'Oops' }}",
                    text: "{{ session('message') }}",
                    type: "{{ session('code') == 200 ? 'success' : 'error' }}",
                    confirmButtonClass: "btn-{{ session('code') == 200 ? 'success' : 'danger' }}",
                    confirmButtonText: "{{ session('code') == 200 ? 'Success' : 'Retry' }}!"
                });
            @endif
            addFilter();
            addFilterSummary();
            removeFilter();
            removeFilterSummary();
            
            onSelectFilterQuestion();
            onSelectFilterQuestionSummary();
            selectFilterQuestion();
            selectFilterQuestionSummary();

            @if (count($reportFilter) > 0)
                @foreach ($reportFilter as $filter)
                    selectFilterAnswer({{ $filter->question_id }});
                @endforeach
            @else
                selectFilterAnswer(1);
            @endif

            @if (count($reportFilterSummary) > 0)
                @foreach ($reportFilterSummary as $filter)
                    selectFilterAnswerSummary({{ $filter->question_id }});
                @endforeach
            @else
                selectFilterAnswerSummary(1);
            @endif

            toggleSummaryTable();
            $(document).on('change', '.toggle_summary', function () {
                toggleSummaryTable();
            });
            switchType();
        });

        function addFilter() {
            $(document).on('click', '.btn-add-filter', function () {
                var question = $('.question-filter:last');
                var index = question.attr('index');
                var next = parseInt(index) + 1;
                question.find('.select-filter-question, .select-filter-answer').select2('destroy');
                var questionId = question.find('.select-filter-answer').attr('question-id');
                var questionfilter = $('.question-filter');

                if (questionfilter.length == 1) {
                    var questionlast = $('.question-filter:last');
                    questionlast.find('.btn-remove-filter').attr('class', 'btn btn-danger btn-remove-filter');
                }

                var clone = question.clone();

                var elements = clone.find('.select-filter-answer').options;
                if (elements !== undefined) {
                    for(var i = 0; i < elements.length; i++){
                        elements[i].selected = false;
                    }
                }
                clone.attr('index', next);
                clone.find('td:first').html('Question ['+next+']');
                clone.find('.filter-user-id').remove();
                clone.find('.select-filter-question').val('').change().attr('name', 'filterQuestion['+next+']').attr('role-id', 0);
                clone.find('.select-filter-answer').val('').change().attr('name', 'filterAnswer['+next+'][]').attr('question-id', next);
                clone.find('.btn-remove-filter').attr('class', 'btn btn-danger btn-remove-filter');
                question.after(clone);
                onSelectFilterQuestion();
                selectFilterQuestion();
                selectFilterAnswer(questionId);
                selectFilterAnswer(next);
                @if (Auth::user()->role != \App\User::ROLE_ADMIN)
                    $('.select-filter-question[role-id!="{{ \App\User::ROLE_ADMIN }}"]').select2({
                        disabled: false,
                    });
                @endif
                    
                return false;
            });
        }

        function addFilterSummary() {
            $(document).on('click', '.btn-add-filter-summary', function () {
                var question = $('.question-filter-summary:last');
                var index = question.attr('index');
                var next = parseInt(index) + 1;
                question.find('.select-filter-question-summary, .select-filter-answer-summary').select2('destroy');
                var questionId = question.find('.select-filter-answer-summary').attr('question-id');
                var questionfilter = $('.question-filter-summary');

                if (questionfilter.length == 1) {
                    var questionlast = $('.question-filter-summary:last');
                    questionlast.find('.btn-remove-filter-summary').attr('class', 'btn btn-danger btn-remove-filter-summary');
                }

                var clone = question.clone();

                var elements = clone.find('.select-filter-answer-summary').options;
                if (elements !== undefined) {
                    for(var i = 0; i < elements.length; i++){
                        elements[i].selected = false;
                    }
                }
                clone.attr('index', next);
                clone.find('td:first').html('QuestionSummary ['+next+']');
                clone.find('.filter-user-id').remove();
                clone.find('.select-filter-question-summary').val('').change().attr('name', 'filterQuestionSummary['+next+']').attr('role-id', 0);
                clone.find('.select-filter-answer-summary').val('').change().attr('name', 'filterAnswerSummary['+next+'][]').attr('question-id', next);
                clone.find('.btn-remove-filter-summary').attr('class', 'btn btn-danger btn-remove-filter-summary');
                question.after(clone);
                onSelectFilterQuestionSummary();
                selectFilterQuestionSummary();
                selectFilterAnswerSummary(questionId);
                selectFilterAnswerSummary(next);
                @if (Auth::user()->role != \App\User::ROLE_ADMIN)
                    $('.select-filter-question-summary[role-id!="{{ \App\User::ROLE_ADMIN }}"]').select2({
                        disabled: false,
                    });
                @endif
                    
                return false;
            });
        }

        function removeFilter() {
            $(document).on('click', '.btn-remove-filter', function () {
                var question = $(this).closest('tr.question-filter');

                var questionfilter = $('.question-filter');
                if (questionfilter.length == 1) {
                    var questionlast = $('.question-filter:last');
                    questionlast.find('.select-filter-question').val('').change().attr('name', 'filterQuestion[1]').attr('role-id', 0);
                    questionlast.find('.select-filter-answer').val('').change().attr('name', 'filterAnswer[1][]');
                    questionlast.find('.btn-remove-filter').attr('class', 'btn btn-danger btn-remove-filter hide');
                } else {
                    question.remove();
                }

                return false;
            });            
        }

        function removeFilterSummary() {
            $(document).on('click', '.btn-remove-filter-summary', function () {
                var question = $(this).closest('tr.question-filter-summary');

                var questionfilter = $('.question-filter-summary');
                if (questionfilter.length == 1) {
                    var questionlast = $('.question-filter-summary:last');
                    questionlast.find('.select-filter-question-summary').val('').change().attr('name', 'filterQuestionSummary[1]').attr('role-id', 0);
                    questionlast.find('.select-filter-answer-summary').val('').change().attr('name', 'filterAnswerSummary[1][]');
                    questionlast.find('.btn-remove-filter-summary').attr('class', 'btn btn-danger btn-remove-filter-summary hide');
                } else {
                    question.remove();
                }

                return false;
            });            
        }

        function onSelectFilterQuestion() {
            $('.select-filter-question').on('select2:select', function (e) {
                var data = e.params.data;
                var rowFilter = $(this).closest('tr');
                rowFilter.find('.select-filter-answer').val('').change();
                rowFilter.find('.select-filter-answer').attr('question-id', data.id);
                selectFilterQuestion();
                selectFilterAnswer(data.id);
            });
        }

        function onSelectFilterQuestionSummary() {
            $('.select-filter-question-summary').on('select2:select', function (e) {
                var data = e.params.data;
                var rowFilter = $(this).closest('tr');
                rowFilter.find('.select-filter-answer-summary').val('').change();
                rowFilter.find('.select-filter-answer-summary').attr('question-id', data.id);
                selectFilterQuestionSummary();
                selectFilterAnswerSummary(data.id);
            });
        }

        function selectFilterQuestion() {
            @if (Auth::user()->role != \App\User::ROLE_ADMIN)
                $('.select-filter-question[role-id="{{ \App\User::ROLE_ADMIN }}"]').select2({
                    disabled: true,
                });
            @endif
            var questionId = [];
            $('.select-filter-question').each(function (index, element) {
                var id = $(this).val();
                if (id != '') {
                    questionId.push(id);
                }
            });
            $('.select-filter-question').select2({
                width: 250,
                ajax: {
                    delay: 500,
                    url: '{{ route('report.question', ['project_id' => $project->uuid]) }}',
                    data: function (params) {
                        var query = {
                            search: params.term,
                            page: params.page || 1,
                            questionId: questionId,
                        }

                        // Query parameters will be ?search=[term]&page=[page]
                        return query;
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;

                        return {
                            results: data.results,
                            pagination: {
                                more: (params.page * 10) < data.count_filtered
                            }
                        };
                    }
                }
            });
        }

        function selectFilterQuestionSummary() {
            @if (Auth::user()->role != \App\User::ROLE_ADMIN)
                $('.select-filter-question-summary[role-id="{{ \App\User::ROLE_ADMIN }}"]').select2({
                    disabled: true,
                });
            @endif
            var questionId = [];
            $('.select-filter-question-summary').each(function (index, element) {
                var id = $(this).val();
                if (id != '') {
                    questionId.push(id);
                }
            });
            $('.select-filter-question-summary').select2({
                width: 250,
                ajax: {
                    delay: 500,
                    url: '{{ route('report.question', ['project_id' => $project->uuid]) }}',
                    data: function (params) {
                        var query = {
                            search: params.term,
                            page: params.page || 1,
                            questionId: questionId,
                        }

                        // Query parameters will be ?search=[term]&page=[page]
                        return query;
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;

                        return {
                            results: data.results,
                            pagination: {
                                more: (params.page * 10) < data.count_filtered
                            }
                        };
                    }
                }
            });
        }

        function selectFilterAnswer(questionId) {
            $('.select-filter-answer[question-id="'+questionId+'"]').select2({
                closeOnSelect: false,
                width: 250,
                ajax: {
                    delay: 500,
                    url: '{{ route('report.question.answer', ['project_id' => $project->uuid]) }}/'+questionId,
                    data: function (params) {
                        var query = {
                            search: params.term,
                            page: params.page || 1
                        }

                        // Query parameters will be ?search=[term]&page=[page]
                        return query;
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;

                        return {
                            results: data.results,
                            pagination: {
                                more: (params.page * 10) < data.count_filtered
                            }
                        };
                    }
                }
            });
        }

        function selectFilterAnswerSummary(questionId) {
            $('.select-filter-answer-summary[question-id="'+questionId+'"]').select2({
                closeOnSelect: false,
                width: 250,
                ajax: {
                    delay: 500,
                    url: '{{ route('report.question.answer', ['project_id' => $project->uuid]) }}/'+questionId,
                    data: function (params) {
                        var query = {
                            search: params.term,
                            page: params.page || 1
                        }

                        // Query parameters will be ?search=[term]&page=[page]
                        return query;
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;

                        return {
                            results: data.results,
                            pagination: {
                                more: (params.page * 10) < data.count_filtered
                            }
                        };
                    }
                }
            });
        }

        function toggleSummaryTable() {
            var checked = $('.toggle_summary:checked').val();
            var show_summary = '{{ isset($report->show_summary)?$report->show_summary:"0" }}';
            if (checked == 1 || show_summary == 1) {
                $('.summary-table').show();
                $('.summary-form').show();
                $('.filter-summary').show();
            } else {
                $('.summary-table').hide();
                $('.summary-form').hide();
                $('.filter-summary').hide();
            }
        }

        function switchType() {
            $(document).on('change', '#type', function () {
                var type = $(this).val();
                if (type == '{{ \App\Report::TYPE_BAR_LINE }}') {
                    $('.barlineform').show();
                } else {
                    $('.barlineform').hide();                    
                }
            });
        }
    </script>

    @yield('generatejs')
@endsection