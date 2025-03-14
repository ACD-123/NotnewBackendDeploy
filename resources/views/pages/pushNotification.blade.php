@extends('../layout')



@section('title','Dashboard')
@push('css')

@endpush

@section('content')
<div class="dash-set">
    <h3 id="Notification_head">New Push Notifications</h3>
</div>
<div class="form-box">
    <form action="{{route('push.promotion')}}" method="POST" enctype="multipart/form-data">
    @csrf
        <div class="form-group">
            <input type="text" class="form-control" name="title" placeholder="Title" required>
        </div>

        <div class="form-group">
            <textarea name="message" placeholder="Send Message" class="form-control" id="" cols="20" rows="7" required></textarea>
        </div>
        <br>
        <div class="form-group">
            <input type="text" class="form-control" name="url" placeholder="URL" required>
        </div>
        <div class="form-group">
            <label for="">GUID (Product Or Seller)</label>
            <input type="text" class="form-control" name="guid" required>
        </div>
        <div class="form-group">
            <label for="">Type (Product Or Seller)</label>
            <select name="type" id="type" class="form-control" required>
                <option value="">Select Type</option>
                <option value="store">Store</option>
                <option value="product">Product</option>
            </select>
        </div>
        <div class="form-group">
                                <label for="Upload Picture (Optional)">Upload Picture (Optional)</label>
                                    <div class="upload-box">
                                        <input type="file" id="fileInput" name="image" class="file-input" />
                                        <div class="upload-content">
                                            <img src="/public/images/mdi_images-outline.png" alt="Upload" class="upload-icon">
                                            <span class="upload-text">Add Images</span>
                                            <a href="#" class="upload-link">Browse</a>
                                        </div>
                                    </div>
                                    <div id="file-info" style="display: block; margin:15px 0;">
                                        <img id="preview" style="width: 120px; height: 100px; border-radius: 7px;" src="/public/images/mdi_images-outline.png" alt="Image Preview" />
                                    </div>
                            </div>

        <button type="submit" class="btn btn-primary form_btn">Done</button>



    </form>

</div>

@endsection

@push('js')

<script>
    document.getElementById('fileInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const fileName = file.name;
            
            const fileReader = new FileReader();
            
            fileReader.onload = function(event) {
                document.getElementById('preview').src = event.target.result;
            };

            if (file) {
                fileReader.readAsDataURL(file);
            }
        });

</script>



@endpush