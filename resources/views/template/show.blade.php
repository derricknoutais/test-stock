@extends('layouts.welcome')

@section('content')

<template-show :template_prop="{{ $template }}" inline-template>

    <section>
        <header class="tw-flex tw-flex-col tw-items-center tw-bg-gray-800 tw-text-white">
            <p class="tw-text-4xl tw-text-bold tw-my-4">Template {{ $template->name }}</p>
            <p class="tw-mb-4">
                Les Templates vous permettent d'enregistrer un groupe de produits que vous pouvez réutiliser dans vos commandes
            </p>

            <div class="tw-flex tw-w-screen tw-justify-center tw-items-center tw--ml-2">
                <div class="tw-w-1/4 tw-mr-4">
                    {{-- <multiselect v-model="selected_product" :options="{{ $products }}" ref="SearchBar" :multiple="true" :searchable="true" :close-on-select="false" :show-labels="false"
                    placeholder="Pick a value" label="name" @input="addProduct()"></multiselect> --}}
                    <multiselect v-model="selected_product" :options="{{ $products }}" :multiple="true" :close-on-select="false" :clear-on-select="false" :preserve-search="true" placeholder="Pick some" label="name" track-by="name" :preselect-first="true"></multiselect>
                </div>

                <button class="tw-btn  tw-shadow-outline tw-leading-none" @click="addProduct()">Ajouter Produit</button>
            </div>



            <p class="tw-text-lg my-4">
                @{{ template.products.length }} 
                @{{ template.products.length <= 1 ? 'Produit' : 'Produits' }}
            </p>
            {{-- <button class="tw-btn" data-toggle="modal" data-target="#modelId">Créer Template</button> --}}
            <!-- Modal -->
        </header>

        <main class="tw-flex tw-justify-center tw-w-screen tw-mt-5">
            <table class="table tw-w-1/2">
                <thead class="thead-inverse">
                    <tr>
                        <th>Nom</th>
                        <th>SKU</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                            <tr v-for="(product, index) in template.products ">
                                <td scope="row">@{{ product.name }}</td>
                                <td>@{{ product.sku }}</td>
                                <td>@{{ product.price }}</td>
                                <td>
                                    <i class="fas fa-times tw-text-red-500 tw-mr-2 tw-cursor-pointer" @click="removeProduct(index)"></i>
                                </td>
                            </tr>    
                        
                    </tbody>
            </table>
        </main>
        
    </section>
    

</template-show>
@endsection