@extends('layouts.admin')

@section('title', get_phrase('Students Wallets'))

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .modal .form-label {
            font-weight: bold;
        }
    </style>
@endpush

@section('content')
    <!-- Wallet Page Header -->
    <div class="ol-card radius-8px">
        <div class="ol-card-body my-3 py-12px px-20px">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-md-nowrap">
                <h4 class="title fs-16px">
                    <i class="fi-rr-wallet me-2"></i>
                    {{ get_phrase('Students Wallets') }}
                </h4>

                <button class="btn ol-btn-primary" data-bs-toggle="modal" data-bs-target="#addFundsModal">
                    <span class="fi-rr-plus"></span>
                    <span>{{ get_phrase('Add Funds') }}</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Search Input -->
    <div class="my-3">
        <input type="text" id="searchWalletUser" class="form-control ol-form-control" placeholder="{{ get_phrase('Search by user name or email') }}">
    </div>

    <!-- Wallet Table -->
    <div id="walletTable">
        @include('admin.wallet.partials.table', ['students' => $students])
    </div>

    <!-- Add Funds Modal -->
    <div class="modal fade" id="addFundsModal" tabindex="-1" aria-labelledby="addFundsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ get_phrase('Add Funds to User') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.wallet.add') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                        <label for="userSelect" class="form-label">{{ get_phrase('Select a User') }}</label>
        <select name="user_id" id="userSelect" class="form-control" style="width: 100%;" required>
            <option value="">{{ get_phrase('Select a user') }}</option>
            @foreach($allStudents as $student)
                <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
            @endforeach
        </select>

                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label">{{ get_phrase('Amount') }}</label>
                            <input type="number" name="amount" class="form-control" placeholder="{{ get_phrase('Enter amount') }}">
                        </div>

                        <input type="hidden" name="transaction_type" value="Manual recharge">

                    <button id="button" type="submit" class="btn ol-btn-primary" onclick="
    var button = this;
    
    button.disabled = true;
    button.innerText = 'Processing...';
    
   
    setTimeout(function() {
        button.closest('form').submit();
    }, 1000);
">
    {{ get_phrase('Add Funds') }}
</button>


                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Individual Modals -->
    @foreach ($students as $student)
        <div class="modal fade" id="addFundsModal{{ $student->id }}" tabindex="-1" aria-labelledby="addFundsModalLabel{{ $student->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ get_phrase('Add Funds to ' . $student->name) }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('admin.wallet.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $student->id }}">
                            
                            <div class="mb-3">
                                <label class="form-label">{{ get_phrase('Amount') }}</label>
                                <input type="number" name="amount" class="form-control" placeholder="{{ get_phrase('Enter amount') }}">
                            </div>

                            <!-- Hidden input for transaction type (hardcoded to "Manual recharge") -->
                            <input type="hidden" name="transaction_type" value="Manual recharge">

                        <button id="button" type="submit" class="btn ol-btn-primary" onclick="
    var button = this;
    // Disable the button and change text
    button.disabled = true;
    button.innerText = 'Processing...';
    
    // Submit the form
    setTimeout(function() {
        button.closest('form').submit();
    }, 1000);
">
    {{ get_phrase('Add Funds') }}
</button>


                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#userSelect').select2({
                dropdownParent: $('#addFundsModal')
            });

            let searchDelay;
            const liveSearch = document.getElementById('searchWalletUser');

            liveSearch.addEventListener('keyup', function () {
                clearTimeout(searchDelay);
                searchDelay = setTimeout(() => {
                    fetchTable(liveSearch.value);
                }, 300);
            });

            function fetchTable(search = '') {
                fetch(`{{ route('admin.wallet') }}?search=${search}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('walletTable').innerHTML = data.html;
                });
            }

            document.addEventListener('click', function(e) {
                const link = e.target.closest();
                if (link) {
                    e.preventDefault();
                    fetch(link.getAttribute('href'), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('walletTable').innerHTML = data.html;
                    });
                }
            });
        });
        
    </script>
@endpush
