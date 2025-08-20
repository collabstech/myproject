@extends('layout.master')
@section('page-title', 'User Management Form')
@section('breadcrumb')
    <li><a href="{{ url('/') }}">Home</a></li>
    <li><a href="{{ route('user.index') }}">User Management</a></li>
    <li class="active">User Management Form</li>
@endsection
@section('content')
<div class="row">
    <div class="mx-auto col-6">
        <div class="panel panel-iris">
            <div class="panel-heading">
                <h4 class="panel-title text-center">{{ isset($user) ? 'Edit' : 'Create New'  }} User</h4>
            </div>
            <div class="panel-body">
                <form class="form-horizontal" action="{{ route('user.store') }}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="uuid" value="{{ isset($user) && $user->uuid ? $user->uuid : 'add' }}">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Profile Picture</label>
                        <div class="col-md-8">
                            <table cellpadding="10">
                                <tr>
                                    <th>
                                        <img src="{{ isset($user) && $user->avatar ? route('file', ['url' => $user->avatar]) : asset('images/placeholder.png') }}" alt="" class="default-thumbnail avatar">
                                        <div class="py-2"></div>
                                        <button class="btn btn-success avatar-edit">Upload</button>
                                        <input type="file" name="avatar" class="file-hidden avatar-file" />
                                        <div class="invalid-feedback">
                                            {{ $errors->default->first('avatar') }}
                                        </div>
                                    </th>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Company Name</label>
                        <div class="col-md-8">
                            <select name="company_id" id="company_id" class="form-control {{ $errors->default->first('company_id') ? 'is-invalid' : '' }}" required>
                                <option value="">Choose...</option>
                                @if ($companies)
                                    @foreach ($companies as $item)
                                        <option value="{{ $item->uuid }}" {{ (isset($user) && $user->company_id == $item->id) ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="invalid-feedback">
                                {{ $errors->default->first('company_id') }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Fullname</label>
                        <div class="col-md-8">
                            <input type="text" name="name" class="form-control {{ $errors->default->first('name') ? 'is-invalid' : '' }}" placeholder="Enter fullname" value="{{ isset($user) && $user->name ? $user->name : old('name') }}" maxlength="120" required>
                            <div class="invalid-feedback">
                                {{ $errors->default->first('name') }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Email address</label>
                        <div class="col-md-8">
                            <input type="email" name="email" class="form-control {{ $errors->default->first('email') ? 'is-invalid' : '' }}" placeholder="Enter email" value="{{ isset($user) && $user->email ? $user->email : old('email') }}" required>
                            <div class="invalid-feedback">
                                {{ $errors->default->first('email') }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Password</label>
                        <div class="col-md-8">
                            <input type="password" name="password" class="form-control {{ $errors->default->first('password') ? 'is-invalid' : '' }}" placeholder="Password">
                            <div class="invalid-feedback">
                                {{ $errors->default->first('password') }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Role</label>
                        <div class="col-md-8">
                            <select name="role" id="role" class="form-control {{ $errors->default->first('role') ? 'is-invalid' : '' }}" required>
                                @foreach (\App\User::roleLabel() as $key => $value)
                                    <option value="{{ $key }}" {{ (isset($user) && $user->role == $key) || old('role') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                {{ $errors->default->first('role') }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Project List</label>
                        <div class="col-md-8">
                            <?php 
                                $project = !empty(old('project'))?old('project'):array();
                            ?>
                            <select name="project[]" id="project[]" class="select2 project-list" multiple="multiple">
                                @foreach ($projects as $item)
                                    <option value="{{ $item->uuid }}" {{ (isset($userProject[$item->id]) && $userProject[$item->id]->project_id == $item->id) || in_array($item->uuid, $project)  ? 'selected' : '' }}>{{ $item->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                {{ $errors->default->first('project') }}
                            </div>
                        </div>
                    </div>
                    <div class="pull-right">
                        <a href="{{ route('user.index') }}" class="btn btn-sm btn-secondary">Back</a>
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
            width: '100%'
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
        $('.avatar-edit').click(function () {
            $('.avatar-file').click();

            return false;            
        });
        $('.avatar-file').change(function() {
            readURL(this, '.avatar');
        });
        $('.logo-edit').click(function () {
            $('.logo-file').click();

            return false;            
        });
        $('.logo-file').change(function() {
            readURL(this, '.logo');
        });

        if("{{old('company_id')}}"){
            $("#company_id").val('{{old("company_id")}}');
            refillValue('{{old("company_id")}}');
        }
        initProjectList();
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

    function initProjectList() {
        $(document).on('change', '#company_id', function () {
            var companyId = $(this).val();
            $('.project-list').val('').change();
            refillValue(companyId);
        });
    }
    function refillValue(companyId) {
        $('.project-list').select2({
                width: '100%',
                ajax: {
                    delay: 500,
                    url: '{{ url('company') }}/'+companyId+'/project',
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
                        };
                    }
                }
            });
    }
</script>
@endsection