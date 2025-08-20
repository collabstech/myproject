@extends('layout.master')
@section('page-title', 'Project Management Form')
@section('breadcrumb')
    <li><a href="{{ url('/') }}">Home</a></li>
    <li><a href="{{ route('project.index') }}">Project Management</a></li>
    <li class="active">Project Management Form</li>
@endsection
@section('content')
<div class="row">
    <div class="mx-auto col-6">
        <div class="panel panel-iris">
            <div class="panel-heading">
                <h4 class="panel-title text-center">{{ isset($project) ? 'Edit' : 'Create New'  }} Project</h4>
            </div>
            <div class="panel-body">
                <form id="form-project" class="form-horizontal form-project" action="{{ route('project.store') }}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" id="uuid" name="uuid" value="{{ isset($project) ? $project->uuid : 'add' }}">
                    @if (isset($project))
                    <div class="form-group">
                        <label class="col-md-4 control-label">Project Name</label>
                        <div class="col-md-8">
                            {{ $project->name }}
                        </div>
                    </div>
                    @endif
                    <div class="form-group">
                        <label class="col-md-4 control-label">Project Type</label>
                        <div class="col-md-6">
                            <select name="type" id="type" class="form-control {{ $errors->default->first('type') ? 'is-invalid' : '' }}" required>
                                <option value="{{ \App\Project::STANDARD_TYPE }}" {{ (isset($project) && $project->type == \App\Project::STANDARD_TYPE) ? 'selected' : '' }}>{{ \App\Project::TYPE_NAME[\App\Project::STANDARD_TYPE] }}</option>
                                <option value="{{ \App\Project::CSI_TYPE }}" {{ (isset($project) && $project->type == \App\Project::CSI_TYPE) ? 'selected' : '' }}>{{ \App\Project::TYPE_NAME[\App\Project::CSI_TYPE] }}</option>
                                <option value="{{ \App\Project::CSL_TYPE }}" {{ (isset($project) && $project->type == \App\Project::CSL_TYPE) ? 'selected' : '' }}>{{ \App\Project::TYPE_NAME[\App\Project::CSL_TYPE] }}</option>
                                <option value="{{ \App\Project::WHEEL_TYPE }}" {{ (isset($project) && $project->type == \App\Project::WHEEL_TYPE) ? 'selected' : '' }}>{{ \App\Project::TYPE_NAME[\App\Project::WHEEL_TYPE] }}</option>
                                <option value="{{ \App\Project::BLACKHOLE_TYPE }}" {{ (isset($project) && $project->type == \App\Project::BLACKHOLE_TYPE) ? 'selected' : '' }}>{{ \App\Project::TYPE_NAME[\App\Project::BLACKHOLE_TYPE] }}</option>
                                <option value="{{ \App\Project::CQI_TYPE }}" {{ (isset($project) && $project->type == \App\Project::CQI_TYPE) ? 'selected' : '' }}>{{ \App\Project::TYPE_NAME[\App\Project::CQI_TYPE] }}</option>
                            </select>
                            <div class="invalid-feedback">
                                {{ $errors->default->first('type') }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Company Name</label>
                        <div class="col-md-6">
                            <select name="company_id" id="company_id" class="form-control {{ $errors->default->first('company_id') ? 'is-invalid' : '' }}" required>
                                <option value="">Choose...</option>
                                @if ($companies)
                                    @foreach ($companies as $item)
                                        <option value="{{ $item->uuid }}" {{ (isset($project) && $project->company_id == $item->id) || old('company_id')==$item->uuid ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="invalid-feedback">
                                {{ $errors->default->first('company_id') }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Import Project</label>
                        <table cellpadding="10">
                            <tr>
                                <th>
                                    <div class="thumbnail-dashed thumbnail-project"><i class="fa fa-file fa-5x"></i></div>
                                    <div class="py-2"></div>
                                    <button class="btn btn-success import-project"><i class="fa fa-plus"></i> Import File</button>
                                    <input type="file" id="import_project" name="import_project" class="file-hidden project-file" />
                                    <br>
                                    <span>
                                        Example: <a href="{{ asset('files/Template_Project.xlsx') }}">Template_Project.xlsx</a>
                                    </span>
                                    <div class="invalid-feedback">
                                        {{ $errors->default->first('import_project') }}
                                    </div>
                                </th>
                            </tr>
                        </table>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Import Result</label>
                        <table cellpadding="10">
                            <tr>
                                <th>
                                    <div class="thumbnail-dashed thumbnail-result"><i class="fa fa-file fa-5x"></i></div>
                                    <div class="py-2"></div>
                                    <button class="btn btn-success import-result"><i class="fa fa-plus"></i> Import File</button>
                                    <input type="file" id="import_result" name="import_result" class="file-hidden result-file" />
                                    <br>
                                    <span>
                                        Example: <a href="{{ asset('files/Template_Result.xlsx') }}">Template_Result.xlsx</a>
                                    </span>
                                    <div class="invalid-feedback">
                                        {{ $errors->default->first('import_result') }}
                                    </div>
                                </th>
                            </tr>
                        </table>
                    </div>
                    @if (isset($project))
                    <div class="form-group">
                        <label class="col-md-4 control-label">Show Report Type in Client</label>
                        <table>
                            @foreach (\App\Report::typeLabel() as $key => $value)
                            <tr>
                                <td><input type="checkbox" id="visibleType[{{$key}}]" name="visibleType[{{$key}}]" value="1" {{ $project->{\App\Report::typeVisible()[$key]} == 1 ? 'checked' : '' }}></td>
                                <td><label for="visibleType[{{$key}}]">{{ $value }}</label></td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                    @endif
                    @if (isset($questionList))
                    <div class="form-group">
                        <label class="col-md-4 control-label">Question List</label>
                        <div style="max-height: 300px; overflow: scroll;">
                            <table class="table table-bordered table-spacing">
                                <tr>
                                    <th>Code</th>
                                    <th>Question</th>
                                    <th>Show in report</th>
                                </tr>
                                    @foreach($questionList as $question)
                                    <tr>
                                        <td>{{ $question->code }}</td>
                                        <td>{{ $question->question }}</td>
                                        <td>
                                            <label><input type="checkbox" name="visible[{{ $question->id }}][top]" value="1" {{ $question->visibleTop ? 'checked' : '' }}> Top</label>
                                            <label><input type="checkbox" name="visible[{{ $question->id }}][side]" value="1" {{ $question->visibleSide ? 'checked' : '' }}> Side</label>
                                            <br>
                                            <label><input type="checkbox" name="visible[{{ $question->id }}][value]" value="1" {{ $question->visibleValue ? 'checked' : '' }}> Value</label>
                                            <label><input type="checkbox" name="visible[{{ $question->id }}][filter]" value="1" {{ $question->visibleFilter ? 'checked' : '' }}> Filter</label>
                                            <label><input type="checkbox" name="visible[{{ $question->id }}][summary]" value="1" {{ $question->visibleSummary ? 'checked' : '' }}> Summary</label>
                                        </td>
                                    </tr>
                                    @endforeach
                            </table>
                        </div>
                    </div>
                    @endif
                    <div class="pull-right">
                        <a href="{{ route('project.index') }}" class="btn btn-sm btn-secondary">Back</a>
                        <button type="submit" class="btn btn-sm btn-primary m-r-5">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="mx-auto col-6">
        <div class="panel panel-iris">
            <div class="panel-heading">
                <h4 class="panel-title text-center">Upload Progress</h4>
            </div>
            <div class="panel-body">
                <div class="alert-message alert" role="alert" style="display: none;">
                </div>
                <div class="progress-save"></div> 
            </div>
        </div>
    </div>
</div>
<style>
.progress-save {
    display: none;
    border: 16px solid #f3f3f3; /* Light grey */
    border-top: 16px solid #3498db; /* Blue */
    border-radius: 50%;
    width: 120px;
    height: 120px;
    animation: spin 2s linear infinite;
    text-align: center;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endsection
@section('js')
<script type="text/javascript">
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
        });
        @if (session('code'))
            swal({
                title: "{{ session('code') == 200 ? 'Success' : 'Oops' }}",
                text: "{{ session('message') }}",
                type: "{{ session('code') == 200 ? 'success' : 'error' }}",
                confirmButtonClass: "btn-{{ session('code') == 200 ? 'success' : 'danger' }}",
                confirmButtonText: "{{ session('code') == 200 ? 'Success' : 'Retry' }}!"
            });
        @endif
        $('.import-project').click(function () {
            $('.project-file').click();

            return false;            
        });
        $('.project-file').change(function() {
            readURL(this, '.thumbnail-project');
        });
        $('.import-result').click(function () {
            $('.result-file').click();

            return false;            
        });
        $('.result-file').change(function() {
            readURL(this, '.thumbnail-result');
        });
        $('#form-project').submit(function (e) {
            e.preventDefault();

            var project = $('#import_project')[0].files;
            var result = $('#import_result')[0].files;
            
            var formData = new FormData();
            formData.append('_token', $('[name="_token"]').val());
            formData.append('uuid', $('#uuid').val());
            formData.append('type', $('#type').val());
            formData.append('company_id', $('#company_id').val());
            for (var i = 0; i < project.length; i++) {
                var file = project[i];
                formData.append('import_project', file, file.name);
            }
            for (var i = 0; i < result.length; i++) {
                var file = result[i];
                formData.append('import_result', file, file.name);
            }
            var progress = 0;
            
            $.ajax({
                method: 'post',
                url: '{{ route('project.store') }}',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function (response) {
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
                },
                success: function (response) {
                    progress = 100;
                    $('.alert-message').show().removeClass('alert-warning').addClass('alert-success').text(response.message);
                    $('.progress-save').hide();
                }
            });

            return false;
        });
    });
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
</script>
@endsection