@extends('layouts.adminlte')

@section('content_header_title', 'Inventory Audit History')
@section('content_header_subtitle', 'All recorded inventory accuracy audits')

@section('content_body')
    <div class="container-fluid">

        {{-- BACK BUTTON --}}
        <div class="row mb-3">
            <div class="col-12">
                <a href="{{ route('reports.weekly') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Weekly Report
                </a>
            </div>
        </div>

        {{-- FILTER BY PERIOD --}}
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header" style="background-color: #1a73e8;">
                <h3 class="card-title font-weight-bold text-white">
                    <i class="fas fa-filter mr-2"></i> FILTER BY AUDIT PERIOD
                </h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('reports.audit.history') }}">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="font-weight-bold">Select Audit Period</label>
                            <select name="period" class="form-control">
                                <option value="">-- All Periods --</option>
                                @foreach ($auditPeriods as $period)
                                    <option value="{{ $period }}" {{ $selectedPeriod == $period ? 'selected' : '' }}>
                                        {{ $period }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-search mr-1"></i> Apply
                            </button>
                            <a href="{{ route('reports.audit.history') }}" class="btn btn-secondary">
                                <i class="fas fa-redo mr-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- AUDIT RECORDS --}}
        @forelse($groupedAudits as $groupKey => $records)
            @php
                $parts = explode('||', $groupKey);
                $period = $parts[0] ?? '';
                $auditedBy = $parts[1] ?? 'Unknown';
                $auditedAt = $parts[2] ?? '';

                $totalItems = $records->count();
                $matchCount = $records->where('status', 'Match')->count();
                $missingCount = $records->where('status', 'Missing')->count();
                $surplusCount = $records->where('status', 'Surplus')->count();
            @endphp

            <div class="card card-outline card-warning shadow-sm mb-4">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-clipboard-check text-warning mr-2"></i>
                        Audit Period: <span class="text-primary">{{ $period }}</span>
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-secondary mr-1">{{ $totalItems }} items</span>
                        <span class="badge badge-success mr-1">{{ $matchCount }} Match</span>
                        <span class="badge badge-danger mr-1">{{ $missingCount }} Missing</span>
                        <span class="badge badge-warning mr-1">{{ $surplusCount }} Surplus</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    {{-- Audit Meta --}}
                    <div class="px-3 py-2 bg-light border-bottom">
                        <small class="text-muted">
                            <i class="fas fa-user mr-1"></i> Audited by: <strong>{{ $auditedBy }}</strong>
                            &nbsp;&nbsp;
                            <i class="fas fa-clock mr-1"></i> Recorded on: <strong>{{ $auditedAt }}</strong>
                        </small>
                    </div>

                    <table class="table table-bordered text-center mb-0">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th class="text-left pl-3">Product Name</th>
                                <th>SKU</th>
                                <th>System Count</th>
                                <th>Actual Count</th>
                                <th>Variance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($records as $record)
                                <tr>
                                    <td class="text-left pl-3 align-middle font-weight-bold">{{ $record->product_name }}
                                    </td>
                                    <td class="align-middle text-muted"><small>{{ $record->product_sku ?? 'N/A' }}</small>
                                    </td>
                                    <td class="align-middle">{{ $record->system_count }}</td>
                                    <td class="align-middle font-weight-bold">{{ $record->actual_count }}</td>
                                    <td
                                        class="align-middle font-weight-bold
                                        {{ $record->variance == 0 ? 'text-dark' : ($record->variance < 0 ? 'text-danger' : 'text-success') }}">
                                        {{ $record->variance > 0 ? '+' . $record->variance : $record->variance }}
                                    </td>
                                    <td class="align-middle">
                                        @if ($record->status === 'Match')
                                            <span class="badge badge-success px-3 py-1">Match</span>
                                        @elseif($record->status === 'Missing')
                                            <span class="badge badge-danger px-3 py-1">MISSING</span>
                                        @else
                                            <span class="badge badge-warning px-3 py-1">SURPLUS</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-5">
                    <i class="fas fa-clipboard fa-3x mb-3 d-block"></i>
                    No audit records found.
                    <a href="{{ route('reports.weekly') }}" class="d-block mt-2">Go to Weekly Report to start an audit.</a>
                </div>
            </div>
        @endforelse

    </div>
@endsection
