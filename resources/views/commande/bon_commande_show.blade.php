@extends('layouts.welcome')


@section('content')
    <bon-commande-show :bc_prop="{{$bc}}" inline-template>
        <div class="tw-container tw-mx-auto">
            <h1 class="tw-text-center tw-text-5xl">Bon Commande @{{ bc.nom }}</h1>

            <h2 class="tw-text-3xl tw-mt-20">Liste des Produits</h2>
            <table class="table tw-mt-10">
                <thead>
                    <tr>
                        <th>Nom du Produit</th>
                        <th>Quantité</th>
                        <th>Prix Achat</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="sectionnable in bc.sectionnables" v-if="sectionnable.product">
                        <td scope="row">@{{ sectionnable.product.name }} </td>
                        <td>@{{ sectionnable.pivot.quantite }}</td>
                        <td>@{{ sectionnable.pivot.prix_achat }}</td>
                        <td>@{{ sectionnable.pivot.quantite * sectionnable.pivot.prix_achat }}</td>
                    </tr>
                    <tr v-for="sectionnable in bc.sectionnables" v-if="sectionnable.article">
                        <td scope="row">@{{ sectionnable.article.nom }} </td>
                        <td>@{{ sectionnable.pivot.quantite }}</td>
                        <td>@{{ sectionnable.pivot.prix_achat }}</td>
                        <td>@{{ sectionnable.pivot.quantite * sectionnable.pivot.prix_achat }}</td>
                    </tr>
                    <tr>
                        <td scope="row" colspan="3" class="tw-text-right">MONTANT TOTAL</td>
                        <td>@{{ montantTotal }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </bon-commande-show>
@endsection