@extends('layouts.welcome')


@section('content')
    {{-- <product-index inline-template>

    </product-index> --}}
   
    <table class="table">
        <thead>
            <tr>
                <th>Identifiant</th>
                <th>Groupe</th>
                <th>Nom</th>
                <th>SKU</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td scope="row">{{ $product->product_id }}</td>
                    <td>{{ $product->handle }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->price }}</td>

                </tr>
            @endforeach
            
        </tbody>
    </table>
    
@endsection