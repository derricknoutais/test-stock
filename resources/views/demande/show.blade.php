@extends('layouts.welcome')


@section('content')

<demande-show :demande_prop="{{$demande}}" inline-template>
    <div class="tw-container-fluid tw-mx-10">
        <en-tete bg-color="tw-bg-green-500"></en-tete>

        <h1 class="tw-text-5xl tw-text-center tw-mt-20 tw-uppercase">Demande @{{ demande.nom }}</h1>
        <div class="tw-mt-10">
            <a :href="'/demande/export/' + demande.id" class="tw-btn tw-btn-dark" >Télécharger .xlsx</a>
        </div>


        <table class="table tw-mt-10">

            <thead>
                <tr>

                    {{-- Option Branding 2.0 <th></th> --}}
                    <th>Id</th>
                    <th>Produit (Français)</th>
                    <th>Product (English)</th>
                    <th>Différente Offre?</th>
                    <th>Quantité</th>
                    <th>Quantité Offerte</th>
                    <th>Offre</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <template v-for="sectionnable in demande.sectionnables" v-if="sectionnable.product">
                    <tr class="">
                        {{-- Option Branding 2.0 <td>
                            <i class="fas fa-chevron-right tw-cursor-pointer" @click="toggleDetails(sectionnable)"></i>
                        </td> --}}
                        <td >
                            @{{ sectionnable.pivot.id }}
                        </td>
                        <td>
                            @{{ sectionnable.product.name }}
                        </td>
                        <td v-if="sectionnable.pivot.traduction">
                            <span class="tw-flex tw-items-center tw-justify-between">
                                <span class="tw-w-full">
                                    <span v-if="! sectionnable.editing">
                                        @{{ sectionnable.pivot.traduction }}
                                    </span>
                                    <input v-else type="text" class="form-control tw-w-full tw-inline-block" v-model="sectionnable.pivot.traduction">
                                </span>
                                <span>
                                    <i v-if="! sectionnable.editing" class="fas fa-pen tw-mx-3 tw-cursor-pointer tw-text-blue-600" @click="editTraduction(sectionnable)"></i>
                                    <i v-else class="fas fa-save tw-mx-3 tw-cursor-pointer tw-text-blue-600" @click="saveTraduction(sectionnable)"></i>
                                </span>

                            </span>
                        </td>
                        <td scope="row" v-else-if="sectionnable.product.handle" >

                            <span class="tw-flex tw-items-center tw-justify-between">
                                <span>
                                    @{{ sectionnable.product.handle.translation }}
                                    <span v-if="sectionnable.product.handle.display1">/ @{{ sectionnable.product[sectionnable.product.handle.display1] }}</span>
                                    <span v-if="sectionnable.product.handle.display2">/ @{{ sectionnable.product[sectionnable.product.handle.display2] }}</span>
                                    <span v-if="sectionnable.product.handle.display3">/ @{{ sectionnable.product[sectionnable.product.handle.display3] }}</span>
                                </span>
                                <i class="fas fa-pen tw-mx-3 tw-cursor-pointer tw-text-blue-600" @click="editTraduction(sectionnable)" ></i>
                            </span>
                        </td>
                        <td v-else></td>
                        <td>

                            <input class="form-check-input" type="checkbox" v-model="sectionnable.pivot.differente_offre" @change="updateSectionnable(sectionnable, 'differente_offre' , sectionnable.pivot.differente_offre)">

                            <input v-if="sectionnable.pivot.differente_offre" class="form-control tw-w-2/3" type="text" v-model="sectionnable.pivot.reference_differente_offre" @change="updateSectionnable(sectionnable, 'reference_differente_offre' ,sectionnable.pivot.reference_differente_offre)">
                        </td>
                        <td>@{{ sectionnable.quantite }} </td>

                        <td class="tw-flex tw-items-center">
                            <i class="fas fa-arrow-down tw-text-red-500 tw-px-5" v-if="sectionnable.pivot.quantite_offerte < sectionnable.quantite"></i>
                            <i class="fas fa-arrow-up tw-text-yellow-600 tw-px-5" v-if="sectionnable.pivot.quantite_offerte > sectionnable.quantite"></i>
                            <i class="fas fa-thumbs-up tw-text-green-500 tw-px-5" v-if="sectionnable.pivot.quantite_offerte === sectionnable.quantite"></i>
                            <input type="number" class="form-control" min="0" step="1" v-model.number="sectionnable.pivot.quantite_offerte" @input="enregisterOffre(sectionnable)">
                        </td>
                        <td>
                            <input type="number" class="form-control" :class="{'tw-border-red-500': sectionnable.hasError, 'focus:tw-border-red-500' : sectionnable.hasError }" min="0" step="1" v-model.number="sectionnable.pivot.offre" @input="enregisterOffre(sectionnable)" onkeyup="">
                        </td>
                        <td >
                            <span v-if="sectionnable.hasError" class="tw-text-red-500"> Erreur! Vérifiez vos entrées!!!</span>
                            <span v-else>@{{ sectionnable.pivot.quantite_offerte * sectionnable.pivot.offre | currency }}</span>
                        </td>
                        <td>
                            <i class="fas fa-trash tw-text-red-500 tw-cursor-pointer" @click="openDeleteModal(sectionnable)"></i>
                            <i class="fas fa-exclamation-triangle tw-text-yellow-600 tw-cursor-pointer tw-ml-2" @click="openDeleteModal(sectionnable)" v-if="sectionnable.pivot.checked === -1"></i>
                        </td>
                    </tr>
                    {{-- Option Branding 2.0 <tr v-show="sectionnable.displayDetails" class=" tw-bg-gray-700 tw-text-white">
                        <td colspan=9 class="tw-py-10 tw-mx-10">
                            <div class="tw-flex tw-items-center">
                                <span v-for="brand in sectionnable.product.handle.brands " class="tw-w-1/3 tw-flex tw-items-center">
                                    Reference @{{ brand.nom }}
                                    <input name="" id="" class="form-control tw-inline-block tw-w-1/2 tw-mx-3" type="text">
                                    <i class="fas fa-check-circle tw-text-green-700 fa-lg"></i>
                                    <i class="fas fa-times-circle fa-lg tw-text-red-500 tw-mx-3"></i>
                                </span>

                            </div>
                        </td>
                    </tr> --}}


                </template>
                <tr v-for="sectionnable in demande.sectionnables" v-if="sectionnable.article">
                    <td>@{{ sectionnable.pivot.id }}</td>
                    <td scope="row">@{{ sectionnable.article.nom }}</td>
                    <td>
                        <span v-if="sectionnable.pivot.traduction && ! sectionnable.editing">
                            @{{ sectionnable.pivot.traduction }}
                            <i class="fas fa-pen tw-mx-3 tw-cursor-pointer tw-text-blue-600" @click="editMode(sectionnable)"></i>
                        </span>
                        <span v-if="!sectionnable.pivot.traduction || sectionnable.editing" class="tw-flex tw-items-center">
                            <input type="text" class="form-control tw-inline-block" v-model="sectionnable.pivot.traduction"
                                @focus="sectionnable.editing = true" />
                            <i class="fas fa-save tw-mx-2 tw-text-green-700 tw-cursor-pointer"
                            @click="updateSectionnable(sectionnable, 'traduction', sectionnable.pivot.traduction)"></i>
                        </span>
                    </td>
                    <td>

                        <input class="form-check-input" type="checkbox" v-model="sectionnable.pivot.differente_offre" @change="updateSectionnable(sectionnable, 'differente_offre' , sectionnable.pivot.differente_offre)">

                        <input v-if="sectionnable.pivot.differente_offre" class="form-control tw-w-2/3" type="text" v-model="sectionnable.pivot.reference_differente_offre" @change="updateSectionnable(sectionnable, 'reference_differente_offre' ,sectionnable.pivot.reference_differente_offre)">
                    </td>
                    <td>@{{ sectionnable.quantite }} </td>
                    <td class="tw-flex tw-items-center">
                        <i class="fas fa-arrow-down  tw-text-red-500 tw-px-5" v-if="sectionnable.pivot.quantite_offerte < sectionnable.quantite"></i>
                        <i class="fas fa-arrow-up  tw-text-yellow-600 tw-px-5" v-if="sectionnable.pivot.quantite_offerte > sectionnable.quantite"></i>
                        <i class="fas fa-thumbs-up  tw-text-green-500 tw-px-5" v-if="sectionnable.pivot.quantite_offerte === sectionnable.quantite"></i>

                        <input type="text" class="form-control" v-model.number="sectionnable.pivot.quantite_offerte" @input="enregisterOffre(sectionnable)">
                    </td>
                    <td>

                        <input type="text" class="form-control" v-model.number="sectionnable.pivot.offre" @input="enregisterOffre(sectionnable)">
                    </td>
                    <td>
                        @{{ sectionnable.pivot.quantite_offerte * sectionnable.pivot.offre | currency}}
                    </td>
                    <td>
                        <i class="fas fa-trash tw-text-red-500 tw-cursor-pointer" @click="openDeleteModal(sectionnable)"></i>
                        <i class="fas fa-exclamation-triangle tw-text-red-500 tw-cursor-pointer" v-if="sectionnable.pivot.checked === -1"></i>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="tw-text-right"></td>
                    <td class="tw-flex tw-justify-center">
                        <button class="tw-btn tw-btn-dark" @click="normaliserQuantités()">Normaliser Quantités</button>
                    </td>
                    <td class="tw-text-right">Total</td>
                    <td>@{{totalDemande | currency }}</td>
                </tr>

            </tbody>

        </table>

        <!-- Modal -->
        <div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Modal title</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="">Êtes-vous sûr de vouloir supprimer cet élement. Cette action est irréversible</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-primary" @click="removeSectionnable()">Oui, Supprimer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</demande-show>


@endsection
