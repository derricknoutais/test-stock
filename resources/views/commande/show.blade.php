@extends('layouts.welcome')


@section('content')

<commande-show :commande_prop="{{ $commande }}" :products_prop="{{ $products }}"  inline-template>
    <section>

        <header class="tw-flex tw-flex-col tw-items-center tw-bg-gray-800 tw-text-white">
            <p class="tw-text-4xl tw-text-bold tw-mt-6 tw-leading-none">{{ $commande->name }}</p>
            <p class="tw-mt-6 tw-leading-none">
                Les Templates vous permettent d'enregistrer un groupe de produits que vous pouvez réutiliser dans vos commandes
            </p>
            
            <div class="tw-flex tw-w-screen tw-justify-around tw-items-center tw-mt-6">

                <button class="tw-btn tw-btn-white " data-toggle="collapse" data-target="#addTemplate">
                    Ajouter Template
                </button>

                <button class="tw-btn tw-btn-white" data-toggle="collapse" data-target="#addProduct">
                    Ajouter Produit
                </button>

                <button class="tw-btn tw-btn-white" @click="addReorderpoint()">
                    {{-- <i class="fas fa-spinner fa-spin"></i> --}}
                    Ajouter Reorder Point
                </button>

                <button class="tw-btn tw-btn-white" @click="majStock()">
                    <i class="fas fa-spinner fa-spin" v-if="isLoading.stock"></i>
                    MàJ Stock
                </button>

                <button class="tw-btn tw-btn-white" @click="toggleEdit()" v-if="! editing">
                    <i class="fas fa-spinner fa-spin" v-if="isLoading.stock"></i>
                    <span>Edit Commande</span>
                </button>
                <button class="tw-btn tw-btn-white" @click="save()" v-if="editing">
                    <i class="fas fa-spinner fa-spin" v-if="isLoading.stock"></i>
                    <span>Save Commande</span>
                </button>
                

            </div>

            <div class="tw-flex tw-w-screen tw-justify-center tw-items-center tw--ml-2 tw-mt-6" >
                <div class="tw-w-1/2 tw-flex tw-justify-center collapse" id="addTemplate">
                    <div class="tw-w-1/2 tw-mr-4">
                        <multiselect v-model="selected_template" :options="{{ $templates }}" :searchable="true" :close-on-select="true" :show-labels="false"
                        placeholder="Pick a value" label="name"></multiselect>
                    </div>

                    <button class="tw-btn tw-btn-white tw-leading-none" @click="addTemplate()">Ajouter Templates</button>
                </div>
                
                <div class="tw-w-1/2 tw-flex tw-justify-center collapse" id="addProduct">
                    <div class="tw-w-1/2 tw-mr-4">
                        <multiselect v-model="selected_product" :options="{{ $products }}" :searchable="true" :close-on-select="true" :show-labels="false"
                        placeholder="Pick a value" label="name"></multiselect>
                    </div>

                    <button class="tw-btn tw-btn-white tw-leading-none" @click="addProduct()">Ajouter Produit</button>
                </div>
            </div>

            

            <div class="tw-flex tw-justify-around tw-w-screen" v-if="commande">
                <p class="tw-text-lg my-4" v-if="commande.templates">
                    @{{ commande.templates.length }} 
                    @{{ commande.templates.length <= 1 ? 'Template' : 'Templates' }}
                </p>
                <p class="tw-text-lg my-4 tw-ml-5">
                    @{{ numberOfProducts }} 
                    @{{ numberOfProducts <= 1 ? 'Produit' : 'Produits' }}
                </p>
            </div>
            
            <button class="tw-btn" data-toggle="modal" data-target="#modelId">Créer Template</button>
            <!-- Modal -->
        </header>

        <main class="tw-flex tw-flex-col tw-justify-center tw-items-center tw-w-screen tw-mt-5">

            {{-- Sections --}}

            <button class="tw-btn tw-bg-red-500 tw-leading-none" data-toggle="modal" data-target="#section" >Ajouter Section</button>


            <div id="accordianId" role="tablist" aria-multiselectable="true" class="tw-mt-5 tw-w-1/2">
                <div class="card" v-for="section in commande.sections">
                    <div class="card-header" role="tab" id="section1HeaderId">
                        <h5 class="mb-0">
                            <a data-toggle="collapse" data-parent="#accordianId" :href="'#section' + section.id " aria-expanded="true" aria-controls="section1ContentId">@{{section.nom}}</a>
                        </h5>
                    </div>
                    <div :id="'section' + section.id" class="collapse in" role="tabpanel" aria-labelledby="section1HeaderId">
                        <div class="card-body tw-flex-col tw-items-center tw-justify-center">

                            <div class="tw-flex ">
                                
                                <div class="tw-w-1/2 tw-mr-4">
                                    <multiselect v-model="selected_article" :options="articles" :searchable="true" :close-on-select="true" :show-labels="false"
                                    placeholder="Pick a value" label="nom"></multiselect>
                                </div>
                                <button class="tw-btn tw-btn-dark tw-leading-none" @click="addProductToSection(section.id)">Ajouter Produit</button>
                            </div>

                            <ul class="list-group tw-mt-3 tw-w-1/2">
                                <li class="list-group-item " v-for="article in section.articles">@{{ article.nom }} </li>
                            </ul>
                        </div>  
                    </div>
                </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="section" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Modal title</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                              <label for="">Nom de Section</label>
                              <input type="text" class="form-control" v-model="new_section" aria-describedby="helpId" placeholder="">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="tw-btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="tw-btn btn-primary" @click="addSection()">Save</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Templates --}}
            <article class="tw-flex tw-flex-col tw-w-full tw-items-center" v-if="commande.templates">
                <h4 class="tw-mt-6 tw-text-xl tw-font-semibold" >Templates</h4>
                <div id="accordianId" role="tablist" aria-multiselectable="true" class="tw-w-2/3 tw-mt-6">

                    <div class="card" v-for="template in commande.templates">
                        <div class="card-header" role="tab" id="section1HeaderId">
                            <h5 class="">
                                <a data-toggle="collapse" data-parent="#accordianId" :href="'#template' + template.id">@{{ template.name }}</a>
                            </h5>
                        </div>

                        <div :id="'template' + template.id" class="collapse in" role="tabpanel" aria-labelledby="section1HeaderId">
                            <div class="card-body">
                                <table class="table tw-w-100">
                                    <thead class="thead-inverse">
                                        <tr>
                                            <th>Nom</th>
                                            <th>SKU</th>
                                            <th>Price</th>
                                            <th>Stock</th>
                                            <th>Quantité</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(product, index) in template.products ">
                                            <td scope="row">@{{ product.name }}</td>
                                            <td>@{{ product.sku }}</td>
                                            <td>@{{ product.price }}</td>
                                            <td >
                                                <span v-if="product.stock">@{{ product.stock }}</span>
                                                <i v-if="isLoading.stock" class="fas fa-spinner fa-spin"></i>
                                            </td>
                                            <td v-if="editing">
                                                <input v-model.number="product.quantity" type="text" class="form-control" name="" id="" aria-describedby="helpId" placeholder="">
                                            </td>
                                            <td v-else>@{{ product.quantity }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </article>
            

            {{-- Autres Produits --}}

            <article class="tw-flex tw-flex-col tw-w-full tw-items-center" v-if="commande.products">
                <h4 class="tw-mt-6 tw-text-xl tw-font-semibold ">Autres Produits</h4>
                <table class="table tw-w-2/3 tw-mt-6">
                    <thead class="thead-inverse">
                        <tr>
                            <th>Nom</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Quantité</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(product, index) in commande.products ">
                                <td scope="row">@{{ product.name }}</td>
                                <td>@{{ product.sku }}</td>
                                <td>@{{ product.price }}</td>
                                <td >
                                    <span v-if="product.stock">@{{ product.stock }}</span>
                                    <i v-if="isLoading.stock" class="fas fa-spinner fa-spin"></i>
                                </td>
                                <td>
                                    <input v-model.number="product.quantity" type="text" class="form-control" name="" id="" aria-describedby="helpId" placeholder="">
                                </td>
                                <td>
                                    <i class="fas fa-times tw-text-red-500 tw-mr-2 tw-cursor-pointer" @click="removeProduct(index)"></i>
                                </td>
                            </tr>    
                        </tbody>
                </table>
            </article>
            

            {{-- Reorder Point --}}

            <article class="tw-flex tw-flex-col tw-w-full tw-items-center" v-if="commande.reorderpoint">
                <div class="tw-flex tw-mt-20 tw-items-center">
                    <h4 class=" tw-text-xl tw-font-semibold ">Reorder Point</h4>
                    <button class="tw-btn tw-btn-dark tw-ml-5">MàJ Reorder Point</button>
                </div>
                <table class="table tw-w-2/3 tw-mt-6">
                    <thead class="thead-inverse">
                        <tr>
                            <th>Nom</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Quantité</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(product, index) in commande.reorderpoint[0].products" v-if="commande.reorderpoint[0].products">
                            <td scope="row">@{{ product.name }}</td>
                            <td>@{{ product.sku }}</td>
                            <td>@{{ product.price }}</td>
                            <td >
                                <span v-if="product.stock !== null">@{{ product.stock }}</span>
                                <i v-if="isLoading.reorder_point" class="fas fa-spinner fa-spin"></i>
                            </td>
                            <td>
                                <input v-model.number="product.quantity" type="text" class="form-control" name="" id="" aria-describedby="helpId" placeholder="">
                            </td>
                            <td>
                                <i class="fas fa-times tw-text-red-500 tw-mr-2 tw-cursor-pointer" @click="removeProduct(index)"></i>
                            </td>
                            
                        </tr>    
                    </tbody>
                </table>
            </article>

            
        </main>

    </section>
</commande-show>

@endsection