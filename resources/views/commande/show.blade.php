@extends('layouts.welcome')


@section('content')

<commande-show :articles_prop="{{ $id_articles }}" :commande_prop="{{ $commande }}" :products_prop="{{ $products }}" :templates_prop="{{ $templates }}"  inline-template>
    <section>

        <header class="tw-flex tw-flex-col tw-items-center tw-bg-gray-600 tw-text-white tw-py-10">
            <p class="tw-text-black tw-text-4xl tw-text-bold tw-mt-6 tw-leading-none">Prépa - {{ $commande->name }}</p>
            <p class="tw-text-black tw-mt-6 tw-leading-none tw-text-2xl">
                Bienvenue dans le tableau de bord de votre commande. Ici vous aurez toutes les informations par rapport à la dite commande
            </p>

            {{-- Cartes Sections / Demandes /  Bons Commandes --}}
            <div class="tw-flex tw-w-full tw-justify-around tw-mt-10">

                {{-- Cartes Sections --}}
                <div class="tw-flex tw-flex-col tw-justify-center tw-items-center tw-w-1/4 tw-mx-5">
                    <div class="tw-flex tw-flex-col tw-w-full tw-justify-center tw-items-center tw-py-3 tw-bg-gray-900 tw-rounded-t-lg">
                        <i class="fas fa-puzzle-piece  fa-2x"></i>
                        <h3 class="tw-text-xl tw-mt-3">Sections ( @{{ commande.sections.length }} ) </h3>
                    </div>

                    <div class="tw-flex tw-flex-col tw-justify-center tw-items-center tw-bg-gray-600 tw-w-full  tw-rounded-b-lg tw-py-10">
                        <h4 class="tw-text-xl"> <i class="fas fa-boxes    "></i> @{{ numberOfProducts }} Produits</h4>
                        <h4 class="tw-text-lg tw-mt-3"> <i class="fas fa-rocket"></i> @{{ numberOfNewProducts }} Nouveaux Produits | <i class="fab fa-vuejs    "></i>  @{{ numberOfVendProducts }} Produits Vend </h4>
                    </div>
                </div>
                {{-- Cartes Demande --}}
                <div class="tw-flex tw-flex-col tw-justify-center tw-items-center tw-w-1/4 tw-mx-5">
                    <a :href="'/commande/' + commande.id  + '/demandes'" class="tw-flex tw-flex-col tw-w-full tw-justify-center tw-items-center tw-py-3 tw-bg-gray-900 tw-rounded-t-lg">
                        <i class="fas fa-envelope-open-text  fa-2x"></i>
                        <h3 class="tw-text-xl tw-mt-3">Demandes ( @{{ commande.demandes.length }} )</h3>
                    </a>

                    <div class="tw-flex tw-flex-col tw-justify-center tw-items-center tw-bg-gray-600 tw-w-full  tw-rounded-b-lg tw-py-10">
                        <h4 class="tw-text-xl"> <i class="fas fa-boxes    "></i> @{{ commande.demandes.length }} Fournisseurs</h4>
                        <h4 class="tw-text-lg tw-mt-3"> <i class="fas fa-rocket"></i> @{{ prixMoyenDemande | currency }} / Demande</h4>
                    </div>
                </div>
                {{-- Cartes Bon Commande --}}
                <div class="tw-flex tw-flex-col tw-justify-center tw-items-center tw-w-1/4 tw-mx-5">
                    <a :href="'/commande/' + commande.id  + '/bons-commandes'" class="tw-flex tw-flex-col tw-w-full tw-justify-center tw-items-center tw-py-3 tw-bg-gray-900 tw-rounded-t-lg">
                        <i class="fas fa-handshake  fa-2x"></i>
                        <h3 class="tw-text-xl tw-mt-3">Bons Commande ( @{{ commande.bons_commandes.length }} )</h3>
                    </a>

                    <div class="tw-flex tw-flex-col tw-justify-center tw-items-center tw-bg-gray-600 tw-w-full  tw-rounded-b-lg tw-py-10">
                        {{-- <h4 class="tw-text-xl"> <i class="fas fa-money"></i>XAF @{{ totalBonsCommandes }} </h4> --}}
                        {{-- <h4 class="tw-text-lg tw-mt-3"> <i class="fas fa-rocket"></i>Produit par Section</h4>
                        <h4 class="tw-text-lg tw-mt-3"> <i class="fas fa-rocket"></i> 9 Produit par Section</h4>  --}}
                    </div>
                </div>

                {{-- Carte Facture --}}
                <div class="tw-flex tw-flex-col tw-justify-center tw-items-center tw-w-1/4 tw-mx-5">
                    {{--  --}}
                    <a :href="'/commande/' + commande.id  + '/factures'" class="tw-flex tw-flex-col tw-w-full tw-justify-center tw-items-center tw-py-3 tw-bg-gray-900 tw-rounded-t-lg">
                        <i class="fas fa-handshake  fa-2x"></i>
                        <h3 class="tw-text-xl tw-mt-3">Factures ( @{{ commande.factures.length }} )</h3>
                    </a>

                    <div class="tw-flex tw-flex-col tw-justify-center tw-items-center tw-bg-gray-600 tw-w-full  tw-rounded-b-lg tw-py-10">
                        {{-- <h4 class="tw-text-xl"> <i class="fas fa-money"></i>XAF @{{ totalBonsCommandes }} </h4> --}}
                        {{-- <h4 class="tw-text-lg tw-mt-3"> <i class="fas fa-rocket"></i>Produit par Section</h4>
                        <h4 class="tw-text-lg tw-mt-3"> <i class="fas fa-rocket"></i> 9 Produit par Section</h4>  --}}
                    </div>
                </div>

            </div>

            {{-- <div class="tw-flex tw-w-screen tw-justify-around tw-items-center tw-mt-6">

                <button class="tw-btn tw-btn-white " data-toggle="collapse" data-target="#addTemplate">
                    Ajouter Template
                </button>

                <button class="tw-btn tw-btn-white" data-toggle="collapse" data-target="#addProduct">
                    Ajouter Produit
                </button>

                <button class="tw-btn tw-btn-white" @click="addReorderpoint()">
                    <i class="fas fa-spinner fa-spin"></i>
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


            </div> --}}

            <div class="tw-flex tw-w-screen tw-justify-center tw-items-center tw-ml-2 tw-mt-6" >
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

            <!-- Modal -->
        </header>

        {{-- Mes Sections --}}
        <main class="tw-flex tw-flex-col tw-justify-center tw-items-center tw-w-screen tw-bg-gray-400 ">

            <h2 class="tw-my-10 tw-text-5xl ">Mes Sections</h2>

            {{-- Boutons de fonction --}}
            <div class="tw-flex tw-justify-center tw-bg-gray-200 tw-w-full tw-py-20">
                <button class="tw-btn tw-bg-gray-900 tw-text-white tw-leading-none" data-toggle="modal" data-target="#section" >Ajouter Section</button>
                <button class="tw-btn tw-bg-gray-900 tw-text-white tw-leading-none tw-ml-5" @click="majStock" >Mettre à Jour Vend Stock
                    <i class="fas fa-spinner fa-spin" v-if="isLoading.majStock"></i>
                </button>
            </div>


            {{-- Modal --}}
            <div class="modal fade" id="sectionDelete" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true" @keydown.enter="removeSection()">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Supprimer Section</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Êtes-vous sûr de supprimer la section "@{{ this.section_being_deleted.nom }}"</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="tw-btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <button type="button" class="tw-btn btn-primary" @click="removeSection()">Oui, Supprimer</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="section" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true" @keydown.enter="addSection()">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Ajouter Section</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                            <label for="">Nom de Section</label>
                            <input type="text" class="form-control" v-model="new_section" aria-describedby="helpId" placeholder="Huile Moteur">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="tw-btn btn-secondary" data-dismiss="modal">Fermer</button>

                            <button type="button" class="tw-btn btn-primary" @click="updateSection()" v-if="isUpdating">Mettre à Jour</button>
                            <button type="button" class="tw-btn btn-primary" @click="addSection()" v-else>Enregistrer</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Accordeon de chaque Section --}}
            <div id="accordianId" role="tablist" aria-multiselectable="true" class="tw-mt-20 tw-w-3/4">
                <div class="card" v-for="section in commande.sections">
                    {{-- En-Tête de Section --}}
                    <div  class="tw-border tw-border-gray-400 tw-shadow tw-cursor-pointer card-header tw-flex tw-justify-between tw-items-center tw-bg-gray-200 tw-text-gray-800" data-toggle="collapse" data-parent="#accordianId" :href="'#section' + section.id " aria-expanded="true" aria-controls="section1ContentId">
                        <h5 class="mb-0 tw-text-xl " data-toggle="collapse" data-parent="#accordianId" :href="'#section' + section.id " role="tab" id="section1HeaderId">@{{section.nom}}
                        </h5>
                        <div>
                            <i class="fas fa-edit tw-mx-3 tw-text-blue-700 tw-cursor-pointer" @click="openEditModal(section)"></i>
                            <i class="fas fa-trash tw-text-red-500 tw-cursor-pointer" @click="openDeleteModal(section)"></i>
                        </div>
                    </div>
                    {{-- Corps de Section --}}
                    <div :id="'section' + section.id" class="collapse in" role="tabpanel" aria-labelledby="section1HeaderId">

                        <div class="card-body tw-flex-col tw-items-center tw-justify-center tw-p-0">

                            {{-- SECTION TOOLBAR--}}
                            <div class="tw-flex-col tw-justify-center tw-items-center tw-bg-gray-600 tw-w-full tw-p-16">

                                {{-- Type de Produits (Vend, Nouveaux Produits, Template) --}}
                                <div class="form-check tw-flex tw-justify-around">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" v-model="sectionnable_type" id="" value="Template">
                                        Template
                                    </label>
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" v-model="sectionnable_type" id="" value="Article">
                                        Nouveaux Produits
                                    </label>
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" v-model="sectionnable_type" value="Product">
                                        Produits Vend
                                    </label>
                                </div>

                                {{-- Barre de Recherche --}}
                                <div class="tw-w-full tw-mr-4 tw-mt-3 tw-flex tw-justify-center tw-items-center">
                                    <multiselect
                                        v-model="selected_article" :options="list_type" :searchable="true"
                                        :show-labels="false" placeholder="Pick a value" :label="label"
                                        id="select" @search-change="asyncFind"
                                    ></multiselect>
                                    <input type="text" v-model.number="selected_article.quantite" id="quantiteInput" class="tw-ml-5  form-control tw-w-1/4 " placeholder="Quantité" @keydown.enter="addProductToSection(section.id)">

                                    <button class="tw-btn ml-5 tw-btn-dark tw-leading-none" @click="addProductToSection(section.id)">Ajouter Produit</button>
                                </div>


                                {{-- Stock --}}
                                <div class="tw-w-full tw-mr-4 tw-mt-10 tw-items-center tw-flex tw-bg-gray-200 tw-p-3" >
                                    <p class="tw-text-lg tw-mr-4">Stock :</p>
                                    <i class="fas fa-spinner fa-spin" v-if="selected_article && selected_article.stock_loading"></i>
                                    <span v-else>@{{ selected_article.stock }}</span>
                                </div>

                                {{-- Subzero --}}
                                <div class="tw-w-full tw-justify-between tw-mr-4 tw-mt-1 tw-items-center tw-flex tw-bg-gray-200 tw-p-3" >
                                    <div class="tw-flex">
                                        <p class="tw-text-lg tw-mr-4">Subzero :</p>
                                        <i class="fas fa-spinner fa-spin" v-if="selected_article && selected_article.sub_loading"></i>
                                        <span v-else>@{{ selected_article.sub }}</span>

                                    </div>
                                    <div>
                                        <span class="tw-mx-3">Entre </span>
                                        <input type="date" v-model="sub_date_apres">
                                        <span class="tw-mx-3">Et </span>

                                        <input type="date" v-model="sub_date_avant" >
                                    </div>
                                </div>

                                {{-- Dernières Commandes --}}
                                <div class="tw-flex tw-flex-col tw-w-full tw-bg-gray-200 tw-p-3 tw-mt-1">
                                    <div class="tw-flex">
                                        <p class="tw-text-2xl tw-mr-4 tw-text-center">Dernières Commandes</p>
                                    </div>
                                    <div class="tw-flex tw-flex-col tw-mt-5 tw-bg-gray-400 tw-p-5" v-if="dernieres_commandes" v-for="dc in dernieres_commandes">
                                        <p class="tw-text-xl tw-underline">@{{ dc.commande.name }}</p>
                                        <a :href=" '/commande/' + commande.id + '/bons-commandes/' + dc.id " class="tw-text-lg tw-mt-3">@{{ dc.nom }}</a>
                                        <p class="tw-text-lg">
                                            <span class="tw-text-red-500">
                                                @{{ dc.sectionnable.quantite }}
                                            </span>
                                            x @{{ dc.sectionnable.prix_achat }} = @{{ dc.sectionnable.quantite * dc.sectionnable.prix_achat }}</p>
                                    </div>
                                </div>

                            </div>
                            {{-- Nouveaux Produits --}}
                            <div class="tw-flex tw-flex-col tw-justify-items-center tw-mt-10 tw-w-full tw-p-16" v-if="articlesFetched.length > 0">
                                <table class="table">
                                    <h4 class="tw-text-2xl tw-my-5 tw-font-bold tw-underline tw-tracking-wide">Nouveaux Produits</h4>
                                    <thead>
                                        <tr>
                                            <th class="tw-text-xl tw-my-5 tw-font-bold tw-w-3/4  tw-tracking-normal">Produit</th>
                                            <th class="tw-text-xl tw-my-5 tw-font-bold  tw-tracking-normal">Quantité</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="article in section.articles">
                                            <td scope="row">
                                                <a :href=" 'http://azimuts.ga/fiche-renseignement/'  + article.fiche_renseignement_id" >@{{article.nom}}</a>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <input type="text" class="form-control" v-model.number="article.pivot.quantite" placeholder="" @input="saveQuantity(section, article)">
                                                </div>
                                            </td>
                                            <td>
                                                <i class="fas fa-times tw-text-red-500" @click="removeProduct(section, article, 'Article')"></i>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Produits Vend --}}
                            <div class="tw-flex tw-flex-col tw-justify-items-center tw-w-full tw-px-32 tw-py-12" v-if="section.products.length > 0">
                                <table class="table">
                                    <h4 class="tw-text-2xl tw-font-bold tw-underline tw-tracking-wide">Produits VEND</h4>
                                    <thead>
                                        <tr class="">
                                            <th class="tw-text-xl tw-my-5 tw-font-bold tw-w-3/4  tw-tracking-normal">Produit</th>
                                            <th class="tw-text-xl tw-my-5 tw-font-bold  tw-tracking-normal">Quantité</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="article in section.products">
                                            <td scope="row">
                                                <a :href=" 'http://azimuts.ga/fiche-renseignement/'  + article.fiche_renseignement_id" >@{{article.name}}</a>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <input type="text" class="form-control" v-model.number="article.pivot.quantite" placeholder="" @input="saveQuantity(section, article)">
                                                </div>
                                            </td>
                                            <td>
                                                <i class="fas fa-times tw-text-red-500" @click="removeProduct(section, article, 'Product')"></i>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            {{-- Boutons  --}}
            <div class="tw-flex tw-mt-10 tw-bg-gray-200 tw-w-full tw-py-10 tw-justify-center tw-items-center">
                <a class="tw-btn tw-btn-dark tw-leading-none" href="/commande">Précédent</a>
                <a :href=" '/commande/' + commande.id + '/prepa-demande'" class="tw-btn tw-btn-dark tw-leading-none tw-ml-5" v-if="commande.sections.length > 0">Suivant</a>
            </div>


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
