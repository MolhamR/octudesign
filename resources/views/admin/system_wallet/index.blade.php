@extends('layouts.admin')

@push('title', get_phrase('System Wallet'))

@push('meta')
@endpush

@push('css')
@endpush

@section('content')
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="ol-card">
                <div class="ol-card-body d-flex align-items-center justify-content-between p-3">
                    <div>
                        <p class="fs-12px text-muted mb-0">{{ get_phrase('Total profit') }}</p>
                        <h4 class="mb-0 fw-600">{{ number_format($total_in, 2) }} SYP</h4>
                    </div>
                    <i class="fi fi-rr-arrow-up-right-from-square fs-24px text-success"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="ol-card">
                <div class="ol-card-body d-flex align-items-center justify-content-between p-3">
                    <div>
                        <p class="fs-12px text-muted mb-0">{{ get_phrase('Total transferred') }}</p>
                        <h4 class="mb-0 fw-600">{{ number_format($total_out, 2) }} SYP</h4>
                    </div>
                    <i class="fi fi-rr-arrow-right-from-bracket fs-24px text-danger"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="ol-card">
                <div class="ol-card-body d-flex align-items-center justify-content-between p-3">
                    <div>
                        <p class="fs-12px text-muted mb-0">{{ get_phrase('Withdrawable balance') }}</p>
                        <h4 class="mb-0 fw-600">{{ number_format($withdrawable, 2) }} SYP</h4>
                    </div>
                    <i class="fi fi-rr-wallet fs-24px text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="ol-card radius-8px">
        <div class="ol-card-body my-3 py-12px px-20px">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-md-nowrap">
                <h4 class="title fs-16px">
                    <i class="fi fi-rr-wallet me-2"></i>
                    {{ get_phrase('System Wallet') }}
                </h4>

                <form action="{{ route('admin.system.wallet.transfer') }}" method="post" onsubmit="return confirm('{{ get_phrase('Transfer the withdrawable profit to the system withdrawal account?') }}')">
                    @csrf
                    <button type="submit" class="btn ol-btn-primary">
                        <span class="fi-rr-arrow-right-from-bracket"></span>
                        <span>{{ get_phrase('Transfer profit') }} @if ($withdraw_account) ({{ $withdraw_account }}) @endif</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="ol-card">
        <div class="ol-card-body p-3">
            @if (count($transactions) > 0)
                <div class="table-responsive">
                    <table class="table eTable eTable-2">
                        <thead>
                            <tr>
                                <th>{{ get_phrase('Date') }}</th>
                                <th>{{ get_phrase('Type') }}</th>
                                <th>{{ get_phrase('Note') }}</th>
                                <th>{{ get_phrase('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $transaction)
                                <tr>
                                    <td>{{ date('d M Y, H:i', strtotime($transaction->entry_date)) }}</td>
                                    <td>
                                        @if ($transaction->type == 'in')
                                            <span class="badge bg-success badge-outline-success">{{ get_phrase('In') }}</span>
                                        @else
                                            <span class="badge bg-danger badge-outline-danger">{{ get_phrase('Out') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $transaction->note }}</td>
                                    <td class="fw-600">{{ number_format($transaction->amount, 2) }} SYP</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $transactions->links() }}
                </div>
            @else
                <div class="empty_box center">
                    <div class="empty_box-img">
                        <img src="{{ asset('assets/global/images/empty_box.svg') }}" alt="">
                    </div>
                    <h2 class="fs-14px">{{ get_phrase('No profit records yet') }}</h2>
                </div>
            @endif
        </div>
    </div>
@endsection