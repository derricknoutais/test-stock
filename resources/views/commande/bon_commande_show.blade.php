@extends('layouts.welcome')


@section('content')
    <bon-commande-show :bc_prop="{{$bc}}" inline-template>
        <div class="tw-container tw-mx-auto tw-flex tw-flex-col">
            <h1 class="tw-text-center tw-text-5xl">@{{ bc.nom }}</h1>
            <div>
                <a href="/bons-commandes/export/{{ $bc->id }}" class="tw-btn tw-inline-block tw-btn-dark tw-mt-5">Export .xlsx</a>
                <button v-if="! editMode" class="tw-btn tw-inline-block tw-btn-dark tw-mt-5" @click="toggleEditMode()">Modifier</button>
                <button v-else class="tw-btn tw-inline-block tw-btn-dark tw-mt-5" @click="updateAllEdited()">Enregistrer</button>
            </div>

            <h2 class="tw-text-3xl tw-mt-20">Liste des Produits</h2>

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
                            <i v-if="sectionnable.editMode" class="fas fa-save" @click="updateSectionnable(sectionnable)"></i>
                            <i v-if="! sectionnable.editMode && !editMode" class="fas fa-edit" @click="enableSectionnableEditMode(sectionnable)"></i>
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
