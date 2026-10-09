@if ($students->count() > 0)
    <div class="table-responsive">
        <table class="table eTable eTable-2">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">{{ get_phrase('Name') }}</th>
                    <th scope="col">{{ get_phrase('Wallet Balance') }}</th>
                    <th class="text-end" scope="col">{{ get_phrase('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $key => $student)
                    <tr>
                        <th scope="row">
                            <p class="row-number">{{ ++$key }}</p>
                        </th>
                        <td>
                            <div class="dAdmin_profile d-flex align-items-center min-w-200px">
                                <div class="dAdmin_profile_img">
                                    <img class="img-fluid rounded-circle image-45" width="45" height="45" src="{{ get_image($student->photo) }}" alt="">
                                </div>
                                <div class="ms-1">
                                    <h4 class="title fs-14px">{{ $student->name }}</h4>
                                    <p class="sub-title2 text-12px">{{ $student->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if ($student->wallet)
                                <div class="dAdmin_info_name">
                                    <p class="fw-600">{{ number_format($student->wallet->balance, 2) }} {{ $student->wallet->currency ?? 'SYP' }}</p>
                                </div>
                            @else
                                <span class="badge bg-secondary">{{ get_phrase('No wallet') }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="dropdown ol-icon-dropdown ol-icon-dropdown-transparent">
                                <button class="btn ol-btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="fi-rr-menu-dots-vertical"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addFundsModal{{ $student->id }}" href="javascript:void(0)">{{ get_phrase('Add Funds') }}</a>
                                    </li>
                                </ul>
                            </div>
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
        {!! $students->appends(request()->query())->links() !!}
    </div>
@else
    @include('admin.no_data')
@endif