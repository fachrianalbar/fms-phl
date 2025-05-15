    <div class="row g-3 mt-3">
        <div class="row">
            <div class="col-md-6">
                <label class="form-label" for="name">Component Name </label>
                {{-- <input class="form-control" type="text" id="componentName" placeholder="Component Name"> --}}
                <select class="js-example-basic-single" id="componentName">
                    <option selected="" disabled="" value="">Choose...</option>
                    @foreach ($component as $item)
                        <option value="{{ $item->code }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 position-relative">
                <label class="form-label" for="fleetDriverCode">Component Type </label>
                <select class="js-example-basic-single" id="componentType">
                    <option selected="" disabled="" value="">Choose...</option>
                    @foreach ($orderCost as $item)
                        <option value="{{ $item->value }}">{{ $item->value }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mt-4">
            {{-- <div class="col-md-6">
                <label class="form-label" for="description">Description</label>
                <input class="form-control" type="text" id="description" placeholder="Description">
            </div> --}}

            <div class="col-md-6">
                <label class="form-label" for="nominal">Nominal</label>
                <input class="form-control" type="text" id="nominal" oninput="formatAngka(this)"
                    placeholder="Nominal">
            </div>
        </div>

        <div class="col-12">
            <button class="btn btn-primary mb-4" type="button" id="addButton">Add Component</button>
        </div>
    </div>

    <table class="table table-bordered dt-responsive table-responsive nowrap" id="dt">
        <thead>
            <tr>
                <th>Action</th>
                <th>Component Name</th>
                <th>Component Type</th>
                {{-- <th>Description</th> --}}
                <th>Nominal</th>
            </tr>
        </thead>
        <tbody id="component-list">
            <!-- New rows will be added here -->
        </tbody>
    </table>

    <script>
        let index = 0; // Initialize index for unique identifiers

        document.getElementById('addButton').addEventListener('click', function() {
            const componentName = document.getElementById('componentName').value;
            const componentNameElement = document.getElementById('componentName');
            const componentNameText = componentNameElement.options[componentNameElement.selectedIndex].text;
            const componentType = document.getElementById('componentType').value;
            // const description = document.getElementById('description').value;
            const nominal = document.getElementById('nominal').value;



            // Validate input fields
            if (componentName === '' || componentType === '' || nominal === '') {

                swal({
                    title: "Warning",
                    text: "Please fill in all required fields: Component Name, Component Type, and Nominal.",
                    icon: "warning",
                })
                return;
            }

            // Create a new row and structure it according to your example
            const table = document.getElementById('dt').getElementsByTagName('tbody')[0];
            //         const newRow = table.insertRow();

            //         newRow.innerHTML = `
        //     <td>
        //         <input type="hidden" name="componentName[]" value="${componentName}">
        //         ${componentName}
        //     </td>
        //     <td>
        //         <input type="hidden" name="componentType[]" value="${componentType}">
        //         ${componentType}
        //     </td>
        //     <td>
        //         <input type="hidden" name="description[]" value="${description}">
        //         ${description}
        //     </td>
        //     <td>
        //          <input type="hidden" name="nominal[]" value="${nominal}">
        //         ${nominal}
        //     </td>
        //     <td>
        //         <ul class="action">
        //             <li class="delete"><a href="javascript:removeRow(${index})"><i class="icon-trash"></i></a></li>
        //         </ul>
        //     </td>
        // `;

            const componentList = document.getElementById('component-list');

            if (index == 0) {
                componentList.innerHTML = ''; // Clear existing list
            }

            let row = `
             <td>
            <ul class="action">
                <li class="delete"><a href="javascript:removeRow(${index})"><i class="icon-trash"></i></a></li>
            </ul>
        </td>
        <td>
            <input type="hidden" name="componentName[]" value="${componentName}">
            ${componentNameText}
        </td>
        <td>
            <input type="hidden" name="componentType[]" value="${componentType}">
            ${componentType}
        </td>
     
        <td>
             <input type="hidden" name="nominal[]" value="${nominal}">
            ${nominal}
        </td>
       
    `;
            componentList.insertAdjacentHTML('beforeend', row);

            // Increment the index for the next row
            index++;

            // Clear input fields after adding
            // document.getElementById('componentName').value = '';
            // document.getElementById('componentType').value = '';
            // document.getElementById('description').value = '';
            document.getElementById('nominal').value = '';
        });

        // Function to remove a row
        function removeRow(rowIndex) {
            const table = document.getElementById('dt').getElementsByTagName('tbody')[0];
            table.deleteRow(rowIndex);
        }
    </script>
