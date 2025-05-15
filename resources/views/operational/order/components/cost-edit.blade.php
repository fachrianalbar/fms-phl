<div class="row g-3 mt-3">
    @include('partials.alert')
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
            <input class="form-control" type="text" id="nominal" placeholder="Nominal" oninput="formatAngka(this)">
        </div>
    </div>

    <div class="col-12 mb-5">
        <button class="btn btn-primary" type="button" id="addButton">Add Component</button>
    </div>
</div>

<table class="table table-bordered dt-responsive table-responsive nowrap" id="dt">
    <thead>
        <tr>
            <th>#</th>
            <th>No</th>
            <th>Component Name</th>
            <th>Component Type</th>
            {{-- <th>Description</th> --}}
            <th>Nominal</th>
        </tr>
    </thead>
    <tbody>

        @php
            $i = 1;
        @endphp
        @foreach ($route->routeDetail as $item)
            <tr>
                <td></td>
                <td>{{ $i++ }}</td>
                <td>{{ $item->costComponent->name }}</td>
                <td>Mandatory</td>
                {{-- <td>-</td> --}}
                <td>
                    @php
                        $price = 0;
                        if ($item->amount != 0) {
                            $price += $item->amount;
                        }

                        if ($item->percentage) {
                            $route = App\Models\Data\Route::where('code', $item->routeCode)->first();

                            $price = $route->price * ($item->percentage / 100);
                        }
                    @endphp
                    {{ 'Rp ' . number_format($price, 0, ',', '.') }}
                </td>
            </tr>
        @endforeach
        @if ($route->routeTypeCode == 'TONASE')
            <tr>
                <td></td>
                <td>{{ $i++ }}</td>
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

                    <ul class="action">
                        <li class="delete"><a href="javascript:deleteCost('{{ $item->id }}')"><i
                                    class="icon-trash"></i></a>
                        </li>
                    </ul>

                </td>
                <td>{{ $i++ }}</td>
                <td>{{ $item->costComponent->name }}</td>
                <td>{{ $item->type }}</td>
                {{-- <td>{{ $item->description }}</td> --}}
                <td>{{ 'Rp ' . number_format($item->nominal, 0, ',', '.') }}</td>

            </tr>
        @endforeach
        <!-- New rows will be added here -->
    </tbody>
</table>

<script>
    let index = document.getElementById('dt').getElementsByTagName('tr')
        .length - 1; // Initialize index for unique identifiers

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
        const newRow = table.insertRow();


        newRow.innerHTML = `
         <td>
        <ul class="action">
            <li class="delete"><a href="javascript:removeRow(${index})"><i class="icon-trash"></i></a></li>
        </ul>
    </td>
    <td>
        ${index + 1}
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
       Rp  ${nominal}
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
