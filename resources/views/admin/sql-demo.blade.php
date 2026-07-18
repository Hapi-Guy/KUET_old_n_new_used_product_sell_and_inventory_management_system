@extends('layouts.app')
@section('title', 'Raw SQL demo')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0"><i class="bi bi-database text-kuet"></i> Raw Oracle SQL demo</h4>
        <small class="text-muted">Each block shows a hand-written query (run via <code>DB::select</code>) and its live result.</small>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Dashboard
    </a>
</div>

@foreach ($results as $r)
    <div class="card mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-terminal text-kuet"></i> {{ $r['title'] }}
        </div>
        <div class="card-body">
            <pre class="bg-light border rounded p-2 mb-3" style="white-space:pre-wrap;font-size:.82rem;">{{ trim(preg_replace('/\s+/', ' ', $r['sql'])) }}</pre>

            @if ($r['error'])
                <div class="alert alert-danger mb-0 small">
                    <i class="bi bi-exclamation-triangle"></i> {{ $r['error'] }}
                </div>
            @elseif (empty($r['rows']))
                <p class="text-muted small mb-0">Query ran successfully — 0 rows returned.</p>
            @else
                @php $cols = array_keys((array) $r['rows'][0]); @endphp
                <div class="text-muted small mb-1">{{ count($r['rows']) }} row(s)</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                @foreach ($cols as $c)
                                    <th>{{ $c }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($r['rows'] as $row)
                                <tr>
                                    @foreach ((array) $row as $value)
                                        <td>{{ $value }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endforeach
@endsection
