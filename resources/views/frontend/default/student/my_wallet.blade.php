@extends('layouts.default')

@push('title', get_phrase('My Wallet'))
@push('meta')@endpush
@push('css')
    <style>
        .wallet-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 40vh;
            text-align: center;
            flex-direction: column;
        }

        .balance-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        .balance-card h5 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #555;
        }

        .balance-amount {
            font-size: 3rem;
            font-weight: bold;
            color: #28a745; /* Green for balance */
            margin-bottom: 20px;
        }

        .card {
            border-radius: 10px;
            border: 1px solid #ddd;
            margin-top: 20px;
        }

        .transaction-header {
            background-color: #f1f1f1;
            font-weight: bold;
        }

        .transaction-history table th,
        .transaction-history table td {
            padding: 10px;
            text-align: center;
        }

        .entry-pagination {
            margin-top: 20px;
        }
    </style>
@endpush

@section('content')
    <section class="my-course-content">
        <div class="profile-banner-area"></div>
        <div class="container profile-banner-area-container">
            <div class="row">
                @include('frontend.default.student.left_sidebar')

                <div class="col-lg-9 px-4">
                    <h4 class="g-title">{{ get_phrase('My Wallet') }}</h4>

                    <!-- Centered Balance Section -->
                    <div class="wallet-container">
                        <div class="balance-card">
                            <h5>{{ get_phrase('Current Balance') }}</h5>
                            <p class="balance-amount">{{ number_format($wallet->balance, 2) }} {{ $wallet->currency }}</p>
                        </div>
                    </div>

                    <!-- Transaction History Section -->
                    <div class="card p-4">
                        <h5>{{ get_phrase('Transaction History') }}</h5>
                        <div class="table-responsive mt-3 transaction-history">
                            <table class="table">
                                <thead class="transaction-header">
                                    <tr>
                                        <th>{{ get_phrase('Date') }}</th>
                                        <th>{{ get_phrase('Description') }}</th>
                                        <th>{{ get_phrase('Amount') }}</th>
                                        <th>{{ get_phrase('Type') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $t)
                                        <tr>
                                            <td>{{ $t->created_at ? $t->created_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                            <td>{{ $t->description }}</td>
                                            <td>{{ number_format($t->amount, 2) }}</td>
                                            <td>{{ ucfirst($t->transaction_type) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">{{ get_phrase('No transactions found.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        @if ($transactions->hasPages())
                            <div class="entry-pagination mt-3">
                                <nav>
                                    {{ $transactions->links() }}
                                </nav>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('js')
@endpush
