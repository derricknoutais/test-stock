@extends('layouts.welcome')


@section('content')
    <bon-commande-show :bc_prop="{{$bc}}" inline-template>
        <div class="tw-container tw-mx-auto tw-flex tw-flex-col">
            {{-- En Tete --}}
            <en-tete></en-tete>
            {{-- Titre Document --}}
            <h1 class="tw-text-center tw-text-5xl">@{{ bc.nom }}</h1>
            {{-- Boutons de Fonction --}}
            <div>
                <a href="/bons-commandes/export/{{ $bc->id }}" class="tw-btn tw-inline-block tw-btn-dark tw-mt-5">Export .xlsx</a>
                {{-- <button v-if="! editMode" class="tw-btn tw-inline-block tw-btn-dark tw-mt-5" @click="toggleEditMode()">Modifier</button>
                <button v-else class="tw-btn tw-inline-block tw-btn-dark tw-mt-5" @click="updateAllEdited()">Enregistrer</button> --}}
            </div>

            {{-- Sous-Titre --}}
            <h2 class="tw-text-3xl tw-mt-20">Liste des Produits</h2>
            {{-- Selecteur --}}
            <div class="tw-flex tw-items-center tw">
                <multiselect class="tw-cursor-text" v-model="newProduct" :options="{{ $products }}" :searchable="true" :close-on-select="true" :show-labels="false"
                placeholder="Pick a value" label="name"></multiselect>

                <input v-if="newProduct" type="number" v-model.number="newProduct.quantite" class="tw-input tw-ml-5 tw-bg-white tw-h-full" placeholder="Quantité">
            </div>
            {{-- Boutton Ajouter Nouveau Produit --}}
            <button v-if="! editMode" class="tw-btn tw-inline-block tw-bg-green-800 tw-text-white tw-mt-5 " @click="addNewProduct()">Ajouter Produit</button>

            <table class="table tw-mt-10">
                <thead>
                    <tr>
                        <th>Nom du Produit</th>
                        <th>Quantité</th>
                        <th>Prix Achat</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Produits --}}
                    <tr v-for="sectionnable in bc.sectionnables" v-if="sectionnable.product">
                        {{-- Nom du Produit --}}
                        <td scope="row">@{{ sectionnable.product.name }} </td>
                        {{-- Quantité du Produit --}}
                        <td>
                            <input v-if="editMode || sectionnable.editMode" @input="addEdited(sectionnable)" class="tw-input focus:tw-border-gray-600" type="text" v-model="sectionnable.pivot.quantite">
                            <span v-else>@{{ sectionnable.pivot.quantite }}</span>
                        </td>
                        {{-- Prix Achat du Produit --}}
                        <td>
                            {{-- En Mode Edit --}}
                            <input v-if="editMode || sectionnable.editMode" @input="addEdited(sectionnable)" class="tw-input focus:tw-border-gray-600" type="text"  v-model="sectionnable.pivot.prix_achat">
                            {{-- En Mode Normal --}}
                            <span v-else >@{{ sectionnable.pivot.prix_achat }}</span>
                        </td>

                        <td>
                            <animated-number
                                :value="sectionnable.pivot.quantite * sectionnable.pivot.prix_achat"
                                :format-value="formatToPrice"
                                :duration="500"
                            />
                        </td>
                        <td>
                            <i v-if="sectionnable.editMode" class="fas fa-save tw-text-green-700 tw-cursor-pointer" @click="updateSectionnable(sectionnable)"></i>
                            <i v-if="! sectionnable.editMode && !editMode " class="fas fa-edit tw-text-blue-700 tw-cursor-pointer" @click="enableSectionnableEditMode(sectionnable)"></i>
                            <i class="fas fa-trash tw-text-red-700 tw-ml-5 tw-cursor-pointer" @click="deleteSectionnable(sectionnable)"></i>
                        </td>
                    </tr>
                    {{-- Articles --}}
                    <tr v-for="sectionnable in bc.sectionnables" v-if="sectionnable.article">
                        <td scope="row">@{{ sectionnable.article.nom }} </td>
                        <td>@{{ sectionnable.pivot.quantite }}</td>
                        <td>@{{ sectionnable.pivot.prix_achat }}</td>
                        <td>
                            <animated-number
                                :value="sectionnable.pivot.quantite * sectionnable.pivot.prix_achat"
                                :format-value="formatToPrice"
                                :duration="500"
                            />
                        </td>
                    </tr>
                    {{-- Total --}}
                    <tr>
                        <td scope="row" colspan="3" class="tw-text-right">MONTANT TOTAL</td>
                        <td>
                            <animated-number
                                :value="montantTotal"
                                :format-value="formatToPrice"
                                :duration="500"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </bon-commande-show>
@endsection
