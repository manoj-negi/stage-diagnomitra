@extends('layouts.adminCommon')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12 text-right">
            @can('dashboard_support_list')
                <a class="btn btn-primary btn-sm my-3" href="{{ route('package.create') }}">Create</a>
            @endcan
            @if(Auth::user()->roles->contains(4))
                <a class="btn btn-primary btn-sm my-3" href="{{ route('package.create') }}">Create</a>
            @endif
            @if ($message = Session::get('msg'))
                <div class="alert alert-danger alert-dismissible text-left mt-2 alertmsg mb-3">
                    <button type="button" class="close" data-dismiss="alert">×</button> 
                    <strong>{{ $message }}</strong>
                </div>
            @endif
        </div>
        <div class="col-lg-12">
            <div class="demo-inline-spacing">
                <div class="card">
                    <div class="px-0 mt-0">
                        <div class="tab-pane fade active show" id="document">
                            <div class="table-responsive text-nowrap">
                                <form action="" method="GET">
                                    <div class="row">
                                        <div class="col-md-1">
                                            <div class="paging-section justify-content-start">
                                                <select style="width:75px;" class="form-select form-select" name="pagination" id="itemsPerPage" onchange="this.form.submit()">
                                                    <option value="10" {{ request('pagination') == '10' ? 'selected' : '' }}>10</option>
                                                    <option value="30" {{ request('pagination') == '30' ? 'selected' : '' }}>30</option>
                                                    <option value="50" {{ request('pagination') == '50' ? 'selected' : '' }}>50</option>
                                                    <option value="70" {{ request('pagination') == '70' ? 'selected' : '' }}>70</option>
                                                    <option value="100" {{ request('pagination') == '100' ? 'selected' : '' }}>100</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-11">
                                            <div class="paging-section justify-content-end row">
                                                <div class="col-md-2 mt-3">
                                                    <!-- Empty for spacing -->
                                                </div>
                                                <div class="col-md-2 mt-3">
                                                    @if(Auth::user()->roles->contains(1) && Request::segment(1) != 'diagno-profiles')
                                                        <select name="type" class="form-control mr-1" style="color:#697a8d!important;" onchange="this.form.submit()">
                                                            <option value="">Package Type</option>
                                                            <option value="is_lifestyle" {{ request('type') == 'is_lifestyle' ? 'selected' : '' }}>LIFESTYLE PACKAGE</option>
                                                            <option value="is_frequently_booking" {{ request('type') == 'is_frequently_booking' ? 'selected' : '' }}>FREQUENTLY BOOKING PACKAGE</option>
                                                        </select>
                                                    @endif
                                                </div>
                                                <div class="col-md-3 mt-3 d-flex">
                                                    <input type="text" id="search" name="search" placeholder="Search" class="form-control" value="{{ request('search') }}">
                                                    <button type="submit" class="form-control src-btn"><i class="fa fa-search" aria-hidden="true"></i></button>
                                                    <a class="form-control src-btn" href="{{ url('lab-test') }}"><i class="fa fa-rotate-left"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <form action="{{ url('package-amount-update') }}" method="POST">
                                    @csrf
                                    @if(Auth::user()->roles->contains(4))
                                        <button class="btn btn-primary m-2" style="float:right;" type="submit">Save</button>
                                    @endif
                                    <table class="table table-bordered m-2">
                                        <thead>
                                            <tr>
                                                <th style="width: 10px!important;">ID</th>
                                                @if(Auth::user()->roles->contains(4))
                                                    <th>Available</th>
                                                @endif
                                                <th>Package Name</th>
                                                <th>Profiles</th>
                                                @if(Auth::user()->roles->contains(4))
                                                    <th>Main Package Name</th>
                                                @endif
                                                <th>Amount</th>
                                                <th style="width: 160px!important;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($data as $key => $result)
                                                @php 
                                                    $value = \App\Models\LabSelectedPackages::where("lab_id", Auth::user()->id)->where("labs_packages_id", $result->id)->first();
                                                @endphp
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    @if(Auth::user()->roles->contains(4))
                                                        <td>
                                                            <div class="form-check form-check-inline mt-3">
                                                                <input class="form-check-input checkbox" data="{{ $result->id }}" type="checkbox" id="inlineCheckbox1" value="1" {{ !empty($value) && $value->is_selected == '1' ? 'checked' : '' }}>
                                                                <input type="hidden" name="checkbox[]" class="form-control" id="checked_{{ $result->id }}" value="{{ !empty($value) && $value->is_selected == '1' ? '1' : '0' }}">  
                                                            </div>
                                                        </td>
                                                    @endif
                                                    <td>{{ $result->package_name ?? 'Package not selected' }}</td>
                                                    <td>{{ $result->lab_profile_name ?? 'No Profile Name' }}</td>
                                                    @if(Auth::user()->roles->contains(4))
                                                        <td>
                                                            @php
                                                                $subPackages = \App\Models\Profile::where('lab_profile_id', $result->lab_profile_id)->first();
                                                            @endphp
                                                            {{ $subPackages->package_name ?? '--' }}
                                                        </td>
                                                    @endif
                                                    <td>
                                                        @if(Auth::user()->roles->contains(4))
                                                            <input type="text" name="amount[]" class="form-control" value="{{ $value->amount ?? $result->amount }}">
                                                            <input type="hidden" name="packagesID[]" class="form-control" value="{{ $result->id }}">
                                                        @else
                                                            {{ number_format($result->amount, 2) }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-primary btn-sm" href="{{ route('package.show', $result->id) }}">
                                                            <i class="fa fa-eye" aria-hidden="true"></i>
                                                        </a>
                                                        @if(Auth::user()->roles->contains(1))
                                                            <a class="btn btn-primary btn-sm" href="{{ route('package.edit', $result->id) }}">
                                                                <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                            </a>
                                                            <a href="{{ url('package-destroy', $result->id) }}" onclick="return confirm('Are you sure you want to delete this item?');">
                                                                <button class="btn btn-danger btn-sm" type="button">
                                                                    <i class="fa fa-trash" aria-hidden="true" style="color:#fff"></i>
                                                                </button>
                                                            </a>
                                                        @endif
                                                        @if(Auth::user()->roles->contains(4) && request()->is('package'))
                                                            <a class="btn btn-primary btn-sm" href="{{ route('package.edit', $result->id) }}">
                                                                <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                            </a>
                                                            <a href="{{ url('package-destroy', $result->id) }}" onclick="return confirm('Are you sure you want to delete this item?');">
                                                                <button class="btn btn-danger btn-sm" type="button">
                                                                    <i class="fa fa-trash" aria-hidden="true" style="color:#fff"></i>
                                                                </button>
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No Data Found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </form>
                            </div>
                            <div id="pagination">
                                {{ $data->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
   $(document).ready(function() {
        $(document).on('change', '.checkbox', function() {
            if ($(this).prop('checked') == true) {
                $(this).val(1);
                $("#checked_" + $(this).data("id")).val(1);
            } else {
                $(this).val(0);
                $("#checked_" + $(this).data("id")).val(0);
            }
        });
    });
</script>
@endsection
