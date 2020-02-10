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
                    </tr>
                </tbody>
            </table>
        </div>
    </demandes-commande-list>
@endsection