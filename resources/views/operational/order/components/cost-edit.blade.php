    <div class="tab-pane fade show active" id="icon-home" role="tabpanel" aria-labelledby="icon-home-tab">

        <div class="row g-3 mt-3">
            @include('partials.alert')
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label" for="name">Component Name </label>

                    <select class="js-example-basic-single" id="componentName">
                        <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                        @foreach ($component as $item)
                            <option value="{{ $item->code }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="description">Description</label>
                    <input class="form-control" type="text" id="description" placeholder="Description">
                </div>
            </div>

            <div class="row mt-4">


                <div class="col-md-6">
                    <label class="form-label" for="nominal">Nominal</label>
                    <input class="form-control" type="text" id="nominal" placeholder="Nominal"
                        oninput="formatAngka(this)">
                </div>
            </div>

            <div class="col-12 mb-5">
                <button class="btn btn-primary mb-4" type="button"
                    id="addButton">{{ __('menu_order.add_component') }}</button>
            </div>
        </div>

        <table class="table table-striped w-100 nowrap" id="dt">
            <thead>
                <tr>
                    <th>#</th>
                    {{-- <th>No</th> --}}
                    <th>Component Name</th>
                    {{-- <th>Component Type</th> --}}
                    <th>Description</th>
                    <th>Nominal</th>
                </tr>
            </thead>
            <tbody>

                @php
                    $i = 1;
                @endphp
                @if ($route->routeTypeCode == 'TONASE')
                    <tr>
                        <td></td>
                        {{-- <td>{{ $i++ }}</td> --}}
                        <td>Bonus Tonase</td>
                        <td>Bonus</td>
                        {{-- <td>-</td> --}}
                        <td>
                            @php
                                $bonus = 0;

                                $bonusTonase = App\Models\Data\TonaseBonus::where('min', '<=', $data->qty)
                                    ->where('max', '>=', $data->qty)
                                    ->first();

                                if ($bonusTonase) {
                                    $bonus += $bonusTonase->value;
                                }
                            @endphp
                            {{ 'Rp ' . number_format($bonus, 0, ',', '.') }}

                        </td>
                    </tr>
                @endif
                @foreach ($cost as $item)
                    <tr>
                        <td>
                            <a href="javascript:deleteCost('{{ $item->id }}')"
                                class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Delete">
                                <i class="mdi mdi-delete fs-14 text-danger"></i>
                            </a>
                        </td>
                        {{-- <td>{{ $i++ }}</td> --}}
                        <td>
                            <input type="hidden" class="form-control" name="componentName[]"
                                value="{{ $item->costComponent->code }}"> {{ $item->costComponent->name }}
                        </td>
                        <td>
                            <input class="form-control" name="description[]" value="{{ $item->description }}">
                        </td>
                        <td>
                            <input class="form-control" name="nominal[]" oninput="formatAngka(this)" type="text"
                                min="1" value="{{ number_format($item->nominal, 0, ',', '.') }}">
                        </td>


                    </tr>
                @endforeach
                <!-- New rows will be added here -->
            </tbody>
        </table>
    </div>

    <script>
        let index = document.getElementById('dt').getElementsByTagName('tr')
            .length - 1; // Initialize index for unique identifiers

        document.getElementById('addButton').addEventListener('click', function() {
            const componentName = document.getElementById('componentName').value;
            const componentNameElement = document.getElementById('componentName');
            const componentNameText = componentNameElement.options[componentNameElement.selectedIndex].text;
            // const componentType = document.getElementById('componentType').value;
            const description = document.getElementById('description').value;
            const nominal = document.getElementById('nominal').value;

            // Validate input fields
            if (componentName === '' || nominal === '') {
                swal({
                    title: "Warning",
                    text: "Please fill in all required fields: Component Name, Component Type, and Nominal.",
                    icon: "warning",
                })
                return;
            }

            let isDuplicate = false;
            document.querySelectorAll('input[name="componentName[]"]').forEach(function(input) {
                if (input.value === componentName) {
                    isDuplicate = true;
                }
            });

            if (isDuplicate) {
                swal({
                    title: "Duplicate Component",
                    text: "Component already exists in the list.",
                    icon: "warning",
                });
                return;
            }

            // Create a new row and structure it according to your example
            const table = document.getElementById('dt').getElementsByTagName('tbody')[0];
            const newRow = table.insertRow();


            newRow.innerHTML = `
         <td>
            <a href="javascript:removeRow(${index})"
            class="btn btn-icon btn-sm bg-danger-subtle me-1"
            data-bs-toggle="tooltip" title="Delete">
                <i class="mdi mdi-delete fs-14 text-danger"></i>
            </a>
        </td>
        <td>
            <input type="hidden" name="componentName[]" value="${componentName}"> ${componentNameText}

        </td>
         <td>
            <input class="form-control"  name="description[]" value="${description}">
        </td>
        <td>
             <input class="form-control"  name="nominal[]" oninput="formatAngka(this)" type="text" min=1  value="${nominal}">
        </td>
   
    `;

            index++;


            // Increment the index for the next row

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
            index--;
        }
    </script>
