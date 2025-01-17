@extends('layouts.adminCommon')
@section('content')
<div class="container">
    <div class="row"> 
    <div class="col-md-12 text-right">
        <a class="btn btn-primary btn-sm my-3" href="{{route('appointments.create')}}">Create</a>
       @if ($message = Session::get('msg'))
                <div class="alert alert-danger alert-dismissible text-left mt-2 alertmsg">
                    <button type="button" class="close" data-dismiss="alert">×</button> 
                        <strong>{{ $message }}</strong>
                </div>
                @endif
    </div>
    <div class="col-md-12">
        <div class="card">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-6">
                    </div>
                    <div class="col-md-6 ">
                    <div class="paging-section justify-content-end">
                     <form class="index-form">
                            <div class="search_bar d-flex">
                                <select class="form-select text-muted mr-1" onchange="document.querySelector('.index-form').submit()" id="filter" name="filter">
                                    <option selected="true" disabled="disabled" >{{__('Select Status')}}</option>
                                    <option class="text-dark" value="1" {{request()->get('filter')=='1'?'selected':''}} >Active</option>
                                    <option class="text-dark" value="0" {{request()->get('filter')=='0'?'selected':''}}>De-Active</option>
                                </select >
                                <input type="" id="search" name="search" placeholder="Search" class="form-control" value ="{{(request()->get('search') != null)? request()->get('search'):''}}" placehoder="Search"/>
                                <button type="submit" class="form-control src-btn"><i class="fa fa-search" aria-hidden="true"></i></button>
                                <a class="form-control  src-btn" href="{{route('appointments.index')}}"><i class="fa fa-rotate-left"></i></a>
                            </div>
                        </form>
                    </div>
                    </div>
                </div>

                <table class="table table-responsive tabel-hover">
                    <tr>
                        <th>S.No</th>
                        <th>Doctor Name</th>
                        <th>patient Name</th>
                        <th>time</th>
                        <th>date</th>
                        <th>Status</th>
                        <th>Action</td>
                    </tr>
                   

                    @foreach($data as $key=> $result)
                    <tr>
                        <td>{{$data->firstItem() + $key ??''}}</td>
                        <td>{{$result->doctor->name}}</td>
                        <td>{{$result->patient->name}}</td>
                        <td>{{$result->time}}</td>
                        <td>{{$result->date}}</td>
                        <td><span class="badge badgess">{{$result->status=="0"?'De-Active':'Active'}}</span></td>
                        <td>
                       <form action="{{route('appointments.destroy',$result->id)}}" method="post" style="display:inline-block">
                                @csrf
                                @method('delete')
                                <button class="btn btn-danger show_confirm btn-sm" type="submit"><i class="fa fa-trash"
                                        aria-hidden="true"></i></button>
                        
                        </form>
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
            <div id="pagination">{{{ $data->links() }}}</div>
        </div>
    </div>
</div>
</div>
<script>
    $(document).ready(function() {
        $('#pagination').on('change', function() {
    
            var form = $(this).closest('form');
            $form.find('input[type=submit]').click();
            console.log($form);
            
  });
    setTimeout(function(){
       $("div.alert").remove();
    }, 3000 ); // 3 secs

    
});

</script>


@endsection

