@extends('layouts.adminCommon')
@section('content')
<div class="container">
   <div class="row">
      <div class="col-md-12 text-right">
         <?php  $url = Request::segment(1); ?>
         <a class="btn btn-primary btn-sm my-3" href='{{url("profile/create?url=$url")}}'>Create</a>
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
                        <form action="">
                           <div class="row">
                              <div class="col-md-1">
                                 <div class="paging-section justify-content-start">
                                    <select style="width:75px;" class="form-select" name="pagination" id="itemsPerPage" onchange="this.form.submit()">
                                       <option value="10" {{ app('request')->input('pagination') == '10' ? 'selected' : '' }}>10</option>
                                       <option value="30" {{ app('request')->input('pagination') == '30' ? 'selected' : '' }}>30</option>
                                       <option value="50" {{ app('request')->input('pagination') == '50' ? 'selected' : '' }}>50</option>
                                       <option value="70" {{ app('request')->input('pagination') == '70' ? 'selected' : '' }}>70</option>
                                       <option value="100" {{ app('request')->input('pagination') == '100' ? 'selected' : '' }}>100</option>
                                    </select>
                                 </div>
                              </div>
                              <div class="col-md-11">
                                 <div class="paging-section justify-content-end row">
                                    <div class="col-md-2 mt-3">
                                    </div>
                                    <div class="col-md-2 mt-3">
                                       @if(Auth::user()->roles->contains(1) && Request::segment(1) != 'diagno-profiles')
                                       <select name="lab" id="labDropdown" class="form-control mr-1" style="color:#697a8d!important;" onchange="fetchLabProfiles(this.value)">
                                          <option value="">Select Lab</option>
                                          @foreach($labs ?? [] as $item)
                                          <option value="{{$item->id ?? ''}}" {{ app('request')->input('lab') == $item->id ? 'selected' : '' }}>{{$item->name ?? ''}}</option>
                                          @endforeach
                                       </select>
                                       @endif
                                    </div>
                                    <div class="col-md-3 mt-3 d-flex">
                                       <input type="text" id="search" name="search" placeholder="Search" class="form-control" value="{{ app('request')->input('search') }}">
                                       <button type="submit" class="form-control src-btn"><i class="fa fa-search" aria-hidden="true"></i></button>
                                       <a class="form-control src-btn" href="{{url('profile')}}"><i class="fa fa-rotate-left"></i></a>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </form>
                        <form>
                           <button class="btn btn-primary m-2" style="float:right;" type="submit">Save</button>
                           <table class="table table-bordered m-2">
                              <thead>
                                 <tr>
                                    <th style="width: 10px!important;">ID</th>
                                    @if(Auth::user()->roles->contains(4))
                                    <th>Available</th>
                                    @endif
                                    <th>Profile Name</th>
                                    @if(Auth::user()->roles->contains(4))
                                    <!-- <th>Main Profile Name</th> -->
                                    @endif
                                    <th>Total Tests</th>
                                    @if(Auth::user()->roles->contains(1))  
                                    <th>Lab Name</th> 
                                    @endif
                                    <th>Amount</th>
                                    <th style="width: 160px!important;">Action</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 @forelse($data as $key => $result)
                                 <tr>
                                    <td>{{ $key + 1 }}</td>
                                    @if(Auth::user()->roles->contains(4))
                                    <td>
                                       <div class="form-check form-check-inline mt-3">
                                          <input class="form-check-input checkbox" data="{{$result->id}}" type="checkbox" id="inlineCheckbox1" value="1" {{ !empty($value) && $value->is_selected == '1' ? 'checked' : '' }}>
                                          <input type="hidden" name="checkbox[]" class="form-control" id="checked_{{$result->id}}" value="{{ !empty($value) && $value->is_selected == '1' ? '1' : '0' }}">  
                                       </div>
                                    </td>
                                    @endif
                                    <td>{{ $result->profile_name ?? 'Profile not selected' }}</td>
                                    @if(Auth::user()->roles->contains(4))
                                    <!-- <td>
                                       @php
                                          $subPackages = \App\Models\LabProfile::where('id', $result->parent_id)->first();
                                       @endphp
                                       {{ $subPackages->profile_name ?? '--' }}
                                    </td> -->
                                    @endif
                                    <td>{{ $result->labsTestsProfiles->count() }}</td> 
                                    @if(Auth::user()->roles->contains(1))
                                    <td>
                                       {{ isset($result->lab_name) ? $result->lab_name : 'Diagnomitra' }}
                                    </td>
                                    @endif
                                    <td>
                                       {{ number_format($result->amount, 2) }}
                                    </td>
                                    <td>
                                       <a class="btn btn-primary btn-sm" href="{{route('profile.show', $result->id)}}">
                                          <i class="fa fa-eye" aria-hidden="true"></i>
                                       </a>
                                       @if(Auth::user()->roles->contains(1) || Auth::user()->id == $result->lab_id)
                                       <a class="btn btn-primary btn-sm" href='{{route('profile.edit', $result->id)."?url=$url"}}'>
                                          <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                       </a>
                                       <a onclick="return confirm('Are you sure you want to delete this item?');" href="{{url('profile-destroy',$result->id)}}" 
                                       <button class="btn btn-danger btn-sm" type="submit">
                                          <i class="fa fa-trash"  aria-hidden="true" style="color:#fff"></i>
                                       </button>
                                       </a>
                                       @endif
                                    </td>
                                 </tr>
                                 @empty
                                 <tr>
                                    <td colspan="8" class="text-center">No Data Found</td>
                                 </tr>
                                 @endforelse
                              </tbody>
                           </table>
                           <div id="pagination">{{ $data->links() }}</div>
                        </form>
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
         var value = $(this).attr('data');
         if ($(this).is(':checked')) {
            $("#checked_"+value).val(1);
         } else {
            $("#checked_"+value).val(0);
         }
      });
   });
   $(document).ready(function() {
      setTimeout(function(){
         $("div.alert").remove();
      }, 3000 ); // 3 secs
   });
</script>
<script>
    function fetchLabProfiles(labId) {
        if (labId) {
            $.ajax({
                url: '/get-lab-profiles/' + labId,
                type: 'GET',
                success: function(data) {
                  console.log(data);  // Check if profiles and counts are being returned

                    updateProfileTable(data.profiles);
                },
                error: function(xhr) {
                    console.error("Error fetching profiles:", xhr);
                }
            });
        } else {
            updateProfileTable([]);
        }
    }

    function updateProfileTable(profiles) {
        var tableBody = $('tbody');
        tableBody.empty();

        if (profiles.length > 0) {
        profiles.forEach(function(profile, index) {
            // Handle undefined fields by providing a fallback (e.g., '--')
            var profileName = profile.profile_name || '--';
            var totalTests = profile.labs_tests_profiles_count || 0;  // Use the count field
            var labName = profile.lab_name || '--';
            var amount = profile.amount !== undefined ? profile.amount.toFixed(2) : '--';

            var row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${profileName}</td>
                    <td>${totalTests}</td>  <!-- Display total test count here -->
                    <td>${labName}</td>
                    <td>${amount}</td>
                    <td>
                        <a class="btn btn-primary btn-sm" href="/profile/${profile.id}">
                            <i class="fa fa-eye" aria-hidden="true"></i>
                        </a>
                        <a class="btn btn-primary btn-sm" href="/profile/edit/${profile.id}">
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                        </a>
                        <a onclick="return confirm('Are you sure you want to delete this item?');" href="/profile-destroy/${profile.id}" class="btn btn-danger btn-sm">
                            <i class="fa fa-trash" aria-hidden="true" style="color:#fff"></i>
                        </a>
                    </td>
                </tr>`;
            tableBody.append(row);
        });
    } else {
        tableBody.append('<tr><td colspan="6" class="text-center">No Data Found</td></tr>');
    }
}
</script>
@endsection
