
@if ($students->count() > 0)
    <div class="table-responsive">
        <!-- Table to display students and their wallet balances -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{ get_phrase('id') }}</th>
                    <th>{{ get_phrase('Name') }}</th>
                    <th>{{ get_phrase('Email') }}</th>
                    <th>{{ get_phrase('Balance') }}</th>
                    <th>{{ get_phrase('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                    <tr>
                        <td>{{ $student->id }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->email }}</td>
                        <td>
                            @if($student->wallet)
                                {{ number_format($student->wallet->balance, 2) }} {{ $student->wallet->currency ?? 'SYP' }}
                            @else
                                {{ get_phrase('No wallet') }}
                            @endif
                        </td>
                        <td>
                            <!-- Actions like add funds, etc. -->
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFundsModal{{ $student->id }}">
                                {{ get_phrase('Add Funds') }}
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="admin-tInfo-pagi d-flex justify-content-between align-items-center flex-wrap">
        <p class="admin-tInfo">
            {{ get_phrase('Showing') . ' ' . count($students) . ' ' . get_phrase('of') . ' ' . $students->total() . ' ' . get_phrase('data') }}
        </p>
        {!! $students->links() !!}
    </div>
@else
    @include('admin.no_data')
@endif
