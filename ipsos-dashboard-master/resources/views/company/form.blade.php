@extends('layout.master')
@section('page-title', 'Company Management Form')
@section('breadcrumb')
    <li><a href="{{ url('/') }}">Home</a></li>
    <li><a href="{{ route('company.index') }}">Company Management</a></li>
    <li class="active">Company Management Form</li>
@endsection
@section('content')
<div class="row">
    <div class="mx-auto col-6">
        <div class="panel panel-iris">
            <div class="panel-heading">
                <h4 class="panel-title text-center">{{ isset($company) ? 'Edit' : 'Create New'  }} Company</h4>
            </div>
            <div class="panel-body">
                <form class="form-horizontal form-company" action="{{ route('company.store') }}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="uuid" value="{{ isset($company) ? $company->uuid : 'add' }}">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Company Logo</label>
                        <table cellpadding="10">
                            <tr>
                                <th>
                                    <img src="{{ isset($company) && $company->logo ? route('file', ['url' => $company->logo]) : asset('images/image.png') }}" alt="" class="default-thumbnail logo">
                                    <div class="py-2"></div>
                                    <button class="btn btn-success logo-edit">Upload</button>
                                    <input type="file" name="logo" class="file-hidden logo-file" />
                                    <div class="invalid-feedback">
                                        {{ $errors->default->first('logo') }}
                                    </div>
                                </th>
                            </tr>
                        </table>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Company Name</label>
                        <div class="col-md-8">
                            <input type="text" name="name" class="form-control {{ $errors->default->first('name') ? 'is-invalid' : '' }}" placeholder="Enter Company Name" value="{{ isset($company) && $company->name ? $company->name : old('name') }}" maxlength="120">
                            <div class="invalid-feedback">
                                {{ $errors->default->first('name') }}
                            </div>
                        </div>
                    </div>
                    <!-- <div class="form-group">
                        <label class="col-md-4 control-label">Project List</label>
                        <div class="col-md-8">
                            <select name="project[]" id="project[]" class="select2" multiple="multiple">
                                @foreach ($projects as $item)
                                    <option value="{{ $item->uuid }}" {{ isset($companyProject[$item->id]) && $companyProject[$item->id]->project_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                {{ $errors->default->first('project') }}
                            </div>
                        </div>
                    </div> -->
                    <div class="pull-right">
                        <a href="{{ route('company.index') }}" class="btn btn-sm btn-secondary">Back</a>
                        <button type="submit" class="btn btn-sm btn-primary m-r-5">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
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
        $('.logo-edit').click(function () {
            $('.logo-file').click();

            return false;            
        });
        $('.logo-file').change(function() {
            readURL(this, '.logo');
        });
    });
    function readURL(input, imageClass) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $(imageClass).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection