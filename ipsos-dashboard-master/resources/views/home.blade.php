@extends('layout.master')
@section('page-title', 'Home')
@section('breadcrumb')
    <li><a href="javascript:;">Home</a></li>
@endsection
@section('content')
    <div class="my-5"></div>
    <div class="container">
        <form action="{{ route('profile.save') }}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-xs-4 col-md-2">
                    <img src="{{ route('file', ['url' => Auth::user()->avatar]) }}" alt="" class="profile-avatar">
                    
                    <div class="my-2"></div>

                    <button class="btn btn-primary profile-avatar-edit"><i class="fa fa-image"></i> Change Picture</button>
                    <input type="file" name="avatar" class="profile-avatar-file" />
                </div>
                <div class="col-xs-8 col-md-9">
                    <table class="table-profile" cellpadding="10">
                        <tr>
                            <td><i class="fa fa-2x fa-user"></i></td>
                            <td>
                                <span class="profile-name profile-text">{{ Auth::user()->name }}</span>
                                <input type="text" name="name" class="profile-input input-lg" value="{{ Auth::user()->name }}" maxlength="120">
                            </td>
                        </tr>
                        <tr>
                            <td><i class="fa fa-2x fa-envelope-o"></i></td>
                            <td>
                                <span class="profile-email profile-text">{{ Auth::user()->email }}</span>
                                <input type="email" name="email" class="profile-input input-lg" value="{{ Auth::user()->email }}" 
                                @if (Auth::user()->role != \App\User::ROLE_ADMIN)
                                    readonly
                                @endif
                                >
                            </td>
                        </tr>
                        <tr>
                            <td><i class="fa fa-2x fa-lock"></i></td>
                            <td>
                                <span class="profile-password profile-text">Change Password</span>
                                <input type="password" name="password" class="profile-input input-lg" placeholder="New Password">
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>
                                <button class="btn btn-primary btn-edit-profile"><i class="fa fa-pencil"></i> Edit Profile</button>
                                <button type="submit" class="btn btn-success btn-save"><i class="fa fa-save"></i> Save</button>
                                <button class="btn btn-secondary btn-cancel"><i class="fa fa-ban"></i> Cancel</button>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </form>

        <div class="my-2"></div>

        <div class="row">
            <div class="col-12">
                <div class="panel panel-iris">
                    <div class="panel-heading">
                        <h4 class="panel-title">Project List</h4>
                    </div>
                    <div class="panel-body">
                        <table id="project-list" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th nowrap>No</th>
                                    <th nowrap>Project Name</th>
                                    <th nowrap>Start Date</th>
                                    <th nowrap>Finish Date</th>
                                    <th nowrap>Project Objective</th>
                                    <th nowrap>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="100%">No data available.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
<script type="text/javascript">
    $(document).ready(function() {
        @if (session('code'))
            swal({
                title: "{{ session('code') == 200 ? 'Success' : 'Oops' }}",
                text: "{{ session('message') }}",
                type: "{{ session('code') == 200 ? 'success' : 'error' }}",
                confirmButtonClass: "btn-{{ session('code') == 200 ? 'success' : 'danger' }}",
                confirmButtonText: "{{ session('code') == 200 ? 'Success' : 'Retry' }}!"
            });
        @endif
        $('.btn-edit-profile').click(function () {
            showProfileInput();
            
            return false;
        });
        $('.btn-cancel').click(function () {
            hideProfileInput();
            $('.profile-avatar').attr('src', "{{ route('file', ['url' => Auth::user()->avatar]) }}");
            $('.profile-avatar-file').val('');            
            
            return false;
        });
        $('.profile-avatar-edit').click(function () {
            $('.profile-avatar-file').click();

            return false;            
        });
        $('.profile-avatar-file').change(function() {
            readURL(this);
        });
        initProjectList();
    });

    function initProjectList() {
        var table = $('#project-list').DataTable({
            processing: true,
            serverSide: true,
            @if (Auth::user()->role == \App\User::ROLE_ADMIN)
                searching: false,
                paging: false,
            @endif
            ajax: '{!! route('home.project.data') !!}',
            order: [
                [6, 'desc']
            ],
            columns: [
                { data: 'rownum', name: 'rownum', searchable: false, orderable: false },
                { data: 'name', name: 'name' },
                { data: 'start_date', name: 'start_date' },
                { data: 'finish_date', name: 'finish_date' },
                { data: 'objective', name: 'objective' },
                { data: 'action', name: 'action', searchable: false, orderable: false },
                @if (Auth::user()->role == \App\User::ROLE_ADMIN)
                    { data: 'created_at', name: 'created_at', visible: false, searchable: false },
                @else
                    { data: 'updated_at', name: 'updated_at', visible: false, searchable: false },
                @endif
            ]
        });
        table.on( 'order.dt search.dt', function () {
            table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                cell.innerHTML = i+1;
            } );
        } ).draw();
    }

    function showProfileInput() {
        $('.profile-text').hide();
        $('.profile-input, .profile-avatar-edit, .btn-save, .btn-cancel').show();
        $('.btn-edit-profile').hide();
    }

    function hideProfileInput() {
        $('.profile-text').show();
        $('.profile-input, .profile-avatar-edit, .btn-save, .btn-cancel').hide();
        $('.btn-edit-profile').show();
    }

    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('.profile-avatar').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection