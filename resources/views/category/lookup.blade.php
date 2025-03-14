<select id="properties" name="category_id" class="form-control">
    <option value="" selected>Please select...</option>
    @foreach( \App\Models\Category::all() as $category)
        <option value="{{$category->id}}">{{$category->name}}</option>
    @endforeach
</select>
