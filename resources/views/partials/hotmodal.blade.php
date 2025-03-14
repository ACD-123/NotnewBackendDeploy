<div class="modal fade" id="productshot{{$data->id}}" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Change Hot Status
                    of <strong>{{$data->name}}</strong> </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form style="display: unset"
                      action="{{route("{$route}.update",["id"=>$data->id])}}"
                      method="POST" id="formhot-submit{{$data->id}}">
                    {{ csrf_field()}}
                    <input type="hidden" name="activateOne" value="activateOnlyOne">
                    @csrf
                    {{$data->hot == 1 ? "The {$data->name} is in Hot Daily Deals Uncheck it to remove it" :
                    "The {$data->name} is not in Hot Daily Deals Check it to add it"}}
                    <br>
                    <input type="checkbox" value="1" {{$data->hot == 1 ? 'checked' : ''}} name="checkboxhot"
                           onchange="document.getElementById('formhot-submit{{$data->id}}').submit()"/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close
                </button>
            </div>
        </div>
    </div>
</div>
