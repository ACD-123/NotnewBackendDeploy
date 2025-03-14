@extends('../layout')



@section('title','Dashboard')
@push('css')
<style>
    .form-control {
        padding: 25px;
        border-radius: 26px;

    }

    .form-box input.form-control {
        margin-bottom: 0;
    }

    label {
        color: #495057;
    }

    .chevron-icon {
        font-size: 20px;
        position: absolute;
        right: 24px;
        top: 30px;
    }

    .dropdown {
        position: relative;
    }

    .form_btn {
        padding: 11px 20px;
        width: unset !important;
    }
</style>
@endpush

@section('content')
<div class="dash-set">

</div>
<div class="form-box">
    <form action="{{route('attribute.store')}}" method="post">
        @csrf
        <div class="form-group">
            <label for="">Name</label>
            <input type="text" class="form-control" name="name" placeholder="Enter name">
        </div>
        <div class="form-group">
            <label for="type">Options</label>
            <input type="text" class="form-control" id="attribute-input" placeholder="Enter Attribute Name" onkeydown="submitAttribute(event)">
        </div>
        <div id="attribute-list" class="mt-2"></div>

        <script>
            function submitAttribute(event) {
                if (event.key === "Enter") {
                    event.preventDefault(); // Prevent form submission on Enter

                    // Get the input field and its value
                    const input = document.getElementById('attribute-input');
                    const attributeName = input.value.trim();

                    // If the input is not empty, create a hidden input for form submission
                    if (attributeName) {
                        const attributeList = document.getElementById('attribute-list');

                        // Create a new div to show the attribute name and cancel icon
                        const newAttribute = document.createElement('div');
                        newAttribute.className = 'attribute-item';

                        // Add the attribute name for display
                        const nameSpan = document.createElement('span');
                        nameSpan.textContent = attributeName;

                        // Create a hidden input to hold the value (for form submission as "options[]")
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'options[]';
                        hiddenInput.value = attributeName;

                        // Add the cancel (delete) icon
                        const cancelIcon = document.createElement('span');
                        cancelIcon.innerHTML = '&times;'; // "×" symbol for cancel
                        cancelIcon.className = 'cancel-icon';
                        cancelIcon.onclick = function() {
                            attributeList.removeChild(newAttribute); // Remove this attribute when the icon is clicked
                        };

                        // Append the name, hidden input, and cancel icon to the new attribute div
                        newAttribute.appendChild(nameSpan);
                        newAttribute.appendChild(hiddenInput);
                        newAttribute.appendChild(cancelIcon);

                        // Append the new attribute div to the list
                        attributeList.appendChild(newAttribute);

                        // Clear the input field for the next entry
                        input.value = '';
                    }
                }
            }
        </script>

        <!-- Optional CSS for styling the attribute list and cancel icon -->
        <style>
            .attribute-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 5px;
                padding: 5px;
                background-color: #f1f1f1;
                border-radius: 4px;
            }

            .cancel-icon {
                cursor: pointer;
                color: red;
                margin-left: 10px;
                font-weight: bold;
            }
        </style>








        <div class="form-group">
            <label for="status">Status</label>
            <div class="dropdown">
                <select name="active" class="form-control" id="status">
                    <option value="1">Active</option>
                    <option value="0">In-Active</option>
                </select>
                <i class="fa fa-chevron-down chevron-icon" id="status-chevron"></i>
            </div>
        </div>







        <button type="submit" class="btn btn-primary form_btn">Submit</button>
</div>





</form>

</div>





</form>

</div>
@endsection

@push('js')

@endpush