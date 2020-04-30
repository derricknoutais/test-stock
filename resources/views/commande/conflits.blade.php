@extends('layouts.welcome')


@section('content')
<commande-conflits inline-template :commande_prop="{{ $commande }}">
        <div class="container">
            <h1 class="tw-text-3xl tw-text-center tw-mt-10">Liste des Conflits</h1>
            <div id="accordianId" role="tablist" aria-multiselectable="true" class="tw-mt-10">
                <div class="card" v-for="conflit in commande.conflits">
                    <div class="card-header" role="tab" id="section1HeaderId">
                        <h5 class="mb-0">
                            <a data-toggle="collapse" data-parent="#accordianId" href="#section1ContentId" aria-expanded="true" aria-controls="section1ContentId" v-if="conflit.name">
                                @{{ conflit.name }} --- @{{ conflit.pivot.quantite }}
                            </a>
                            <a data-toggle="collapse" data-parent="#accordianId" href="#section1ContentId" aria-expanded="true" aria-controls="section1ContentId" v-else>
                                @{{ conflit.nom }}
                            </a>
                        </h5>
                    </div>
                    <div id="section1ContentId" class="collapse in" role="tabpanel" aria-labelledby="section1HeaderId">
                        <div class="card-body tw-flex tw-flex-col">
                            <ul class="list-group">
                                <li class="list-group-item tw-px-10 tw-flex tw-justify-between" v-for="element in conflit.elements_conflictuels">

                                    <input class="form-check-input" v-model="conflit.selected" name="conflit.id" :value="element" type="radio" aria-label="Text for screen reader">
                                    <span>Fournisseur : @{{ element.demande.nom }}</span>
                                    <span>Prix : @{{ element.offre | currency }}</span>
                                    <span>Quantité Offerte : @{{ element.quantite_offerte }}</span>

                                </li>
                                <button class="tw-btn tw-btn-dark tw-mt-5" @click="selectionnerElementConflictuel(conflit)">Selectionner</button>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>

            <div class="tw-flex tw-my-10 tw-py-5 tw-justify-center tw-items-center tw-sticky tw-bottom-0">
                <a href="/commande/{{$commande->id}}/demandes" class="tw-btn tw-btn-dark tw-leading-none">Précédent</a>
                <a href="/commande/{{$commande->id}}/bons-commandes" class="tw-btn tw-btn-dark tw-leading-none tw-ml-5">Suivant</a>
            </div>
        </div>
    </commande-conflits>

@endsection
