<div id="accordion-question">
    @foreach($projectQuestion as $key => $question)
    <div class="card">
        <div class="card-header" data-toggle="collapse" data-target="#collapse_{{ $key }}" aria-expanded="true" aria-controls="collapse_{{ $key }}">
            <button class="btn btn-link">
                {{ $question->code.' '.$question->question }}
            </button>
        </div>

        <div id="collapse_{{ $key }}" class="collapse" data-parent="#accordion">
            <div class="card-body">
                @if ($question->projectQuestionAnswer()->count() > 0)
                    <table cellpadding="5">
                        <tbody>
                            @foreach($question->projectQuestionAnswer as $answer)
                                <tr>
                                    <td>{{ $answer->code }}</td>
                                    <td>{{ $answer->answer }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    No children answer found.
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
{{ $projectQuestion->appends(['tab' => 'question-list'])->links() }}