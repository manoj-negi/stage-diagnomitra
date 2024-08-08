@extends('layouts.adminCommon')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
            <div class="card mb-4">
   <!-- Account -->
   <div class="card-body">
      <div class="row">
         <div class="mb-3 col-md-12">
            <h5 class="mb-4">Profile Details</h5>
          
            <table class="table">
               <tbody> 
               <tr>
                     <th width="80">Profile Name</th>
                     <td> {{$data->package_name ??''}}</td>
                  </tr> 
                  <tr>
                     <th width="80">Profile Amount</th>
                     <td>
                    @if(isset($data->getChilds) && $data->getChilds->isNotEmpty())
                    ₹{{ $data->getChilds->sum('amount') }}
                    @else
                        -
                    @endif
                </td>

                   

                  </tr> 
               
               </tbody>
            </table>
           
         </div>
        
         <table id="example" class="display" style="width:100%">
        <thead>
        <tr>
                <th>#</th> 
                <th>Test Name</th> 
                <th>Test Amount</th> 

                <th> Action</th> 
              
            </tr>
        </thead>
        <tbody>
            <?php $counter = 1; ?>
            @foreach($data->getChilds ?? [] as $key => $item)
                <tr>
                  
                    <td>{{$counter++}}</td>
                    <td> {{$item->package_name ?? ''}}</td> 
                    <td> ₹{{$item->amount ?? ''}}</td>
                    <td>
                    <a href="{{route('lab-test.edit', $item->id)}}" class="btn btn-primary btn-sm">
                   <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
              </a>
              @if(Auth::user()->roles->contains(1))     
<a href='{{url("deleted/$data->id/$item->id")}}'><button type="submit" class="text-white btn btn-danger btn-sm"> 
        <i class="fa fa-trash" aria-hidden="true"></i>
  </button></a>
  @endif  

</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex pt-3">
                            <a href="{{route('profile.index')}}" class="btn btn-warning btn-sm mb-3">Back</a>&nbsp;
                           
                        </div>
</div>
</div>
</div></div>


<script>
        $(document).ready(function() {
            $('#example').DataTable(); // Initialize DataTable
        });
    </script>
@endsection
