@extends('layouts.welcome')


@section('content')
    <demandes-commande-list inline-template :commande_prop="{{$commande}}">
        <div class="tw-container tw-mx-auto tw-mt-10">
            <h1 class="tw-text-4xl">@{{ commande.name}} </h1>
            <button type="button" class="btn tw-btn-dark" @click="générerBons()">Générer Bons Commande</button>
            <table class="table tw-mt-10">
                <thead>
                    <tr>
                        <th>Identifiant Demande</th>
                        <th>Nom de la Demande</th>
                        <th>Créé le </th>
                        <th>Total Demande</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="demande in commande.demandes">
                        <td scope="row">
                            <a :href="'/demande/' + demande.id">@{{ demande.id }}</a>
                        </td>
                        <td>@{{ demande.nom }}</td>
                        <td>@{{ demande.created_at }}</td>
                        <td>@{{ totalDemande(demande) }}</td>
                        <td>
                            <i class="fas fa-edit tw-text-blue-500 tw-mr-3 tw-cursor-pointer" @editDemandeName()></i>
                            <i class="fas fa-times tw-text-red-500 tw-cursor-pointer"></i>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="tw-flex tw-my-10 tw-py-5 tw-justify-center tw-items-center tw-sticky tw-bottom-0">
                <a href="/commande/{{$commande->id}}" class="tw-btn tw-btn-dark tw-leading-none">Précédent</a>
                <a href="/commande/{{$commande->id}}/bons-commandes" class="tw-btn tw-btn-dark tw-leading-none tw-ml-5">Suivant</a>
            </div>
        </div>
    </demandes-commande-list>
@endsection