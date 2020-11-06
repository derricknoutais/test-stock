@extends('layouts.welcome')


@section('content')
<prepa-demande :commande_prop="{{$commande}}" :commandes_prop="{{ $commandes }}" :fournisseurs_prop="{{$fournisseurs}}" inline-template>
    <div class="tw-flex tw-flex-col">

        <div class="tw-flex">
            {{-- Prepa Demande --}}
            <div class="tw-mx-auto tw-container-fluid tw-w-3/4 tw-bg-gray-300">
                {{-- Titre --}}
                <h1 class="tw-text-4xl tw-mt-10 tw-tracking-widest tw-font-hairline tw-font- tw-text-center">Prepa - Demandes - {{ $commande->name }}</h1>
                {{-- Bouton Flottant Ajouter a Demande --}}
                <div class="tw-flex tw-mt-5 tw-py-1 tw-justify-center tw-items-center tw-sticky tw-top-0" v-if="selected_products.length > 0">
                    <button class="tw-btn tw-btn-dark tw-leading-none tw-ml-5" data-toggle="modal" data-target="#ajouter-demande-modal">Ajouter à <span>@{{ selected_products.length }}</span> éléments à Demande ... <i class="fas fa-mail-bulk tw-ml-2"></i></button>
                </div>
                {{-- Bouttons Options --}}
                <div class="tw-mt-24 tw-bg-gray-500 tw-py-10 tw-w-full">
                    <button class="tw-btn tw-btn-dark tw-leading-none tw-ml-5" @click="filter_demandé()" >Déja Demandé</button>
                    <button class="tw-btn tw-btn-dark tw-leading-none tw-ml-5" @click="filter_non_demandé()" >Pas encore Demandé</button>

                    <button class="tw-btn tw-btn-dark tw-leading-none tw-ml-5" @click="réinitialiser()" >Réinitialiser</button>
                </div>
                {{-- Sections --}}
                <div class="tw-bg-gray-300 tw-px-32" v-for="section in commande.sections" v-show="filtered.sections.length <= 0">

                    <div class="tw-flex tw-items-center tw-mt-24" @click="toggleSection(section)">
                        <i class="fas fa-chevron-down tw-cursor-pointer" @click="toggleSection(section)" v-if="section.show"></i>
                        <i class="fas fa-chevron-right tw-cursor-pointer" @click="toggleSection(section)" v-else></i>
                        <h4 class="tw-text-2xl tw-ml-4 tw-font-thin tw-tracking-wide">@{{ section.nom }} [ <span class="tw-text-blue-500">@{{ niveauDAchevement(section, 'niveau') }}</span> <span class="tw-text-red-500">@{{ niveauDAchevement(section, 'pourcentage') }}%</span> ]</h4>
                    </div>

                    <table class="table" v-show="section.show">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" @click="checkAll(section)" v-model="section.checkAll">
                                </th>
                                <th>Identifiant</th>
                                <th>Nom </th>
                                <th>Quantité</th>
                                <th># Demande</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="produit in section.products" :class=" produit.demandes.length > 0 ? 'tw-text-red-500' : '' ">
                                <td class="tw-flex tw-items-center">
                                    {{-- <i class="fas fa-chevron-down tw-cursor-pointer" @click="toggleSection(section)" v-if="produit.show"></i>
                                    <i class="fas fa-chevron-right tw-cursor-pointer" @click="toggleSection(section)" v-else></i> --}}
                                    <input type="checkbox" v-model="selected_products"  :value="produit">
                                </td>
                                <td scope="row">
                                    <a :href=" 'https://stapog.vendhq.com/product/' + produit.id " target="_blank">
                                        @{{ produit.id }}
                                    </a>
                                </td>
                                <td>@{{ produit.name }}</td>
                                <td>@{{ produit.pivot.quantite }}</td>
                                <td>@{{produit.demandes.length}}</td>
                            </tr>
                            {{-- :class=" produit.demandes.length > 0 ? 'tw-text-red-500' : '' " --}}
                            <tr v-for="produit in section.articles" :class=" produit.demandes.length > 0 ? 'tw-text-red-500' : '' ">
                                <td>
                                    <input type="checkbox" v-model="selected_products"  :value="produit">
                                </td>
                                <td scope="row">
                                    <a :href=" 'http://azimuts.ga/fiche-renseignement/' + produit.fiche_renseignement_id " target="_blank">
                                    0af7n3os-{{ rand(1000,9999) }}-ff13-kj{{ rand(100,999) }}-@{{ produit.fiche_renseignement_id   }}
                                    </a>
                                </td>
                                <td>@{{ produit.nom }}</td>
                                <td>@{{ produit.pivot.quantite }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-for="section in filtered.sections" v-show="filtered.sections.length > 0">
                    <h4 class="tw-text-2xl tw-mt-24 tw-font-thin tw-tracking-wide">@{{ section.nom }}</h4>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" @click="checkAll(section)" v-model="section.checkAll">
                                </th>
                                <th>Identifiant</th>
                                <th>Nom </th>
                                <th>Quantité</th>
                                <th>Prix Achat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="produit in section.products">
                                <td>
                                    <input type="checkbox" v-model="selected_products"  :value="produit">
                                </td>
                                <td scope="row">
                                    <a :href=" 'https://stapog.vendhq.com/product/' + produit.id " target="_blank">
                                        @{{ produit.id }}
                                    </a>
                                </td>
                                <td>@{{ produit.name }}</td>
                                <td>@{{ produit.pivot.quantite }}</td>
                                <td></td>
                            </tr>
                            <tr v-for="produit in section.articles">
                                <td>
                                    <input type="checkbox" v-model="selected_products"  :value="produit">
                                </td>
                                <td scope="row">
                                    <a :href=" 'http://azimuts.ga/fiche-renseignement/' + produit.fiche_renseignement_id " target="_blank">
                                    0af7n3os-{{ rand(1000,9999) }}-ff13-kj{{ rand(100,999) }}-@{{ produit.fiche_renseignement_id   }}
                                    </a>
                                </td>
                                <td>@{{ produit.nom }}</td>
                                <td>@{{ produit.pivot.quantite }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- Demandes --}}
            <div class="tw-mx-auto tw-container tw-w-1/4 tw-flex tw-flex-col tw-items-center tw-bg-gray-300 tw-border tw-border-gray-400 tw-border-r-0 tw-border-t-0 tw-border-b-0">

                <h1 class="tw-text-3xl tw-uppercase tw-tracking-wide tw-text-center tw-my-5 ">Demandes</h1>

                <button type="button" name="" id="" class="tw-btn tw-btn-dark tw-uppercase" data-toggle="modal" data-target="#demande-modal">Ajouter Une Demande</button>
                <button type="button" name="" id="" class="tw-mt-5 tw-btn tw-btn-dark tw-uppercase " @click="dispatchProduits()">
                    <i class="fas fa-spinner fa-spin" v-if="isLoading.toutesDemandes"></i>
                    Génerer Toutes Les Demandes
                </button>
                <button type="button" class="tw-mt-5 tw-btn tw-btn-dark tw-uppercase"
                    @click="prendreOffreDe(5)"
                >
                    {{-- <i class="fas fa-spinner fa-spin" v-if="isLoading.toutesDemandes"></i> --}}
                    Offres a Partir de ...
                </button>

                <div class="tw-w-3/4">
                    <ul class="tw-mt-10">
                        <a :href="'/demande/' + demande.id" v-for="demande in commande.demandes">
                            <li class="list-group-item d-flex justify-content-between align-items-center" >

                                @{{ demande.nom }}

                            <span class="badge badge-secondary badge-pill tw-ml-8" v-if="demande.sectionnables">
                                @{{ demande.sectionnables.length }}
                            </span>
                        </li>
                        </a>
                    </ul>
                </div>
            </div>

            {{-- Modal Demandes --}}
            <div class="modal fade" id="demande-modal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true" @keydown.enter="saveDemande">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">AJOUTER UNE DEMANDE</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                              <label for="">Nom de la Demande</label>
                              <multiselect v-model="selected_fournisseur" :options="fournisseurs" label="nom" :searchable="true" :close-on-select="true" :show-labels="true"
                              placeholder="Pick a value"></multiselect>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <button type="button" class="btn btn-primary" @click="saveDemande">Enregistrer</button>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Modal Commandes --}}
            {{-- <div class="modal fade" id="commandes-modal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">SELECTIONNER UNE COMMANDE</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="tw-px-5 tw-flex tw-items-center tw-h-6" v-for="commande_offre in commandes">
                                <input type="checkbox" :value="commande_offre.id" v-model="offre_commande" class="tw-mx-5">
                                <label for="">@{{ commande_offre.name }}</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <button type="button" class="btn btn-primary" @click="saveDemande">Enregistrer</button>
                        </div>
                    </div>
                </div>
            </div> --}}

        </div>
        {{-- Boutons <-- Précédent - Suivant --> --}}
        <div class="tw-flex tw-py-5 tw-justify-center tw-items-center tw-sticky tw-bottom-0 tw-bg-gray-500">
            <a href="/commande/{{$commande->id}}" class="tw-btn tw-btn-dark tw-leading-none">Précédent</a>
            <a href="/commande/{{$commande->id}}/demandes" class="tw-btn tw-btn-dark tw-leading-none tw-ml-5">Suivant</a>
        </div>

        <div class="modal fade" id="ajouter-demande-modal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">AJOUTER A DEMANDE</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                    </div>
                    <div class="modal-body">
                        <div class="tw-px-5 tw-flex tw-items-center tw-h-6" v-for="demande in commande.demandes">
                            <input type="checkbox" :value="demande" v-model="selected_demandes" class="tw-mx-5">
                            <label for="">@{{ demande.nom }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" @click="addProductsToDemandes">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</prepa-demande>


@endsection
