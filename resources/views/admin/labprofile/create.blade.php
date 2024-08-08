@extends('layouts.adminCommon')
@section('content')
<div class="container">
   <div class="row justify-content">
      <div class="col-12 pt-5 ">
      @if (session('msg'))
    <div class="validationclass text-danger pt-2">{{ session('msg') }}</div>
@endif
         <div class="card">
            <div class="card-body">
               <form action="{{route('profile.store')}}" method="post" enctype="multipart/form-data">
                  @csrf
                  <input type="hidden" name="id" value="{{$data->id ?? ''}}">
                  <input type="hidden" name="url" value="{{ app('request')->input('url') }}">
                  <div class="row">
                     <div class="row">
                        <div class="col-md-6">
                           <div class="mb-3">
                              <label class="form-label" for="title">Title<span class="text-danger">*</span></label>
                              <input type="text" name="title" id="title" class="form-control"
                                 value="{{ old('title', isset($data) && isset($data->package_name) ? $data->package_name : '') }}"
                                 placeholder="Title"/>
                           </div>
                           @error('title')
                           <div class="validationclass text-danger pt-2">{{$message}}</div>
                           @enderror
                        </div>
                        @if(Auth::user()->roles->contains(4))
                        <div class="col-md-6">
                           <div class="mb-3">
                              <label class="form-label" for="sub_profile">Select Main Profile<span class="text-danger">*</span></label>
                              <!-- <input type="text" name="sub_profile" id="sub_profile" class="form-control"
                                 value="{{ old('sub_profile', isset($data) && isset($data->package_name) ? $data->package_name : '') }}"
                                 placeholder="Sub Title"/> -->
                                 <select required name="parent_id" class="form-control" id="parent_id">
                                    <option value="">Select Main Profile</option>
                                    @foreach($subprofile as $value)
                                    <option value="{{$value->id ?? ''}}" {{ old('parent_id', isset($data) && $data->parent_id == $value->id) ? 'selected' : '' }}>{{$value->package_name ?? ''}}</option>
                                    @endforeach
                                 </select>
                           </div>
                           @error('sub_profile')
                           <div class="validationclass text-danger pt-2">{{$message}}</div>
                           @enderror
                        </div>
                        @endif
                        <div class="col-md-6">
                           <div class="mb-3">
                              <label class="form-label" for="amount">Amount<span
                                    class="text-danger">*</span></label>
                              <input type="number" id="amount" name="amount" class="form-control"
                                 value="{{ old('amount', isset($data) && isset($data->amount) ? $data->amount : '') }}"
                                 placeholder="Amount"/>
                           </div>
                           @error('amount')
                           <div class="validationclass text-danger pt-2">{{$message}}</div>
                           @enderror
                        </div>
                        @if(Auth::user()->roles->contains(1))
                        <div class="col-md-6">
                           <label for="lab_id" class="form-label">Select lab<span class="text-danger">*</span></label>
                           <select name="lab_id" class="mb-2 mr-sm-2 form-select test_id labId" id="lab_id"
                              <option value="">Select lab</option>
                              <option value="1" {{ old('lab_id', isset($data) && $data->lab_id == '1' ? 'selected' : '')
                                 }}>Diagno Mitra</option>
                              @foreach($hospitals as $key => $hospital)
                              <option value="{{ $hospital->id }}" {{ old('lab_id', isset($data) && $data->lab_id ==
                                 $hospital->id ? 'selected' : '') }}>
                                 {{ $hospital->name }}
                              </option>
                              @endforeach
                           </select>
                        </div>
                        @else(Auth::user()->roles->contains(4))
                        <input type="hidden" name="lab_id" value="{{ auth()->id() }}" class="labId"/>
                        @endif
                        <div class="form-group col-6 mb-3 {{ $errors->has('test_id') ? 'has-error' : '' }}">
                           <label class="form-label" for="testsData">{{ __('Test Name') }} <span class="text-danger">*</span></label>
                           <!-- <select name="test_id[]" class="select2test form-control test_id tests" id="testsData" multiple="multiple"
                              @foreach($tests ?? [] as $item)
                              <option value="{{ $item->id }}" {{isset($data->getChilds) && in_array($item->id, old('test_id', $data->getChilds->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $item->package_name }}</option>
                              @endforeach
                           </select> -->
                           <select name="test_id[]" class="select2test form-control test_id tests" id="testsData" multiple="multiple">
                           @foreach($tests ?? [] as $item)
                              <option value="{{ $item->id }}" {{ isset($data->getChilds) && in_array($item->id, old('test_id', $data->getChilds->pluck('id')->toArray())) ? 'selected' : '' }}>
                                    {{ $item->package_name }}
                              </option>
                           @endforeach
</select>



                 @if($errors->has('test_id'))
                           <p class="help-block">
                              {{ $errors->first('test_id') }}
                           </p>
                           @endif
                        </div>
                        @if(Auth::user()->id ==1)
                        <div class="form-group col-6 mb-3 {{ $errors->has('image') ? 'has-error' : '' }}">
                           <label for="image" class="form-label">Image</label>
                           <input type="file" name="image" id="image" class="form-control" />
                           @error('image')
                           <span class="validationclass text-danger"
                              style="position: absolute;top: 90px;">{{$message}}</span>
                           @enderror
                        </div>
                        <div class="col-md-6">
                           @if(isset($data) && !empty($data->image))
                           <img class="img-fluid rounded my-4" src="{{url('uploads/package',$data->image ?? '')}}"
                              height="80px" width="160px" alt="User avatar">
                           @endif
                        </div>
                        @endif
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-6">
                        <a href="{{route('profile.index')}}" class="btn btn-warning btn-sm">Back</a>
                        <button type="submit" value="submit" class="btn btn-primary btn-sm">Submit</button>
               </form>
            </div>
         </div>
      </div>
   </div>
</div>
</div>
<style>
   .modal-dialog {
      background-color: white !important;
      border-radius: 8px !important;
   }
</style>
@endsection
@section('js')
<script>
   $(document).ready(function($){
      function formatItem(item) {
         console.log('item', item);
          if (item.loading) return item.text;
          var markup = "<div class='select2-result-item'>";
          markup += item.package_name;
          markup += "</div>";

          return markup;
      }
      function formatItemSelection(repo) {
         return repo.package_name || repo.text;
      }
      $('.select2test').select2({
         ajax: {
            url: "{{ url('/get-test') }}",
            dataType: 'json',
            delay: 250,
            data: function (params) {
               return {
                  q: params.term,
                  page: params.page || 1
               };
            },
            processResults: function (data, params) {
               params.page = params.page || 1;
               return {
                  results: data.items,
                  pagination: {
                        more: (params.page * 10) < data.total_count 
                  }
               };
            },
            cache: true
         },
         placeholder: 'Search for an test...',
         minimumInputLength: 1, 
         escapeMarkup: function (markup) { return markup; }, 
         templateResult: formatItem, 
         templateSelection: formatItemSelection
      });
      
   });
</script>

<!-- <script>
$(document).ready(function(){
    $('#parent_id').change(function(){
        var parentId = $(this).val();
        console.log(parentId);
      //   console.log("Selected Parent ID:", parentId); // Log the selected value to the console
        $.ajax({
            url: "{{ url('/get-tests-by-parent') }}",
            type: 'GET',
            data: { parent_id: parentId },
            success: function(response){
               console.log(response);
                $('#testsData').html(response);
            }
        });
    });
});
</script> -->
<script>
$(document).ready(function(){
    $('#parent_id').change(function(){
        var parentId = $(this).val();
        console.log("Selected Parent ID:", parentId); // Log the selected value to the console

        $.ajax({
            url: "{{ url('/get-tests-by-parent') }}",
            type: 'GET',
            data: { parent_id: parentId },
            success: function(response){
                console.log(response);
                var select = $('#testsData');
                select.empty(); // Clear the current options

                // Append the new options from the response
                $.each(response, function(index, item) {
                    console.log("Appending item:", item); // Log each item
                    select.append($('<option>', {
                        value: item.id,
                        text: item.package_name
                    }));
                });

                // If using Select2, refresh the plugin to show the new options
                if (select.hasClass('select2test')) {
                    select.select2();
                }
            },
            error: function(xhr, status, error) {
                console.error("An error occurred while fetching tests:", error);
            }
        });
    });
});
</script>



@endsection