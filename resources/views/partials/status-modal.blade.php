<div class="modal fade" id="products{{$data->id}}" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Change Status
                    of <strong>{{$data->name}}</strong> </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form style="display: unset"
                      action="{{route("{$route}.update",$data->id)}}"
                      method="POST" id="form-submit{{$data->id}}">
                    {{ method_field('PATCH') }}
                    {{ csrf_field()}}
                    <input type="hidden" name="activateOne" value="activateOnlyOne">
                    @csrf
                    {{$data->active == 1 ? "The {$data->name} is Active Uncheck it to make it In-active" :
                    "The {$data->name} is In-Active Uncheck it to make it Active"}}
                    <br>
                    <input type="checkbox" value="1" {{$data->active == 1 ? 'checked' : ''}} name="checkbox"
                           onchange="document.getElementById('form-submit{{$data->id}}').submit()"/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close
                </button>
            </div>
        </div>
    </div>
</div>
