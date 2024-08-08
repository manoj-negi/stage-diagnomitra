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
                    <form action="{{route('package.store')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" value="{{$data->id ?? ''}}">
                        <div class="row">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="basic-default-fullname">Package Name <span class="text-danger">*</span></label>
                                        <input type="text" name="package_name" class="form-control" value="{{ old('package_name', isset($data) && isset($data->package_name) ? $data->package_name : '') }}" placeholder="Package Name" required />
                                    </div>
                                    @error('package_name')
                                    <div class="validationclass text-danger pt-2">{{$message}}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="basic-default-fullname">Select Main Package <span class="text-danger">*</span></label>
                                        <!-- <input type="text" name="parent_id" class="form-control" value="{{ old('package_name', isset($data) && isset($data->package_name) ? $data->package_name : '') }}" placeholder="Package Name" required /> -->
                                        <select name="parent_id" id="parent_id" class="form-control" 
                                            @if(Auth::user()->roles->contains(4)) required @endif>
                                            <option value="">Select Main Package</option>
                                            @foreach($packageName as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ old('parent_id', isset($data) && $data->parent_id == $value->id) ? 'selected' : '' }}>
                                                    {{ $value->package_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('parent_id')
                                    <div class="validationclass text-danger pt-2">{{$message}}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="basic-default-fullname">Amount <span class="text-danger">*</span></label>
                                        <input type="text" name="amount" class="form-control" value="{{ old('amount', isset($data) && isset($data->amount) ? $data->amount : '') }}" placeholder="Amount" required />
                                    </div>
                                    @error('amount')
                                    <div class="validationclass text-danger pt-2">{{$message}}</div>
                                    @enderror
                                </div>
                                @if(Auth::user()->roles->contains(1))
                                @else(Auth::user()->roles->contains(4))
                                <input type="hidden" name="lab_id" value="{{ auth()->id() }}" required />
                                @endif
                                <div class="form-group col-6 mb-3 {{ $errors->has('profile_id') ? 'has-error' : '' }}">
                                    <label class="form-label" for="profile_id">{{ __('Package Profile') }} </label>
                                    <select name="profile_id[]" id="profileData" class="select2Profile form-control profile_id" multiple="multiple">
                                        @foreach($profiles ?? [] as $item)
                                        <option value="{{ $item->id }}" {{isset($data->getChilds) && in_array($item->id, old('profile_id', $data->getChilds->pluck('id')->toArray())) ? 'selected' : '' }}>
                                            {{ $item->package_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('profile_id'))
                                    <p class="help-block">
                                        {{ $errors->first('profile_id') }}
                                    </p>
                                    @endif
                                </div>

                                @if(Auth::user()->roles->contains(1))
                                <div class="form-group col-6 mb-3 {{ $errors->has('image') ? 'has-error' : '' }}">
                                    <label for="image" class="form-label">Image<span
                                            class="text-danger">*</span></label>
                                    <input type="file" name="image" class="form-control" />

                                    @error('image')
                                    <span class="validationclass text-danger"
                                        style="position: absolute;top: 90px;">{{$message}}</span>
                                    @enderror
                                </div>
                                @endif
                                <div class="col-md-6">
                                    @if(isset($data) && !empty($data->image))
                                    <img class="img-fluid rounded my-4"
                                        src="{{url('uploads/package',$data->image ?? '')}}" height="80px" width="160px"
                                        alt="User avatar">
                                    @endif
                                </div>
                                <div class="col-md-6 "></div>
                                @if(Auth::user()->roles->contains(1))
                                <div class="col-md-6 ">
                                    <div class="d-flex align-items-center">
                                        <div class="lb mt-3">
                                            <label class="form-label">Lifestyle Package<span
                                                    class="text-danger">*</span></label>
                                        </div>
                                        <div class="in">
                                            <input class="form-check-input ms-2 mt-0" type="checkbox"
                                                name="is_lifestyle" {{ isset($data) && $data->is_lifestyle==true ?
                                            'checked' : '' }} value="1">
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if(Auth::user()->roles->contains(1))
                                <div class="col-md-6 ">
                                    <div class="d-flex align-items-center">
                                        <div class="lb mt-3">
                                            <label class="form-label">Frequently Booking Package<span
                                                    class="text-danger">*</span></label>
                                        </div>
                                        <div class="in">
                                            <input class="form-check-input ms-2 mt-0" type="checkbox"
                                                name="is_frequently_booking" {{ isset($data) &&
                                                $data->is_frequently_booking==true ? 'checked' : '' }} value="1">
                                        </div>
                                    </div>
                                </div>
                                @endif



                            </div>


                            <div class="row">
                                <div class="col-md-6">

                                    <a href="{{route('package.index')}}" class="btn btn-warning btn-sm">Back</a>
                                    <button type="submit" value="submit" class="btn btn-primary btn-sm">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
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
      $('.select2Profile').select2({
         ajax: {
            url: "{{ url('/get-package-profile') }}",
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
         placeholder: 'Search for an Profile...',
         minimumInputLength: 1, 
         escapeMarkup: function (markup) { return markup; }, 
         templateResult: formatItem, 
         templateSelection: formatItemSelection
      });
      
   });
</script>
@endsection
@endsection
