@extends('layouts.welcome')


@section('content')

<demande-show :demande_prop="{{$demande}}" inline-template>
    <div class="tw-container tw-mx-auto">

        <h1 class="tw-text-5xl tw-text-center tw-mt-20 tw-uppercase">Demande @{{ demande.nom }}</h1>

        <table class="table tw-mt-5">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Offre</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="sectionnable in demande.sectionnables">
                    <td scope="row">@{{ sectionnable.product.name }}</td>
                    <td>@{{ sectionnable.quantite }} </td>
                    <td>
                        <input type="text" class="form-control" v-model.number="sectionnable.pivot.offre" @input="enregisterOffre(sectionnable)">
                    </td>
                    <td>
                        @{{ sectionnable.quantite * sectionnable.pivot.offre }}
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="tw-text-right">Total</td>
                    <td>@{{totalDemande}}</td>
                </tr>
            </tbody>
        </table>

    </div>


</demande-show>


@endsection