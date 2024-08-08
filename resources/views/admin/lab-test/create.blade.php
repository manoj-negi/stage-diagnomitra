@extends('layouts.adminCommon')

@section('content')
<div class="container">
   <div class="row justify-content">
      <div class="col-12 pt-5">
         @if (session('msg'))
            <div class="validationclass text-danger pt-2">{{ session('msg') }}</div>
         @endif
         <div class="card">
            <div class="card-body">
               <form action="{{ route('lab-test.store') }}" method="post" enctype="multipart/form-data">
                  @csrf
                  <input type="hidden" name="id" value="{{ $data->id ?? '' }}">
                  <input type="hidden" name="url" value="{{ app('request')->input('url') }}">

                  <div class="row">
                     <div class="col-md-6 mb-3">
                        <label class="form-label" for="test_name">Test Name<span class="text-danger">*</span></label>
                        <select name="test_name" id="test_name" style="width: 100%;"></select>
                        @error('test_name')
                           <div class="validationclass text-danger pt-2">{{ $message }}</div>
                        @enderror
                     </div>

                     <div class="col-md-6 mb-3">
                        <label class="form-label" for="amount">Amount<span class="text-danger">*</span></label>
                        <input type="number" id="amount" name="amount" class="form-control"
                           value="{{ old('amount', $data->amount ?? '') }}"
                           placeholder="Amount"/>
                        @error('amount')
                           <div class="validationclass text-danger pt-2">{{ $message }}</div>
                        @enderror
                     </div>

                     @if(Auth::user()->roles->contains(1))
                     <div class="col-md-6 mb-3">
                        <label class="form-label" for="lab_id">Lab<span class="text-danger">*</span></label>
                        <select name="lab_id" class="form-control" id="lab_id">
                           <option value="">Select lab</option>
                           @foreach($labs as $item)
                              <option value="{{ $item->id }}" {{ old('lab_id', $data->lab_id ?? '') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                           @endforeach
                        </select>
                        @error('lab_id')
                           <div class="validationclass text-danger pt-2">{{ $message }}</div>
                        @enderror
                     </div>
                     @endif

                     <div class="col-md-12 mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea id="ckeditor" name="description" class="form-control">{{ old('description', $data->description ?? '') }}</textarea>
                        @error('description')
                           <p class="validationclass text-danger pt-2">{{ $message }}</p>
                        @enderror
                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <a href="{{ route('lab-test.index') }}" class="btn btn-warning btn-sm">Back</a>
                        <button type="submit" value="submit" class="btn btn-primary btn-sm">Submit</button>
                     </div>
                  </div>
               </form>
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
$(document).ready(function() {
    function formatItem(item) {
        return item.text;
    }

    function formatItemSelection(item) {
        return item.text;
    }

    $('#test_name').select2({
        ajax: {
            url: "{{ url('/lab-test/autocomplete') }}",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    term: params.term,
                    page: params.page || 1
                };
            },
            processResults: function(data, params) {
                params.page = params.page || 1;
                return {
                    results: data,
                    pagination: {
                        more: (params.page * 10) < data.total_count
                    }
                };
            },
            cache: true
        },
        placeholder: 'Select a test...',
        minimumInputLength: 1,
        tags: true,
        createTag: function(params) {
            var term = $.trim(params.term);
            if (term === '') {
                return null;
            }

            return {
                id: term,
                text: term,
                new: true
            };
        },
        escapeMarkup: function(markup) { return markup; },
        templateResult: formatItem,
        templateSelection: formatItemSelection
    }).on('select2:select', function(e) {
        var data = e.params.data;
        if (data.new) {
            $.ajax({
                url: "{{ route('lab-test.store') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    test_name: data.text,
                    amount: $('#amount').val(),
                    lab_id: $('#lab_id').val()
                },
                success: function(response) {
                    $('#test_name').append(new Option(response.test_name, response.id, true, true)).trigger('change');
                },
                error: function(xhr) {
                    console.error('Error creating new test:', xhr.responseText);
                }
            });
        }
    });
});
</script>
@endsection
