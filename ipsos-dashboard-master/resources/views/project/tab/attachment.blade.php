<div class="container">
    <div class="row">
        <div class="col-12">
            <table cellpadding="5">
                <tr>
                    <td>Column</td>
                    <td>:</td>
                    <td>
                        <select name="column" id="column" class="select2" style="width: 300px;">
                            <option value="">Choose question column</option>
                            @foreach($attachmentQuestion as $value)
                                <option value="{{ $value->id }}" {{ request()->question_id == $value->id ? 'selected' : '' }}>{{ $value->alias }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <table id="attachment-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th nowrap>{{ $identityQuestion ? ucwords(str_replace('_', ' ', $identityQuestion->alias)) : '' }}</th>
                        <th nowrap>Description</th>
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