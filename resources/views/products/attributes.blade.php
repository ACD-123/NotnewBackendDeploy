@foreach($attributes as $attribute)
    <div class="form-group">
        <label>{{ucfirst($attribute->name)}}</label>
        <br />
        @if( $attribute->type == 'CHECKBOX' )
            <input type="checkbox" checked="{{$defaults[$attribute->id] ?? null}}"  name="attributes[{{$attribute->id}}]" value="{{$defaults[$attribute->id] ?? null}}"/>
        @elseif ( $attribute->type == 'RADIO_GROUP')
            {{--@foreach($attribute->options as $option)--}}
            <input type="radio" checked="{{$defaults[$attribute->id] ?? null}}" name="attributes[{{$attribute->id}}]" value="{{$defaults[$attribute->id] ?? null}}"/>
            {{--@endforeach --}} {{$defaults[$attribute->id] ?? null}}
        @elseif ( $attribute->type == 'TEXT')
            <input class="form-control" name="attributes[{{$attribute->id}}]" value="{{$defaults[$attribute->id] ?? null}}"/>
        @elseif ( $attribute->type == 'SELECT')
            <select class="form-control" name="attributes[{{$attribute->id}}]">
                @foreach($attribute->options as $option)
                    <option value="@if($defaults[$attribute->id] == $option) selected  @endif">{{ $option }}</option>
                @endforeach
            </select>
        @elseif ( $attribute->type == 'CHECKBOX_GROUP')
            @foreach($defaults[$attribute->id] as $attr)
                <input type="checkbox" checked="{{ $attr }}"  name="attributes[{{$attribute->id}}]" value="{{$attr}}"/>
                {{ $attr }}
            @endforeach
        @endif
    </div>
@endforeach
