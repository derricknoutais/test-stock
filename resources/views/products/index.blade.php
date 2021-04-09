@extends('layouts.welcome')


@section('content')
    <product-index :products_prop="{{ $products }}" :fournisseurs_prop="{{ $fournisseurs }}" inline-template>
        <div class="tw-container tw-mx-auto tw-mt-10">
            <form action="/product" method="GET" class="tw-flex tw-w-1/3">
                <select name="handle" class="form-control">
                    @foreach ($handles as $handle)
                        <option value="{{ $handle->id }}">{{ $handle->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary tw-ml-5 ">Filtrer</button>
            </form>
            <table class="table">
                <thead>
                    <tr>
                        <th>Identifiant</th>
                        <th>Groupe</th>
                        <th>Nom</th>
                        <th>SKU</th>
                        <th>Price</th>
                        <th>Fournisseurs</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in products">
                        <td scope="row">@{{ product.id }}</td>
                        <td>@{{ product.handle }}</td>
                        <td>@{{ product.name }}</td>
                        <td>@{{ product.sku }}</td>
                        <td>@{{ product.price }}</td>
                        <td class="tw-w-64">
                            <multiselect v-model="product.fournisseurs" label="nom"
                                track-by="nom" :options="fournisseurs" :multiple="true" :taggable="true" @input="addTag(product)"></multiselect>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </product-index>

@endsection
