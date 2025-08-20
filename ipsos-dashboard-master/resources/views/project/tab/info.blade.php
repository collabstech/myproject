<div class="container">
    <div class="row">
        <div class="col-12">
            <table cellpadding="10">
                <tr>
                    <th>Project Name</th>
                    <th>:</th>
                    <td>{{ $project->name }}</td>
                </tr>
                <tr>
                    <th>Project Type</th>
                    <th>:</th>
                    <td>{{ \App\Project::TYPE_NAME[$project->type] }}</td>
                </tr>
                <tr>
                    <th>Start Date</th>
                    <th>:</th>
                    <td>{{ $project->start_date ? $project->start_date->format('d F Y H:i:s') : '' }}</td>
                </tr>
                <tr>
                    <th>Finish Date</th>
                    <th>:</th>
                    <td>{{ $project->finish_date ? $project->finish_date->format('d F Y H:i:s') : '' }}</td>
                </tr>
                <tr>
                    <th>Project Description</th>
                    <th>:</th>
                    <td>{{ $project->description }}</td>
                </tr>
                <tr>
                    <th>Project Objective</th>
                    <th>:</th>
                    <td>{{ $project->objective }}</td>
                </tr>
                <tr>
                    <th>Methodology</th>
                    <th>:</th>
                    <td>{{ $project->methodology }}</td>
                </tr>
                <tr>
                    <th>Target Respondent</th>
                    <th>:</th>
                    <td>{{ $project->respondent }}</td>
                </tr>
                <tr>
                    <th>Area Coverage</th>
                    <th>:</th>
                    <td>{{ $project->coverage }}</td>
                </tr>
                <tr>
                    <th>Project Timeline</th>
                    <th>:</th>
                    <td>{{ $project->timeline }} days</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="pull-left">
        <a href="{{ url('/') }}" class="btn btn-primary"><i class="fa fa-chevron-left"></i> Back</a>
    </div>
    <div class="pull-right">
        <a href="{{ route('report.generate', ['project_id' => $project->uuid]) }}" class="btn btn-success"><i class="fa fa-plus"></i> Generate New Report</a>
    </div>

    <div class="clearfix my-3"></div>

    <div class="row">
        <div class="col-12">
            <div class="panel panel-iris">
                <div class="panel-heading">
                    <h4 class="panel-title">Dashboard List</h4>
                </div>
                <div class="panel-body">
                    <table id="report-list" class="w-100 table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th nowrap>Name</th>
                                <th nowrap>Type</th>
                                <th nowrap>Created At</th>
                                <th nowrap>Created By</th>
                                <th nowrap>Last Update</th>
                                <th nowrap>Last Update By</th>
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